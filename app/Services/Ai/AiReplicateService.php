<?php

namespace App\Services\Ai;

use App\Models\AiAssetGeneration;
use App\Models\AiSetting;
use App\Models\Media;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Image + video generation via Replicate (Flux, SDXL, Kling, Wan, Hunyuan…).
 *
 * Images run synchronously using the `Prefer: wait` header (Replicate holds the
 * request until the prediction finishes, up to 60s). Videos are submitted and
 * then polled via refresh(), mirroring the fal.ai flow.
 */
class AiReplicateService
{
    private const BASE = 'https://api.replicate.com/v1';

    public function __construct(
        private StorageService $storage,
        private AiQuotaService $quota,
    ) {}

    private function client(bool $wait = false): PendingRequest
    {
        $key = AiSetting::singleton()->replicate_api_key;

        if (blank($key)) {
            throw new RuntimeException('Falta el API token de Replicate.');
        }

        $headers = ['Authorization' => 'Bearer ' . $key];

        if ($wait) {
            $headers['Prefer'] = 'wait';
        }

        $waitTimeout = (int) config('starcho_ai.request_timeout', 120);

        return Http::withHeaders($headers)->acceptJson()->timeout($wait ? $waitTimeout : 30);
    }

    // ── Image (synchronous) ──────────────────────────────────────────
    public function generateImage(string $prompt, ?string $model, ?User $user, array $input = []): AiAssetGeneration
    {
        $model = $model ?: (AiSetting::singleton()->image_model ?: 'black-forest-labs/flux-schnell');
        $this->quota->ensureCanGenerate($user, 'image', 1, $this->quota->pricing()->imageCostCents($model, 1));

        $generation = AiAssetGeneration::create([
            'user_id'  => $user?->id,
            'type'     => AiAssetGeneration::TYPE_IMAGE,
            'provider' => 'replicate',
            'model'    => $model,
            'status'   => AiAssetGeneration::STATUS_PROCESSING,
            'prompt'   => $prompt,
            'params'   => $input,
        ]);

        $startedAt = microtime(true);

        try {
            $prediction = $this->createPrediction($model, array_merge(['prompt' => $prompt], $input), true);
            $prediction = $this->waitForCompletion($prediction);
            $url = $this->firstOutputUrl($prediction['output'] ?? null);

            $download = Http::timeout(120)->get($url);
            if ($download->failed()) {
                throw new RuntimeException('No se pudo descargar la imagen generada.');
            }

            $media = $this->storeBytes($download->body(), 'png', 'image/png', $user, 'ai_image', $prompt);
            $cost  = $this->quota->pricing()->imageCostCents($model, 1);

            $generation->update([
                'status'      => AiAssetGeneration::STATUS_COMPLETED,
                'media_id'    => $media->id,
                'external_id' => $prediction['id'] ?? null,
                'cost_cents'  => $cost,
                'price_cents' => $this->quota->pricing()->priceCents($cost),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            $this->quota->record($user, 'image', 1, $cost);
        } catch (\Throwable $e) {
            $generation->update(['status' => AiAssetGeneration::STATUS_FAILED, 'error' => $e->getMessage()]);

            throw $e;
        }

        return $generation->refresh();
    }

    // ── Video (asynchronous) ─────────────────────────────────────────
    public function submitVideo(string $prompt, ?string $model, ?User $user, array $input = []): AiAssetGeneration
    {
        $model = $model ?: (AiSetting::singleton()->video_model ?: 'kwaivgi/kling-v1.6-standard');
        $this->quota->ensureCanGenerate($user, 'video', 1, $this->quota->pricing()->videoCostCents($model, 1));

        $generation = AiAssetGeneration::create([
            'user_id'  => $user?->id,
            'type'     => AiAssetGeneration::TYPE_VIDEO,
            'provider' => 'replicate',
            'model'    => $model,
            'status'   => AiAssetGeneration::STATUS_PROCESSING,
            'prompt'   => $prompt,
            'params'   => $input,
        ]);

        try {
            $prediction = $this->createPrediction($model, array_merge(['prompt' => $prompt], $input), false);
            $generation->update(['external_id' => $prediction['id'] ?? null]);
        } catch (\Throwable $e) {
            $generation->update(['status' => AiAssetGeneration::STATUS_FAILED, 'error' => $e->getMessage()]);

            throw $e;
        }

        return $generation->refresh();
    }

    public function refresh(AiAssetGeneration $generation): AiAssetGeneration
    {
        if ($generation->status !== AiAssetGeneration::STATUS_PROCESSING || ! $generation->external_id) {
            return $generation;
        }

        $prediction = $this->getPrediction($generation->external_id);
        $status = $prediction['status'] ?? 'starting';

        if (in_array($status, ['starting', 'processing'], true)) {
            return $generation; // still running
        }

        if ($status !== 'succeeded') {
            $generation->update([
                'status' => AiAssetGeneration::STATUS_FAILED,
                'error'  => $prediction['error'] ?? 'La predicción de Replicate falló.',
            ]);

            return $generation->refresh();
        }

        try {
            $url = $this->firstOutputUrl($prediction['output'] ?? null);
            $download = Http::timeout(180)->get($url);
            if ($download->failed()) {
                throw new RuntimeException('No se pudo descargar el video generado.');
            }

            $media = $this->storeBytes($download->body(), 'mp4', 'video/mp4', $generation->user, 'ai_video', $generation->prompt);
            $cost  = $this->quota->pricing()->videoCostCents($generation->model, 1);

            $generation->update([
                'status'      => AiAssetGeneration::STATUS_COMPLETED,
                'media_id'    => $media->id,
                'cost_cents'  => $cost,
                'price_cents' => $this->quota->pricing()->priceCents($cost),
            ]);

            $this->quota->record($generation->user, 'video', 1, $cost);
        } catch (\Throwable $e) {
            $generation->update(['status' => AiAssetGeneration::STATUS_FAILED, 'error' => $e->getMessage()]);
        }

        return $generation->refresh();
    }

    // ── Replicate API helpers ────────────────────────────────────────
    private function createPrediction(string $model, array $input, bool $wait): array
    {
        $response = $this->client($wait)->post(self::BASE . '/models/' . $model . '/predictions', [
            'input' => $input,
        ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('detail') ?? $response->json('title') ?? 'Replicate rechazó la solicitud.');
        }

        return $response->json();
    }

    private function getPrediction(string $id): array
    {
        return $this->client()->get(self::BASE . '/predictions/' . $id)->json() ?? [];
    }

    /** Polls a few times in case `Prefer: wait` returned before completion. */
    private function waitForCompletion(array $prediction): array
    {
        $attempts = 0;

        while (in_array($prediction['status'] ?? 'starting', ['starting', 'processing'], true) && $attempts < 20) {
            sleep(3);
            $prediction = $this->getPrediction($prediction['id'] ?? '');
            $attempts++;
        }

        if (($prediction['status'] ?? null) !== 'succeeded') {
            throw new RuntimeException($prediction['error'] ?? 'La generación en Replicate no se completó.');
        }

        return $prediction;
    }

    /** Replicate output may be a string URL or an array of URLs. */
    private function firstOutputUrl(mixed $output): string
    {
        $url = is_array($output) ? ($output[0] ?? null) : $output;

        if (! is_string($url) || $url === '') {
            throw new RuntimeException('Replicate no devolvió una URL de salida.');
        }

        return $url;
    }

    private function storeBytes(string $bytes, string $ext, string $mime, ?User $user, string $context, string $caption): Media
    {
        $tmp = tempnam(sys_get_temp_dir(), 'aigen_') . '.' . $ext;
        file_put_contents($tmp, $bytes);

        try {
            $file = new UploadedFile($tmp, 'ai-' . $context . '-' . now()->timestamp . '.' . $ext, $mime, null, true);

            return $this->storage->upload($file, $user, null, $context, ['caption' => mb_substr($caption, 0, 480)]);
        } finally {
            @unlink($tmp);
        }
    }
}

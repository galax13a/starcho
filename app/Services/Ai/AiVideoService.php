<?php

namespace App\Services\Ai;

use App\Models\AiAssetGeneration;
use App\Models\AiSetting;
use App\Models\Media;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Video generation via fal.ai (Kling, Veo, MiniMax…).
 *
 * fal.ai is asynchronous: submit() queues the job and returns immediately with
 * status = processing. refresh() polls fal and, once the clip is ready,
 * downloads it into the Media gallery and records cost/price + usage.
 */
class AiVideoService
{
    public function __construct(
        private StorageService $storage,
        private AiQuotaService $quota,
    ) {}

    private function apiKey(): string
    {
        $key = AiSetting::singleton()->fal_api_key;

        if (blank($key)) {
            throw new RuntimeException('Falta la API key de fal.ai para generar videos.');
        }

        return $key;
    }

    /**
     * Image generation via fal.ai synchronous endpoint (fal.run/{model}).
     * Returns immediately with the finished image.
     */
    public function generateImage(string $prompt, ?string $model, ?User $user, array $params = []): AiAssetGeneration
    {
        $model = $model ?: 'fal-ai/flux/schnell';
        $apiKey = $this->apiKey();

        $this->quota->ensureCanGenerate($user, 'image', 1, $this->quota->pricing()->imageCostCents($model, 1));

        $generation = AiAssetGeneration::create([
            'user_id'  => $user?->id,
            'type'     => AiAssetGeneration::TYPE_IMAGE,
            'provider' => 'fal',
            'model'    => $model,
            'status'   => AiAssetGeneration::STATUS_PROCESSING,
            'prompt'   => $prompt,
            'params'   => $params,
        ]);

        $startedAt = microtime(true);

        try {
            $response = Http::withHeaders(['Authorization' => 'Key ' . $apiKey])
                ->timeout(120)
                ->acceptJson()
                ->post('https://fal.run/' . $model, array_merge(['prompt' => $prompt], $params));

            if ($response->failed()) {
                throw new RuntimeException($response->json('detail') ?? 'fal.ai rechazó la solicitud de imagen.');
            }

            $url = $response->json('images.0.url') ?? $response->json('image.url');
            if (! $url) {
                throw new RuntimeException('fal.ai no devolvió la URL de la imagen.');
            }

            $download = Http::timeout(120)->get($url);
            if ($download->failed()) {
                throw new RuntimeException('No se pudo descargar la imagen generada.');
            }

            $media = $this->storeBytes($download->body(), 'png', 'image/png', $user, 'ai_image', $prompt);
            $cost  = $this->quota->pricing()->imageCostCents($model, 1);

            $generation->update([
                'status'      => AiAssetGeneration::STATUS_COMPLETED,
                'media_id'    => $media->id,
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

    public function submit(string $prompt, ?string $model, ?User $user, array $params = []): AiAssetGeneration
    {
        $settings = AiSetting::singleton();
        $model = $model ?: ($settings->video_model ?: 'fal-ai/kling-video/v1/standard/text-to-video');
        $apiKey = $this->apiKey();

        $estCost = $this->quota->pricing()->videoCostCents($model, 1);
        $this->quota->ensureCanGenerate($user, 'video', 1, $estCost);

        $generation = AiAssetGeneration::create([
            'user_id'  => $user?->id,
            'type'     => AiAssetGeneration::TYPE_VIDEO,
            'provider' => 'fal',
            'model'    => $model,
            'status'   => AiAssetGeneration::STATUS_PROCESSING,
            'prompt'   => $prompt,
            'params'   => $params,
        ]);

        try {
            $response = Http::withHeaders(['Authorization' => 'Key ' . $apiKey])
                ->timeout(60)
                ->acceptJson()
                ->post('https://queue.fal.run/' . $model, array_merge(['prompt' => $prompt], $params));

            if ($response->failed()) {
                throw new RuntimeException($response->json('detail') ?? 'fal.ai rechazó la solicitud de video.');
            }

            $generation->update([
                'external_id' => $response->json('request_id'),
                'params'      => array_merge($params, [
                    'status_url'   => $response->json('status_url'),
                    'response_url' => $response->json('response_url'),
                ]),
            ]);
        } catch (\Throwable $e) {
            $generation->update([
                'status' => AiAssetGeneration::STATUS_FAILED,
                'error'  => $e->getMessage(),
            ]);

            throw $e;
        }

        return $generation->refresh();
    }

    /** Polls fal.ai once; finalizes the record when the clip is ready. */
    public function refresh(AiAssetGeneration $generation): AiAssetGeneration
    {
        if ($generation->status !== AiAssetGeneration::STATUS_PROCESSING) {
            return $generation;
        }

        $statusUrl = $generation->params['status_url'] ?? null;
        $responseUrl = $generation->params['response_url'] ?? null;

        if (! $statusUrl) {
            return $generation;
        }

        $apiKey = $this->apiKey();
        $status = Http::withHeaders(['Authorization' => 'Key ' . $apiKey])->acceptJson()->get($statusUrl);

        if ($status->failed()) {
            return $generation;
        }

        $state = $status->json('status');

        if ($state !== 'COMPLETED') {
            return $generation; // still IN_QUEUE / IN_PROGRESS
        }

        try {
            $result = Http::withHeaders(['Authorization' => 'Key ' . $apiKey])->acceptJson()->get($responseUrl);
            $videoUrl = $result->json('video.url') ?? $result->json('videos.0.url');

            if (! $videoUrl) {
                throw new RuntimeException('fal.ai no devolvió la URL del video.');
            }

            $download = Http::timeout(180)->get($videoUrl);

            if ($download->failed()) {
                throw new RuntimeException('No se pudo descargar el video generado.');
            }

            $media = $this->storeBytes($download->body(), 'mp4', 'video/mp4', $generation->user, 'ai_video', $generation->prompt);

            $cost  = $this->quota->pricing()->videoCostCents($generation->model, 1);
            $price = $this->quota->pricing()->priceCents($cost);

            $generation->update([
                'status'      => AiAssetGeneration::STATUS_COMPLETED,
                'media_id'    => $media->id,
                'cost_cents'  => $cost,
                'price_cents' => $price,
            ]);

            $this->quota->record($generation->user, 'video', 1, $cost);
        } catch (\Throwable $e) {
            $generation->update([
                'status' => AiAssetGeneration::STATUS_FAILED,
                'error'  => $e->getMessage(),
            ]);
        }

        return $generation->refresh();
    }

    private function storeBytes(string $bytes, string $ext, string $mime, ?User $user, string $context, string $caption): Media
    {
        $tmp = tempnam(sys_get_temp_dir(), 'aigen_') . '.' . $ext;
        file_put_contents($tmp, $bytes);

        try {
            $file = new UploadedFile($tmp, 'ai-' . $context . '-' . now()->timestamp . '.' . $ext, $mime, null, true);

            return $this->storage->upload($file, $user, null, $context, [
                'caption' => mb_substr($caption, 0, 480),
            ]);
        } finally {
            @unlink($tmp);
        }
    }
}

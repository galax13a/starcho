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
 * Image generation via OpenAI (gpt-image-1 / dall-e-3).
 *
 * Flow: quota check -> call OpenAI -> store the PNG in the Media gallery
 * (converted to WebP by StorageService) -> record cost/price + usage.
 */
class AiImageService
{
    public function __construct(
        private StorageService $storage,
        private AiQuotaService $quota,
    ) {}

    /**
     * @param  string  $size  e.g. 1024x1024, 1024x1536, 1536x1024
     */
    public function generate(string $prompt, ?string $model, ?User $user, string $size = '1024x1024'): AiAssetGeneration
    {
        $settings = AiSetting::singleton();
        $model = $model ?: ($settings->image_model ?: 'gpt-image-1');
        $apiKey = $settings->keyForProvider('openai');

        if (blank($apiKey)) {
            throw new RuntimeException('Falta la API key de OpenAI para generar imágenes.');
        }

        $this->quota->ensureCanGenerate($user, 'image', 1, $this->quota->pricing()->imageCostCents($model, 1));

        $generation = AiAssetGeneration::create([
            'user_id'  => $user?->id,
            'type'     => AiAssetGeneration::TYPE_IMAGE,
            'provider' => 'openai',
            'model'    => $model,
            'status'   => AiAssetGeneration::STATUS_PROCESSING,
            'prompt'   => $prompt,
            'params'   => ['size' => $size],
        ]);

        $startedAt = microtime(true);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->acceptJson()
                ->post('https://api.openai.com/v1/images/generations', [
                    'model'           => $model,
                    'prompt'          => $prompt,
                    'n'               => 1,
                    'size'            => $size,
                    'response_format' => $model === 'dall-e-3' ? 'b64_json' : null,
                ]);

            if ($response->failed()) {
                throw new RuntimeException($response->json('error.message') ?? 'Error de OpenAI al generar la imagen.');
            }

            $bytes = $this->extractImageBytes($response->json('data.0') ?? []);
            $media = $this->storeBytes($bytes, 'png', 'image/png', $user, 'ai_image', $prompt);

            $cost  = $this->quota->pricing()->imageCostCents($model, 1);
            $price = $this->quota->pricing()->priceCents($cost);

            $generation->update([
                'status'      => AiAssetGeneration::STATUS_COMPLETED,
                'media_id'    => $media->id,
                'cost_cents'  => $cost,
                'price_cents' => $price,
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ]);

            $this->quota->record($user, 'image', 1, $cost);
        } catch (\Throwable $e) {
            $generation->update([
                'status' => AiAssetGeneration::STATUS_FAILED,
                'error'  => $e->getMessage(),
            ]);

            throw $e;
        }

        return $generation->refresh();
    }

    private function extractImageBytes(array $data): string
    {
        if (! empty($data['b64_json'])) {
            $decoded = base64_decode($data['b64_json'], true);

            if ($decoded !== false) {
                return $decoded;
            }
        }

        if (! empty($data['url'])) {
            $download = Http::timeout(120)->get($data['url']);

            if ($download->successful()) {
                return $download->body();
            }
        }

        throw new RuntimeException('La respuesta de OpenAI no contenía datos de imagen.');
    }

    /** Stores raw bytes as a Media record by wrapping them in a temp UploadedFile. */
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

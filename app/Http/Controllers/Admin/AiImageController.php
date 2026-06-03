<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\Media;
use App\Models\User;
use App\Services\Ai\AiImageService;
use App\Services\Ai\AiReplicateService;
use App\Services\Ai\AiVideoService;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Generates (or imports) a featured image for the post/page editor and returns
 * the resulting Media so the editor can set it as the featured image.
 */
class AiImageController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function featured(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode'    => ['required', 'in:prompt,article,url'],
            'prompt'  => ['nullable', 'string', 'max:3000'],
            'title'   => ['nullable', 'string', 'max:300'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'url'     => ['nullable', 'url', 'max:2000'],
            'width'   => ['required', 'integer', 'min:64', 'max:2048'],
            'height'  => ['required', 'integer', 'min:64', 'max:2048'],
        ]);

        /** @var User|null $user */
        $user = $request->user();
        $w = (int) $data['width'];
        $h = (int) $data['height'];

        try {
            if ($data['mode'] === 'url') {
                $media = $this->storeFromUrl((string) ($data['url'] ?? ''), $user);
            } else {
                $prompt = $data['mode'] === 'article'
                    ? $this->articlePrompt((string) ($data['title'] ?? ''), (string) ($data['excerpt'] ?? ''))
                    : trim((string) ($data['prompt'] ?? ''));

                if ($prompt === '') {
                    return response()->json(['success' => false, 'message' => 'Falta el prompt o el título del artículo.'], 422);
                }

                $media = $this->generateByProvider($prompt, $user, $w, $h);
            }

            return response()->json([
                'success' => true,
                'media'   => [
                    'id'   => $media->id,
                    'url'  => $media->preview_url ?? $media->public_url,
                    'full' => $media->public_url,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function generateByProvider(string $prompt, ?User $user, int $w, int $h): Media
    {
        $settings = AiSetting::singleton();
        $provider = $settings->image_provider ?: 'openai';
        $model = $settings->image_model;

        $generation = match ($provider) {
            'replicate' => app(AiReplicateService::class)->generateImage($prompt, $model, $user, ['width' => $w, 'height' => $h]),
            'fal'       => app(AiVideoService::class)->generateImage($prompt, $model, $user, ['image_size' => ['width' => $w, 'height' => $h]]),
            default     => app(AiImageService::class)->generate($prompt, $model, $user, $this->openAiSize($w, $h)),
        };

        if (! $generation->media) {
            throw new RuntimeException('La imagen no se pudo generar.');
        }

        return $generation->media;
    }

    private function storeFromUrl(string $url, ?User $user): Media
    {
        $response = Http::timeout(60)->get($url);

        if ($response->failed()) {
            throw new RuntimeException('No se pudo descargar la imagen de esa URL.');
        }

        $mime = $response->header('Content-Type') ?: 'application/octet-stream';

        if (! str_starts_with($mime, 'image/')) {
            throw new RuntimeException('La URL no apunta a una imagen válida.');
        }

        $ext = match (true) {
            str_contains($mime, 'png')  => 'png',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'gif')  => 'gif',
            default                     => 'jpg',
        };

        $tmp = tempnam(sys_get_temp_dir(), 'feat_') . '.' . $ext;
        file_put_contents($tmp, $response->body());

        try {
            $file = new UploadedFile($tmp, 'featured-url-' . now()->timestamp . '.' . $ext, $mime, null, true);

            return $this->storage->upload($file, $user, null, 'featured_url');
        } finally {
            @unlink($tmp);
        }
    }

    private function articlePrompt(string $title, string $excerpt): string
    {
        $title = trim($title);
        $excerpt = trim($excerpt);

        if ($title === '' && $excerpt === '') {
            return '';
        }

        $base = $title !== '' ? "Imagen destacada editorial para un artículo titulado: «{$title}»." : 'Imagen destacada editorial para un artículo.';

        if ($excerpt !== '') {
            $base .= " Contexto: {$excerpt}.";
        }

        return $base . ' Estilo fotográfico profesional, alta calidad, sin texto ni marcas de agua, composición limpia.';
    }

    private function openAiSize(int $w, int $h): string
    {
        if ($h > $w) {
            return '1024x1536';
        }

        if ($w > $h) {
            return '1536x1024';
        }

        return '1024x1024';
    }
}

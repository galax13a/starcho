<?php

namespace App\Services;

use App\Models\Media;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * StorageService
 *
 * Central service for all media uploads / deletes in Starcho.
 *
 * Usage:
 *   $media = app(StorageService::class)->upload($file, auth()->user(), $post, 'gallery');
 *   app(StorageService::class)->delete($media);
 *
 * Drivers (configured in admin → Site → Storage):
 *   - local      → public disk (storage/app/public)
 *   - s3         → Amazon S3
 *   - do_spaces  → DigitalOcean Spaces (S3-compatible)
 *   - r2         → Cloudflare R2 (S3-compatible)
 *
 * All uploaded images are automatically converted to WebP when GD is available.
 * The original file is NOT kept on disk; only the WebP version is stored.
 * Non-image files are stored as-is.
 *
 * Quota:
 *   Upload is refused with QuotaExceededException when the user's storage_plan
 *   would be exceeded. Pass $user = null to bypass quota (e.g. system uploads).
 */
class StorageService
{
    private const IMAGE_VARIANT_QUALITY = 80;

    private StorageSetting $settings;

    public function __construct()
    {
        $this->settings = StorageSetting::singleton();
    }

    // ─────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────

    /**
     * Upload a file and return a persisted Media record.
     *
     * @param  UploadedFile      $file     The uploaded file
     * @param  User|null         $user     Owner (null = no quota check, no attribution)
     * @param  Model|null        $mediable Polymorphic owner (Post, etc.)
     * @param  string            $context  Tag: 'gallery', 'featured_image', 'editor', …
     * @param  array{alt?:string, caption?:string} $meta
     * @throws \RuntimeException on quota exceeded
     */
    public function upload(
        UploadedFile $file,
        ?User $user = null,
        ?Model $mediable = null,
        string $context = 'gallery',
        array $meta = []
    ): Media {
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $isImage  = str_starts_with($mimeType, 'image/');

        // ── Quota check ──────────────────────────────────────────────
        if ($user && $user->storage_plan_id) {
            if ($user->storageExceeded($file->getSize())) {
                throw new \RuntimeException(
                    'Storage quota exceeded. Plan: ' . $user->storagePlan->limitLabel()
                );
            }
        }

        // ── Build destination path ───────────────────────────────────
        $ext        = $isImage ? 'webp' : strtolower($file->getClientOriginalExtension());
        $root       = $this->settings->uploadFolder();
        $subfolder  = $context === 'editor' ? 'media/editor' : 'media/' . date('Y/m');
        $folder     = $root . '/' . $subfolder;
        $filename   = Str::uuid() . '.' . $ext;
        $path       = $folder . '/' . $filename;

        // ── Process & store ──────────────────────────────────────────
        $disk      = $this->disk();
        $diskName  = $this->settings->diskName();
        $storedUrl = null;
        $webpPath  = null;
        [$width, $height] = [null, null];

        if ($isImage) {
            [$content, $width, $height] = $this->convertToWebp($file);
            $disk->put($path, $content, 'public');
        } else {
            $disk->put($path, file_get_contents($file->getRealPath()), 'public');
        }

        // For cloud drivers, capture only truly public URLs. R2's S3 endpoint is private
        // unless a public/custom domain is configured, so the UI will use Laravel's proxy.
        if (! $this->settings->isLocal() && ! ($this->settings->default_driver === 'r2' && blank($this->settings->r2_public_url))) {
            $storedUrl = $disk->url($path);
        }

        if ($isImage) {
            $webpPath = $path;
        }

        // Actual stored size (WebP may differ from original)
        $storedSize = $disk->size($path);

        // ── Persist Media record ─────────────────────────────────────
        $media = Media::create([
            'user_id'       => $user?->id,
            'driver'        => $this->settings->default_driver,
            'disk'          => $diskName,
            'path'          => $path,
            'webp_path'     => $webpPath,
            'url'           => $storedUrl,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $isImage ? 'image/webp' : $mimeType,
            'size'          => $storedSize,
            'width'         => $width,
            'height'        => $height,
            'mediable_type' => $mediable ? get_class($mediable) : null,
            'mediable_id'   => $mediable?->getKey(),
            'context'       => $context,
            'alt'           => $meta['alt'] ?? null,
            'caption'       => $meta['caption'] ?? null,
        ]);

        // ── Update user quota counter ────────────────────────────────
        if ($user) {
            $user->increment('storage_used_bytes', $storedSize);
        }

        if ($media->isImage() && $this->settings->imageVariantsEnabled()) {
            try {
                $this->generateImageVariants($media);
            } catch (\RuntimeException $exception) {
                $this->delete($media);

                throw $exception;
            }
        }

        return $media;
    }

    public function uploadProfileAvatar(UploadedFile $file, User $user): Media
    {
        $avatarSize = $this->settings->avatarSize();
        [$content, $width, $height] = $this->convertImagePathToWebpMax($file->getRealPath(), $avatarSize);
        $size = strlen($content);
        $oldMedia = Media::query()
            ->where('user_id', $user->id)
            ->where('context', 'profile_avatar')
            ->where('path', $user->avatar)
            ->first();
        $oldSize = (int) ($oldMedia?->size ?? 0);

        if ($user->storage_plan_id && $user->storageExceeded(max(0, $size - $oldSize))) {
            throw new \RuntimeException('No hay espacio suficiente en tu plan para subir este avatar.');
        }

        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'avatar';
        $folder = trim($this->settings->uploadFolder() . '/profiles/avatars/' . $user->id, '/');
        $path = $folder . '/' . $baseName . '-' . Str::random(10) . '.webp';
        $disk = $this->disk();
        $diskName = $this->settings->diskName();

        $disk->put($path, $content, 'public');

        if ($oldMedia) {
            $this->delete($oldMedia);
        } elseif ($user->avatar && ! Str::startsWith($user->avatar, ['http://', 'https://'])) {
            $oldDisk = $user->avatar && ($user->avatar !== $path) ? $this->disk() : null;

            if ($oldDisk && $oldDisk->exists($user->avatar)) {
                $oldDisk->delete($user->avatar);
            }
        }

        $media = Media::create([
            'user_id' => $user->id,
            'driver' => $this->settings->default_driver,
            'disk' => $diskName,
            'path' => $path,
            'webp_path' => $path,
            'url' => null,
            'variants' => null,
            'variants_size' => 0,
            'original_name' => $baseName . '.webp',
            'display_name' => 'Avatar de ' . $user->name,
            'mime_type' => 'image/webp',
            'size' => $size,
            'width' => $width,
            'height' => $height,
            'context' => 'profile_avatar',
            'alt' => 'Avatar de ' . $user->name,
        ]);

        $user->forceFill(['avatar' => $path])->save();

        if ($user->storage_plan_id) {
            $user->increment('storage_used_bytes', $size);
        }

        return $media;
    }

    /**
     * Delete a media record and its file(s) from disk.
     */
    public function delete(Media $media): void
    {
        $disk = $this->diskFor($media);

        if ($media->path && $disk->exists($media->path)) {
            $disk->delete($media->path);
        }

        if ($media->webp_path && $media->webp_path !== $media->path && $disk->exists($media->webp_path)) {
            $disk->delete($media->webp_path);
        }

        foreach (($media->variants ?? []) as $variant) {
            $path = $variant['path'] ?? null;

            if ($path && $path !== $media->path && $disk->exists($path)) {
                $disk->delete($path);
            }
        }

        // Decrement owner's quota counter
        if ($media->user_id) {
            $user = $media->user;
            if ($user) {
                $user->decrement('storage_used_bytes', max(0, $media->totalSize()));
            }
        }

        $media->delete();
    }

    /**
     * Generate responsive WebP copies for an existing image.
     */
    public function generateImageVariants(Media $media, bool $force = false): Media
    {
        if (! $this->settings->imageVariantsEnabled() || ! $media->isImage() || ! function_exists('imagecreatefromstring')) {
            return $media;
        }

        $disk = $this->diskFor($media);

        if (! $media->path || ! $disk->exists($media->path)) {
            return $media;
        }

        $source = @imagecreatefromstring($disk->get($media->path));

        if ($source === false) {
            return $media;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $sourceMax = max($sourceWidth, $sourceHeight);
        $basename = pathinfo($media->path, PATHINFO_FILENAME);
        $folder = trim(dirname($media->path), '.');
        $variantFolder = ($folder === '' ? '' : $folder . '/') . 'variants';
        $oldVariants = $media->variants ?? [];
        $existing = $force ? [] : $oldVariants;
        $variants = $existing;
        $pendingWrites = [];

        foreach ($this->settings->imageVariantSizes() as $size) {
            $key = (string) $size;
            $targetPath = $variantFolder . '/' . $basename . '-' . $size . '.webp';

            if (! $force && isset($existing[$key]['path']) && $disk->exists($existing[$key]['path'])) {
                $variants[$key] = $existing[$key];
                continue;
            }

            if ($sourceMax <= $size && $size !== $this->settings->imagePreviewVariantSize()) {
                continue;
            }

            $variant = $this->resizeImageResourceToWebp($source, $sourceWidth, $sourceHeight, min($size, $sourceMax));

            if (! $variant) {
                continue;
            }

            $pendingWrites[$targetPath] = $variant['content'];

            $variants[$key] = [
                'path' => $targetPath,
                'width' => $variant['width'],
                'height' => $variant['height'],
                'size' => strlen($variant['content']),
                'mime_type' => 'image/webp',
            ];
        }

        imagedestroy($source);

        $oldVariantsSize = (int) $media->variants_size;
        $newVariantsSize = collect($variants)->sum(fn (array $variant) => (int) ($variant['size'] ?? 0));

        if ($media->user_id && $newVariantsSize > $oldVariantsSize) {
            $diff = $newVariantsSize - $oldVariantsSize;
            $user = $media->user;

            if ($user && $user->storageExceeded($diff)) {
                throw new \RuntimeException(
                    'No hay espacio suficiente para generar las copias responsive. Plan: ' . ($user->storagePlan?->limitLabel() ?? 'sin límite')
                );
            }
        }

        if ($force) {
            foreach ($oldVariants as $variant) {
                $path = $variant['path'] ?? null;

                if ($path && $path !== $media->path && $disk->exists($path)) {
                    $disk->delete($path);
                }
            }
        }

        foreach ($pendingWrites as $targetPath => $content) {
            $disk->put($targetPath, $content, 'public');
        }

        $media->forceFill([
            'variants' => $variants ?: null,
            'variants_size' => $newVariantsSize,
        ])->save();

        if ($media->user_id && $newVariantsSize !== $oldVariantsSize) {
            $diff = $newVariantsSize - $oldVariantsSize;

            if ($diff > 0) {
                $media->user?->increment('storage_used_bytes', $diff);
            } else {
                $media->user?->decrement('storage_used_bytes', abs($diff));
            }
        }

        return $media->refresh();
    }

    /**
     * Return the filesystem disk for an existing Media record.
     * Cloud disks are configured at runtime, so persisted disk names like
     * starcho_r2 must be registered before Storage::disk() is called.
     */
    public function diskFor(Media $media): Filesystem
    {
        if ($media->driver === 'local' || $media->disk === 'public') {
            return Storage::disk('public');
        }

        $driver = $media->driver ?: $this->settings->default_driver;
        $diskName = $media->disk ?: $this->diskNameForDriver($driver);

        config(['filesystems.disks.' . $diskName => $this->buildDiskConfig($driver)]);

        return Storage::disk($diskName);
    }

    /**
     * Return the active Filesystem disk instance.
     */
    public function disk(): Filesystem
    {
        $driver = $this->settings->default_driver;

        if ($driver === 'local') {
            return Storage::disk('public');
        }

        // Dynamically configure the cloud disk at runtime from DB settings
        $diskConfig = $this->buildDiskConfig($driver);
        config(['filesystems.disks.' . $this->settings->diskName() => $diskConfig]);

        return Storage::disk($this->settings->diskName());
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────

    /** Convert any image UploadedFile to WebP using PHP GD. Returns [string $content, int $w, int $h]. */
    private function convertToWebp(UploadedFile $file): array
    {
        if (! function_exists('imagecreatefromstring')) {
            // GD not available — store original bytes, no resize
            $content = file_get_contents($file->getRealPath());
            [$w, $h]  = @getimagesize($file->getRealPath()) ?: [null, null];
            return [$content, $w, $h];
        }

        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if ($source === false) {
            // Not a valid image for GD (e.g. SVG) — pass through
            return [file_get_contents($file->getRealPath()), null, null];
        }

        $w = imagesx($source);
        $h = imagesy($source);

        // Preserve transparency
        $output = imagecreatetruecolor($w, $h);
        imagealphablending($output, false);
        imagesavealpha($output, true);
        $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
        imagefilledrectangle($output, 0, 0, $w, $h, $transparent);
        imagecopy($output, $source, 0, 0, 0, 0, $w, $h);

        ob_start();
        imagewebp($output, null, 82); // quality 82 — good balance
        $content = ob_get_clean();

        imagedestroy($source);
        imagedestroy($output);

        return [$content, $w, $h];
    }

    private function resizeImageResourceToWebp($source, int $sourceWidth, int $sourceHeight, int $maxSize): ?array
    {
        $scale = min($maxSize / $sourceWidth, $maxSize / $sourceHeight, 1);
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));

        $output = imagecreatetruecolor($width, $height);

        if ($output === false) {
            return null;
        }

        imagealphablending($output, false);
        imagesavealpha($output, true);
        $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
        imagefilledrectangle($output, 0, 0, $width, $height, $transparent);
        imagecopyresampled($output, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        ob_start();
        imagewebp($output, null, self::IMAGE_VARIANT_QUALITY);
        $content = ob_get_clean();

        imagedestroy($output);

        if (! is_string($content) || $content === '') {
            return null;
        }

        return compact('content', 'width', 'height');
    }

    private function convertImagePathToWebpMax(string $path, int $maxSize): array
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('GD es requerido para convertir la imagen a WebP.');
        }

        $source = @imagecreatefromstring(file_get_contents($path));

        if ($source === false) {
            throw new \RuntimeException('No se pudo procesar la imagen seleccionada.');
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $output = imagecreatetruecolor($maxSize, $maxSize);

        if ($output === false) {
            imagedestroy($source);

            throw new \RuntimeException('No se pudo preparar el avatar.');
        }

        imagealphablending($output, false);
        imagesavealpha($output, true);
        $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
        imagefilledrectangle($output, 0, 0, $maxSize, $maxSize, $transparent);

        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = 1;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $srcX = (int) floor(($sourceWidth - $cropWidth) / 2);
            $srcY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $srcX = 0;
            $srcY = (int) floor(($sourceHeight - $cropHeight) / 2);
        }

        imagecopyresampled($output, $source, 0, 0, $srcX, $srcY, $maxSize, $maxSize, $cropWidth, $cropHeight);

        ob_start();
        imagewebp($output, null, 86);
        $content = ob_get_clean();

        imagedestroy($output);
        imagedestroy($source);

        if (! is_string($content) || $content === '') {
            throw new \RuntimeException('No se pudo convertir la imagen a WebP.');
        }

        return [$content, $maxSize, $maxSize];
    }

    /** Build Laravel filesystem disk config array for S3-compatible providers. */
    private function buildDiskConfig(string $driver): array
    {
        $s = $this->settings;

        return match ($driver) {
            's3' => [
                'driver'                  => 's3',
                'key'                     => $s->s3_key,
                'secret'                  => $s->s3_secret,
                'region'                  => $s->s3_region ?? 'us-east-1',
                'bucket'                  => $s->s3_bucket,
                'url'                     => $s->s3_url,
                'endpoint'                => $s->s3_endpoint ?: null,
                'use_path_style_endpoint' => (bool) $s->s3_use_path_style,
                'visibility'              => 'public',
            ],
            'do_spaces' => [
                'driver'                  => 's3',
                'key'                     => $s->do_key,
                'secret'                  => $s->do_secret,
                'region'                  => $s->do_region ?? 'nyc3',
                'bucket'                  => $s->do_bucket,
                'endpoint'                => $s->do_endpoint,
                'url'                     => $s->do_cdn_url ?: null,
                'use_path_style_endpoint' => false,
                'visibility'              => 'public',
            ],
            'r2' => [
                'driver'                  => 's3',
                'key'                     => $s->r2_key,
                'secret'                  => $s->r2_secret,
                'region'                  => 'auto',
                'bucket'                  => $s->r2_bucket,
                'endpoint'                => $s->r2_endpoint,
                'url'                     => $s->r2_public_url ?: null,
                'use_path_style_endpoint' => false,
                'visibility'              => 'public',
            ],
            default => ['driver' => 'local', 'root' => storage_path('app/public'), 'visibility' => 'public'],
        };
    }

    private function diskNameForDriver(string $driver): string
    {
        return match ($driver) {
            's3'        => 'starcho_s3',
            'do_spaces' => 'starcho_do',
            'r2'        => 'starcho_r2',
            default     => 'public',
        };
    }
}

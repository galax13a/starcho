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

        // For cloud drivers, capture the full URL
        if (! $this->settings->isLocal()) {
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

        return $media;
    }

    /**
     * Delete a media record and its file(s) from disk.
     */
    public function delete(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        if ($media->path && $disk->exists($media->path)) {
            $disk->delete($media->path);
        }

        if ($media->webp_path && $media->webp_path !== $media->path && $disk->exists($media->webp_path)) {
            $disk->delete($media->webp_path);
        }

        // Decrement owner's quota counter
        if ($media->user_id) {
            $user = $media->user;
            if ($user) {
                $user->decrement('storage_used_bytes', max(0, $media->size));
            }
        }

        $media->delete();
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
}

<?php

namespace App\Services;

use App\Jobs\GenerateMediaVariants;
use App\Models\Media;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

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
     * @param  UploadedFile  $file  The uploaded file
     * @param  User|null  $user  Owner (null = no quota check, no attribution)
     * @param  Model|null  $mediable  Polymorphic owner (Post, etc.)
     * @param  string  $context  Tag: 'gallery', 'featured_image', 'editor', …
     * @param  array{alt?:string, caption?:string}  $meta
     *
     * @throws \RuntimeException on quota exceeded
     */
    public function upload(
        UploadedFile $file,
        ?User $user = null,
        ?Model $mediable = null,
        string $context = 'gallery',
        array $meta = [],
        string $visibility = 'public'
    ): Media {
        if (! in_array($visibility, Media::VISIBILITIES, true)) {
            throw new \InvalidArgumentException('Invalid media visibility.');
        }

        $mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $isImage = str_starts_with($mimeType, 'image/');

        // Reserve against the current database value before doing CPU or disk work.
        // The conditional UPDATE makes concurrent requests compete for the same quota.
        $reservedBytes = $file->getSize();
        if ($user) {
            $this->reserveStorage($user, $reservedBytes);
        }

        // ── Build destination path ───────────────────────────────────
        $ext = $isImage ? 'webp' : strtolower($file->getClientOriginalExtension());
        $root = $this->settings->uploadFolder();
        $subfolder = $context === 'editor' ? 'media/editor' : 'media/'.date('Y/m');
        $folder = $root.'/'.$subfolder;
        $filename = Str::uuid().'.'.$ext;
        $path = $folder.'/'.$filename;

        // ── Process & store ──────────────────────────────────────────
        $restricted = $visibility !== 'public';
        $privateDriver = $restricted ? ($this->settings->private_driver ?: 'local') : null;
        $privateBucket = $restricted && $privateDriver !== 'local'
            ? $this->settings->privateBucket($privateDriver)
            : null;
        $disk = $restricted ? $this->privateDisk($privateDriver, $privateBucket) : $this->disk();
        $diskName = $restricted ? 'starcho_private' : $this->settings->diskName();
        $storedDriver = $restricted ? $privateDriver : $this->settings->default_driver;
        $storedUrl = null;
        $webpPath = null;
        [$width, $height] = [null, null];

        $writeAttempted = false;
        $media = null;

        try {
            if ($isImage) {
                [$content, $width, $height] = $this->convertToWebp($file);
            } else {
                $content = file_get_contents($file->getRealPath());
            }

            $writeAttempted = true;
            if (! is_string($content) || ! $disk->put($path, $content, $restricted ? ['visibility' => 'private'] : 'public')) {
                throw new \RuntimeException('No se pudo guardar el archivo multimedia.');
            }

            // For cloud drivers, capture only truly public URLs. R2's S3 endpoint is private
            // unless a public/custom domain is configured, so the UI will use Laravel's proxy.
            if (! $restricted && ! $this->settings->isLocal() && ! ($this->settings->default_driver === 'r2' && blank($this->settings->r2_public_url))) {
                $storedUrl = $disk->url($path);
            }

            if ($isImage) {
                $webpPath = $path;
            }

            // WebP conversion can change the byte count; reconcile the reservation before persisting.
            $storedSize = $disk->size($path);
            if ($user && $storedSize !== $reservedBytes) {
                $this->adjustStorageReservation($user, $reservedBytes, $storedSize);
                $reservedBytes = $storedSize;
            }

            $media = Media::create([
                'user_id' => $user?->id,
                'driver' => $storedDriver,
                'disk' => $diskName,
                'private_bucket' => $privateBucket,
                'path' => $path,
                'webp_path' => $webpPath,
                'url' => $storedUrl,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $isImage ? 'image/webp' : $mimeType,
                'size' => $storedSize,
                'width' => $width,
                'height' => $height,
                'mediable_type' => $mediable ? get_class($mediable) : null,
                'mediable_id' => $mediable?->getKey(),
                'context' => $context,
                'visibility' => $visibility,
                'alt' => $meta['alt'] ?? null,
                'caption' => $meta['caption'] ?? null,
            ]);

            if ($media->isImage() && $this->settings->imageVariantsEnabled()) {
                // Keep the original usable immediately; expensive responsive copies
                // are handled by a retryable worker after this request returns.
                $this->queueImageVariants($media);
            }

            return $media;
        } catch (Throwable $exception) {
            // Compensate for a DB or image-variant failure; retain the reservation if
            // cleanup itself fails so the leaked bytes cannot be uploaded over quota.
            $cleaned = true;
            try {
                if ($media?->exists) {
                    $this->delete($media, adjustStorageUsage: false);
                } elseif ($writeAttempted && $disk->exists($path)) {
                    $cleaned = $disk->delete($path) && ! $disk->exists($path);
                }
            } catch (Throwable $cleanupException) {
                $cleaned = false;
                Log::error('Failed to clean up a media upload after an error.', [
                    'path' => $path,
                    'exception' => $cleanupException->getMessage(),
                ]);
            }

            if ($user && $cleaned) {
                $this->releaseStorage($user, $reservedBytes);
            }

            throw $exception;
        }
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
        $oldSize = (int) ($oldMedia->size ?? 0);
        $oldAvatarPath = $user->avatar;

        // Reserve only the growth over the existing avatar so a replacement can
        // succeed at quota; the old file remains intact until the new row is saved.
        $reservedBytes = max(0, $size - $oldSize);
        $this->reserveStorage($user, $reservedBytes, 'No hay espacio suficiente en tu plan para subir este avatar.');

        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'avatar';
        $folder = trim($this->settings->uploadFolder().'/profiles/avatars/'.$user->id, '/');
        $path = $folder.'/'.$baseName.'-'.Str::random(10).'.webp';
        $disk = $this->disk();
        $diskName = $this->settings->diskName();

        $writeAttempted = false;
        $media = null;
        $avatarSwitched = false;

        try {
            $writeAttempted = true;
            if (! $disk->put($path, $content, 'public')) {
                throw new \RuntimeException('No se pudo guardar el nuevo avatar.');
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
                'original_name' => $baseName.'.webp',
                'display_name' => 'Avatar de '.$user->name,
                'mime_type' => 'image/webp',
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'context' => 'profile_avatar',
                'alt' => 'Avatar de '.$user->name,
            ]);

            $user->forceFill(['avatar' => $path])->save();
            $avatarSwitched = true;

            // Only remove the previous avatar after the replacement is addressable.
            if ($oldMedia) {
                try {
                    $this->delete($oldMedia, adjustStorageUsage: false);
                    if ($size < $oldSize) {
                        $this->releaseStorage($user, $oldSize - $size);
                    }
                } catch (Throwable $cleanupException) {
                    // Keep quota truthful if both old and new files remain, even if
                    // that means the account temporarily exceeds its plan limit.
                    $overlap = min($oldSize, $size);
                    if ($overlap > 0) {
                        User::query()->whereKey($user->id)->increment('storage_used_bytes', $overlap);
                    }
                    Log::error('The old profile avatar could not be removed after replacement.', [
                        'media_id' => $oldMedia->id,
                        'path' => $oldMedia->path,
                        'exception' => $cleanupException->getMessage(),
                    ]);
                }
            } elseif ($oldAvatarPath && ! Str::startsWith($oldAvatarPath, ['http://', 'https://']) && $oldAvatarPath !== $path) {
                $oldDisk = $this->disk();
                if ($oldDisk->exists($oldAvatarPath)) {
                    $oldDisk->delete($oldAvatarPath);
                }
            }

            return $media;
        } catch (Throwable $exception) {
            if (! $avatarSwitched) {
                $cleaned = true;
                try {
                    if ($media?->exists) {
                        $this->delete($media, adjustStorageUsage: false);
                    } elseif ($writeAttempted && $disk->exists($path)) {
                        $cleaned = $disk->delete($path) && ! $disk->exists($path);
                    }
                } catch (Throwable $cleanupException) {
                    $cleaned = false;
                    Log::error('Failed to clean up a profile avatar upload after an error.', [
                        'path' => $path,
                        'exception' => $cleanupException->getMessage(),
                    ]);
                }

                if ($cleaned) {
                    $this->releaseStorage($user, $reservedBytes);
                }
            }

            throw $exception;
        }
    }

    /**
     * Store a site-level asset (favicon, Open Graph image) on the active driver.
     * Unlike normal media uploads, this preserves the original file bytes and
     * extension so .ico favicons never get mislabeled as WebP.
     */
    public function uploadSiteAsset(UploadedFile $file, string $context): Media
    {
        $mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $isConvertibleOgImage = $context === 'site_og_image'
            && str_starts_with($mimeType, 'image/')
            && $mimeType !== 'image/svg+xml'
            && function_exists('imagecreatefromstring');
        $extension = $isConvertibleOgImage
            ? 'webp'
            : strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $root = $this->settings->uploadFolder();
        $folder = trim($root.'/site', '/');
        $filename = Str::uuid().'.'.$extension;
        $path = $folder.'/'.$filename;
        $disk = $this->disk();
        $diskName = $this->settings->diskName();
        $storedUrl = null;

        if ($isConvertibleOgImage) {
            [$content, $width, $height] = $this->convertToWebp($file);
            $mimeType = 'image/webp';
        } else {
            $content = file_get_contents($file->getRealPath());
            [$width, $height] = str_starts_with($mimeType, 'image/')
                ? (@getimagesize($file->getRealPath()) ?: [null, null])
                : [null, null];
        }

        $writeAttempted = false;

        try {
            $writeAttempted = true;
            if (! is_string($content) || ! $disk->put($path, $content, 'public')) {
                throw new \RuntimeException('No se pudo guardar el recurso del sitio.');
            }

            if (! $this->settings->isLocal() && ! ($this->settings->default_driver === 'r2' && blank($this->settings->r2_public_url))) {
                $storedUrl = $disk->url($path);
            }

            return Media::create([
                'user_id' => auth()->id(),
                'driver' => $this->settings->default_driver,
                'disk' => $diskName,
                'path' => $path,
                'webp_path' => $isConvertibleOgImage ? $path : null,
                'url' => $storedUrl,
                'variants' => null,
                'variants_size' => 0,
                'original_name' => $file->getClientOriginalName(),
                'display_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size' => $disk->size($path),
                'width' => is_numeric($width) ? (int) $width : null,
                'height' => is_numeric($height) ? (int) $height : null,
                'context' => $context,
                'alt' => $context === 'site_favicon' ? 'Site favicon' : 'Open Graph image',
            ]);
        } catch (Throwable $exception) {
            if ($writeAttempted) {
                try {
                    if ($disk->exists($path) && (! $disk->delete($path) || $disk->exists($path))) {
                        Log::error('Failed to clean up a site asset after its database write failed.', ['path' => $path]);
                    }
                } catch (Throwable $cleanupException) {
                    Log::error('Failed to clean up a site asset after its database write failed.', [
                        'path' => $path,
                        'exception' => $cleanupException->getMessage(),
                    ]);
                }
            }

            throw $exception;
        }
    }

    /**
     * Delete a media record and its file(s) from disk.
     */
    public function delete(Media $media, bool $adjustStorageUsage = true): void
    {
        $disk = $this->diskFor($media);
        $paths = collect([$media->path, $media->webp_path])
            ->merge(collect($media->variants ?? [])->pluck('path'))
            ->filter()
            ->unique();

        foreach ($paths as $path) {
            if ($disk->exists($path) && (! $disk->delete($path) || $disk->exists($path))) {
                // Keep the DB row and quota reservation if the physical delete failed.
                throw new \RuntimeException("No se pudo eliminar el archivo multimedia {$media->id} del storage.");
            }
        }

        if ($adjustStorageUsage && $media->user_id && ! str_starts_with((string) $media->context, 'site_')) {
            $this->releaseStorage($media->user_id, max(0, $media->totalSize()));
        }

        $media->delete();
    }

    /**
     * Queue variant generation without delaying the original upload response.
     * A dispatch failure is visible on the media row; the original remains usable.
     */
    public function queueImageVariants(Media $media, bool $force = false): bool
    {
        if (! $media->isImage() || ! $this->settings->imageVariantsEnabled()) {
            return false;
        }

        $media->forceFill(['variants_status' => 'queued', 'variants_error' => null])->save();

        try {
            GenerateMediaVariants::dispatch((int) $media->getKey(), $force)->afterCommit();

            return true;
        } catch (Throwable $exception) {
            // A temporarily unavailable queue must not discard the successfully stored original.
            $media->forceFill([
                'variants_status' => 'failed',
                'variants_error' => Str::limit($exception->getMessage(), 1000),
            ])->save();
            Log::error('Could not enqueue image variant generation.', [
                'media_id' => $media->id,
                'exception' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate responsive WebP copies for an existing image.
     */
    public function generateImageVariants(Media $media, bool $force = false): Media
    {
        if (! $this->settings->imageVariantsEnabled() || ! $media->isImage()) {
            return $media;
        }

        if (! function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('GD es requerido para generar variantes de imagen.');
        }

        $disk = $this->diskFor($media);

        if (! $media->path || ! $disk->exists($media->path)) {
            throw new \RuntimeException("No se encontró el archivo fuente para el medio {$media->id}.");
        }

        $source = @imagecreatefromstring($disk->get($media->path));

        if ($source === false) {
            throw new \RuntimeException("No se pudo leer la imagen fuente para el medio {$media->id}.");
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $sourceMax = max($sourceWidth, $sourceHeight);
        $basename = pathinfo($media->path, PATHINFO_FILENAME);
        $folder = trim(dirname($media->path), '.');
        $variantFolder = ($folder === '' ? '' : $folder.'/').'variants';
        $oldVariants = $media->variants ?? [];
        $variants = $force ? [] : $oldVariants;
        $pendingWrites = [];

        foreach ($this->settings->imageVariantSizes() as $size) {
            $key = (string) $size;
            // Unique names let us finish the new set before touching files used by the old metadata.
            $targetPath = $variantFolder.'/'.$basename.'-'.$size.'-'.Str::uuid().'.webp';

            if (! $force && isset($variants[$key]['path']) && $disk->exists($variants[$key]['path'])) {

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

        $newVariantsSize = collect($variants)->sum(fn (array $variant) => (int) ($variant['size'] ?? 0));

        // Reserve only the bytes about to be written. Old variants remain available
        // until the new metadata commits, so replacement never creates broken URLs.
        $pendingBytes = collect($pendingWrites)->sum(fn (string $content) => strlen($content));
        if ($media->user_id && ! str_starts_with((string) $media->context, 'site_')) {
            $this->reserveStorage(
                $media->user_id,
                $pendingBytes,
                'No hay espacio suficiente para generar las copias responsive.'
            );
        }

        $attemptedPaths = [];
        try {
            foreach ($pendingWrites as $targetPath => $content) {
                $attemptedPaths[] = $targetPath;
                if (! $disk->put($targetPath, $content, $media->disk === 'starcho_private' ? ['visibility' => 'private'] : 'public')) {
                    throw new \RuntimeException('No se pudo guardar una copia responsive.');
                }
            }

            $media->forceFill([
                'variants' => $variants ?: null,
                'variants_size' => $newVariantsSize,
                'variants_status' => 'ready',
                'variants_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $remainingReserved = 0;
            foreach ($attemptedPaths as $attemptedPath) {
                try {
                    if ($disk->exists($attemptedPath) && (! $disk->delete($attemptedPath) || $disk->exists($attemptedPath))) {
                        $remainingReserved += (int) ($pendingWrites[$attemptedPath] ? strlen($pendingWrites[$attemptedPath]) : 0);
                    }
                } catch (Throwable $cleanupException) {
                    $remainingReserved += (int) ($pendingWrites[$attemptedPath] ? strlen($pendingWrites[$attemptedPath]) : 0);
                    Log::error('Failed to clean up a responsive image after generation failed.', [
                        'path' => $attemptedPath,
                        'exception' => $cleanupException->getMessage(),
                    ]);
                }
            }

            if ($media->user_id && ! str_starts_with((string) $media->context, 'site_')) {
                $this->releaseStorage($media->user_id, max(0, $pendingBytes - $remainingReserved));
            }

            throw $exception;
        }

        // Metadata now points at the complete new variant set. Remove replaced
        // objects and release only the bytes that were actually deleted.
        $newPaths = collect($variants)->pluck('path')->filter()->all();
        $releasedBytes = 0;
        foreach ($oldVariants as $key => $variant) {
            $oldPath = $variant['path'] ?? null;
            if (! $oldPath || in_array($oldPath, $newPaths, true) || ! $disk->exists($oldPath)) {
                continue;
            }

            try {
                if ($disk->delete($oldPath) && ! $disk->exists($oldPath)) {
                    $releasedBytes += (int) ($variant['size'] ?? 0);
                }
            } catch (Throwable $cleanupException) {
                Log::warning('A replaced responsive image could not be removed.', [
                    'media_id' => $media->id,
                    'path' => $oldPath,
                    'exception' => $cleanupException->getMessage(),
                ]);
            }
        }

        if ($media->user_id && ! str_starts_with((string) $media->context, 'site_') && $releasedBytes > 0) {
            $this->releaseStorage($media->user_id, $releasedBytes);
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
        if ($media->disk === 'starcho_private') {
            // Keep routing tied to the asset's saved location, not today's admin selection.
            return $this->privateDisk($media->driver ?: 'local', $media->private_bucket);
        }

        if ($media->driver === 'local' || $media->disk === 'public') {
            return Storage::disk('public');
        }

        $driver = $media->driver ?: $this->settings->default_driver;
        $diskName = $media->disk ?: $this->diskNameForDriver($driver);

        config(['filesystems.disks.'.$diskName => $this->buildDiskConfig($driver)]);

        return Storage::disk($diskName);
    }

    /** Resolve a saved private-storage location for read-only maintenance commands. */
    public function privateDiskFor(string $driver, ?string $bucket = null): Filesystem
    {
        return $this->privateDisk($driver, $bucket);
    }

    /**
     * Move an asset and every generated variant onto a private disk, removing
     * the old public object only after the private copies are in place.
     * Persist a restrictive visibility or album policy before calling this
     * method; a failed move must leave the asset denied by its DB policy.
     */
    public function moveToPrivate(Media $media): void
    {
        $targetDriver = $this->settings->private_driver ?: 'local';
        $targetBucket = $targetDriver === 'local' ? null : $this->settings->privateBucket($targetDriver);

        if ($media->disk === 'starcho_private'
            && $media->driver === $targetDriver
            && $media->private_bucket === $targetBucket) {
            if ($media->getRawOriginal('url') !== null) {
                $media->forceFill(['url' => null])->save();
            }

            return;
        }

        $source = $this->diskFor($media);
        $destination = $this->privateDisk($targetDriver, $targetBucket);
        $paths = collect([$media->path, $media->webp_path])
            ->merge(collect($media->variants ?? [])->pluck('path'))
            ->filter()
            ->unique()
            ->values();
        $copiedPaths = [];

        if (! filled($media->path)) {
            throw new \RuntimeException("El archivo multimedia {$media->id} no tiene una ruta de origen.");
        }

        foreach ($paths as $path) {
            if (! $source->exists($path)) {
                if ($destination->exists($path)) {
                    // A previous attempt may have removed the public object but
                    // failed before updating the database record.
                    $copiedPaths[] = $path;

                    continue;
                }

                if ($path === $media->path) {
                    throw new \RuntimeException("No se encontró el archivo multimedia {$media->id} en el storage de origen.");
                }

                // Tolerate stale optional WebP/variant paths; they were already
                // unavailable to readers and must not block securing the source.
                continue;
            }

            $stream = $source->readStream($path);

            if (! is_resource($stream)) {
                throw new \RuntimeException("No se pudo leer el archivo multimedia {$media->id}.");
            }

            try {
                $stored = $destination->put($path, $stream, ['visibility' => 'private']);
            } finally {
                fclose($stream);
            }

            if (! $stored || ! $destination->exists($path)) {
                throw new \RuntimeException("No se pudo guardar en el storage privado el archivo multimedia {$media->id}.");
            }

            $copiedPaths[] = $path;
        }

        // Do not report success while a public origin object remains reachable.
        // The visibility policy is persisted by the caller first, so a failure
        // leaves the record denied by the application and safely retryable.
        foreach (array_unique($copiedPaths) as $path) {
            if ($source->exists($path)) {
                $source->delete($path);
            }

            if ($source->exists($path)) {
                throw new \RuntimeException("No se pudo eliminar del storage público el archivo multimedia {$media->id}.");
            }
        }

        $media->forceFill([
            'driver' => $targetDriver,
            'disk' => 'starcho_private',
            'private_bucket' => $targetBucket,
            'url' => null,
        ])->save();
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
        config(['filesystems.disks.'.$this->settings->diskName() => $diskConfig]);

        return Storage::disk($this->settings->diskName());
    }

    /** Verify private credentials and bucket access without leaving a test object behind. */
    public function testPrivateStorageConnection(): string
    {
        $driver = $this->settings->private_driver ?: 'local';
        $bucket = $driver === 'local' ? null : $this->settings->privateBucket($driver);
        $disk = $this->privateDisk($driver, $bucket);
        $path = '.starcho-test/private-'.Str::uuid().'.txt';

        try {
            if (! $disk->put($path, 'Starcho private storage test', ['visibility' => 'private'])
                || ! $disk->exists($path)) {
                throw new \RuntimeException('No se pudo escribir y verificar el archivo de prueba privado.');
            }
        } finally {
            // A failed check may still have created an object, so cleanup is unconditional.
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }

        return $driver;
    }

    /**
     * Resolve a public URL for a raw path using the active storage settings.
     * Local storage uses the configured local_url/canonical domain; R2 uses
     * its public URL when present and falls back to a temporary URL otherwise.
     */
    public function publicUrlForPath(string $path): string
    {
        $path = ltrim($path, '/');

        if ($this->settings->isLocal()) {
            return $this->settings->localPublicUrl($path);
        }

        if ($this->settings->default_driver === 'r2' && filled($this->settings->r2_public_url)) {
            return rtrim((string) $this->settings->r2_public_url, '/').'/'.$path;
        }

        $disk = $this->disk();

        if ($this->settings->default_driver === 'r2' && method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl($path, now()->addMinutes(30));
        }

        return $disk->url($path);
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
            [$w, $h] = @getimagesize($file->getRealPath()) ?: [null, null];

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
                'driver' => 's3',
                'key' => $s->s3_key,
                'secret' => $s->s3_secret,
                'region' => $s->s3_region ?? 'us-east-1',
                'bucket' => $s->s3_bucket,
                'url' => $s->s3_url,
                'endpoint' => $s->s3_endpoint ?: null,
                'use_path_style_endpoint' => (bool) $s->s3_use_path_style,
                'visibility' => 'public',
            ],
            'do_spaces' => [
                'driver' => 's3',
                'key' => $s->do_key,
                'secret' => $s->do_secret,
                'region' => $s->do_region ?? 'nyc3',
                'bucket' => $s->do_bucket,
                'endpoint' => $s->do_endpoint,
                'url' => $s->do_cdn_url ?: null,
                'use_path_style_endpoint' => false,
                'visibility' => 'public',
            ],
            'r2' => [
                'driver' => 's3',
                'key' => $s->r2_key,
                'secret' => $s->r2_secret,
                'region' => 'auto',
                'bucket' => $s->r2_bucket,
                'endpoint' => $s->r2_endpoint,
                'url' => $s->r2_public_url ?: null,
                'use_path_style_endpoint' => false,
                'visibility' => 'public',
            ],
            default => ['driver' => 'local', 'root' => storage_path('app/public'), 'visibility' => 'public'],
        };
    }

    private function configurePrivateDisk(string $driver, ?string $bucket = null): string
    {
        if ($driver === 'local') {
            // Keep the original private local disk as the backward-compatible default.
            config(['filesystems.disks.starcho_private' => [
                'driver' => 'local',
                'root' => storage_path('app/private/media'),
                'visibility' => 'private',
                'serve' => false,
                'throw' => true,
                'report' => false,
            ]]);

            return 'starcho_private';
        }

        $bucket ??= $this->settings->privateBucket($driver);

        if (! in_array($driver, ['s3', 'do_spaces'], true) || blank($bucket)) {
            throw new \RuntimeException('Configura un proveedor y un bucket privado antes de guardar archivos restringidos.');
        }

        // Use a bucket-specific disk name so old media remains routable after an admin changes buckets.
        $diskName = 'starcho_private_'.substr(hash('sha256', $driver.'|'.$bucket), 0, 16);
        $config = $this->buildDiskConfig($driver);
        $config['bucket'] = $bucket;
        // Never inherit a CDN/base URL from the public disk configuration for private objects.
        unset($config['url']);
        $config['visibility'] = 'private';
        if ($driver === 's3') {
            // AWS accepts this non-public ACL on buckets with ACLs disabled (Bucket owner enforced).
            $config['options'] = array_merge($config['options'] ?? [], ['ACL' => 'bucket-owner-full-control']);
        }
        $config['throw'] = true;
        $config['report'] = false;
        config(['filesystems.disks.'.$diskName => $config]);

        return $diskName;
    }

    private function privateDisk(?string $driver = null, ?string $bucket = null): Filesystem
    {
        $diskName = $this->configurePrivateDisk($driver ?: 'local', $bucket);

        return Storage::disk($diskName);
    }

    /**
     * Atomically reserve bytes on the owner's counter before writing objects.
     * A conditional UPDATE prevents two uploads from both spending the same remainder.
     */
    private function reserveStorage(User|int $owner, int $bytes, ?string $errorMessage = null): void
    {
        if ($bytes <= 0) {
            return;
        }

        $userId = $owner instanceof User ? (int) $owner->getKey() : $owner;
        $user = User::query()->with('storagePlan')->findOrFail($userId);
        $planId = $user->storage_plan_id;
        $query = User::query()->whereKey($userId);

        if ($planId === null) {
            $query->whereNull('storage_plan_id');
        } else {
            $limit = (int) ($user->storagePlan?->storage_limit_bytes ?? 0);
            if ($bytes > $limit) {
                throw new \RuntimeException($errorMessage ?: 'Storage quota exceeded. Plan: '.($user->storagePlan?->limitLabel() ?? 'unavailable'));
            }

            $query->where('storage_plan_id', $planId)
                ->where('storage_used_bytes', '<=', $limit - $bytes);
        }

        if ($query->increment('storage_used_bytes', $bytes) !== 1) {
            throw new \RuntimeException($errorMessage ?: 'Storage quota exceeded. Plan: '.($user->storagePlan?->limitLabel() ?? 'unavailable'));
        }
    }

    /** Release a completed deletion or failed-upload reservation without going negative. */
    private function releaseStorage(User|int $owner, int $bytes): void
    {
        if ($bytes <= 0) {
            return;
        }

        $userId = $owner instanceof User ? (int) $owner->getKey() : $owner;
        $released = User::query()
            ->whereKey($userId)
            ->where('storage_used_bytes', '>=', $bytes)
            ->decrement('storage_used_bytes', $bytes);

        if ($released !== 1) {
            // An undercount is safer than subtracting unrelated bytes or making usage negative.
            Log::warning('Could not fully release a user storage reservation.', [
                'user_id' => $userId,
                'bytes' => $bytes,
            ]);
        }
    }

    /** Reconcile an upload reservation after conversion changes its actual byte size. */
    private function adjustStorageReservation(User $user, int $reservedBytes, int $actualBytes): void
    {
        if ($actualBytes > $reservedBytes) {
            $this->reserveStorage($user, $actualBytes - $reservedBytes);
        } elseif ($actualBytes < $reservedBytes) {
            $this->releaseStorage($user, $reservedBytes - $actualBytes);
        }
    }

    private function diskNameForDriver(string $driver): string
    {
        return match ($driver) {
            's3' => 'starcho_s3',
            'do_spaces' => 'starcho_do',
            'r2' => 'starcho_r2',
            default => 'public',
        };
    }
}

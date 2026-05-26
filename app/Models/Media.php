<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'user_id', 'driver', 'disk', 'path', 'webp_path', 'url',
        'original_name', 'mime_type', 'size', 'width', 'height',
        'mediable_type', 'mediable_id', 'context',
        'alt', 'caption',
    ];

    protected $casts = [
        'size'   => 'integer',
        'width'  => 'integer',
        'height' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Accessors ─────────────────────────────────────────────────────

    /**
     * Public URL for the media file.
     *
     * Priority:
     *  1. Explicit URL column (cloud drivers store it here).
     *  2. Local driver with a custom local_url configured in StorageSetting:
     *     builds {local_url}/storage/{path}  so Herd / Valet custom domains work.
     *  3. Fallback: Storage::url() — uses APP_URL / default disk config.
     */
    public function getPublicUrlAttribute(): string
    {
        if (filled($this->url)) {
            return $this->url;
        }

        // For local disk, respect the custom base URL configured in admin/site → Storage
        if ($this->disk === 'public' || $this->driver === 'local') {
            $settings = \App\Models\StorageSetting::singleton();

            if ($settings->isLocal() && filled($settings->local_url)) {
                $base = rtrim($settings->local_url, '/');

                return $base . '/storage/' . ltrim($this->path, '/');
            }
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Public URL for the WebP version (fallback to original public_url).
     */
    public function getWebpUrlAttribute(): string
    {
        if (filled($this->webp_path)) {
            if ($this->disk === 'public' || $this->driver === 'local') {
                $settings = \App\Models\StorageSetting::singleton();

                if ($settings->isLocal() && filled($settings->local_url)) {
                    $base = rtrim($settings->local_url, '/');

                    return $base . '/storage/' . ltrim($this->webp_path, '/');
                }
            }

            return Storage::disk($this->disk)->url($this->webp_path);
        }

        return $this->public_url;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    public function isDocument(): bool
    {
        return ! $this->isImage() && ! $this->isVideo();
    }

    /**
     * Returns a canonical type string: 'image' | 'video' | 'document'
     */
    public function fileType(): string
    {
        if ($this->isImage()) return 'image';
        if ($this->isVideo()) return 'video';

        return 'document';
    }

    /** Human-readable file size. */
    public function sizeLabel(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}

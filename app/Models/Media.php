<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'user_id', 'driver', 'disk', 'path', 'webp_path', 'url',
        'original_name', 'display_name', 'mime_type', 'size', 'width', 'height',
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

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(MediaAlbum::class, 'media_album_media')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(MediaTag::class, 'taggable', 'media_taggables')->withTimestamps();
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(MediaComment::class, 'commentable')->latest();
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'ratable');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(MediaFavorite::class);
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
        // Local files must always respect the configured site/storage URL.
        // Some older rows stored APP_URL/localhost in url, so rebuild instead.
        if ($this->disk === 'public' || $this->driver === 'local') {
            $settings = \App\Models\StorageSetting::singleton();

            return $settings->localPublicUrl($this->path);
        }

        if ($this->isR2()) {
            return $this->r2Url($this->path);
        }

        if (filled($this->url)) {
            return $this->url;
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function getNameAttribute(): string
    {
        return $this->display_name ?: $this->original_name;
    }

    public function getAverageRatingAttribute(): ?float
    {
        $average = $this->ratings()->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }

    /**
     * Public URL for the WebP version (fallback to original public_url).
     */
    public function getWebpUrlAttribute(): string
    {
        if (filled($this->webp_path)) {
            if ($this->disk === 'public' || $this->driver === 'local') {
                return \App\Models\StorageSetting::singleton()->localPublicUrl($this->webp_path);
            }

            if ($this->isR2()) {
                return $this->r2Url($this->webp_path);
            }

            return Storage::disk($this->disk)->url($this->webp_path);
        }

        return $this->public_url;
    }

    private function isR2(): bool
    {
        return $this->driver === 'r2' || $this->disk === 'starcho_r2';
    }

    private function r2Url(string $path): string
    {
        $settings = \App\Models\StorageSetting::singleton();

        if (filled($settings->r2_public_url)) {
            return rtrim((string) $settings->r2_public_url, '/') . '/' . ltrim($path, '/');
        }

        return app(\App\Services\StorageService::class)
            ->diskFor($this)
            ->temporaryUrl($path, now()->addMinutes(30));
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

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

    /** Public URL for the media file. Prefers explicit URL, then Storage::url(). */
    public function getPublicUrlAttribute(): string
    {
        if (filled($this->url)) {
            return $this->url;
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    /** Public URL for the WebP version (fallback to original). */
    public function getWebpUrlAttribute(): string
    {
        if (filled($this->webp_path)) {
            if (filled($this->url)) {
                // Build WebP URL by swapping the filename
                return Storage::disk($this->disk)->url($this->webp_path);
            }

            return Storage::disk($this->disk)->url($this->webp_path);
        }

        return $this->public_url;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
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

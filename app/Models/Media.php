<?php

namespace App\Models;

use App\Services\StorageService;
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
    public const VISIBILITIES = ['public', 'authenticated', 'protected', 'private'];

    private const VISIBILITY_LEVELS = [
        'public' => 0,
        'authenticated' => 1,
        'protected' => 2,
        'private' => 3,
    ];

    public static function stricterVisibility(string $first, string $second): string
    {
        $first = array_key_exists($first, self::VISIBILITY_LEVELS) ? $first : 'private';
        $second = array_key_exists($second, self::VISIBILITY_LEVELS) ? $second : 'private';

        return self::VISIBILITY_LEVELS[$first] >= self::VISIBILITY_LEVELS[$second] ? $first : $second;
    }

    /** Persist a temporary minimum policy before a restricted association. */
    public function enforceMinimumVisibility(string $visibility): void
    {
        $current = in_array($this->visibility, self::VISIBILITIES, true) ? $this->visibility : 'public';
        $stricter = self::stricterVisibility($current, $visibility);

        if ($stricter !== $current) {
            $this->forceFill(['visibility' => $stricter])->save();
        }
    }

    public const VARIANT_SIZES = [240, 480, 960, 1440];

    /**
     * Allow-list of upload extensions. Deliberately excludes script-capable
     * formats (svg, html, htm, xml, js, php, ...) that could lead to stored XSS
     * when served from the same origin.
     */
    public const ALLOWED_UPLOAD_EXTENSIONS = 'jpg,jpeg,png,gif,webp,bmp,'
        .'mp4,webm,ogg,mov,m4v,'
        .'mp3,wav,m4a,'
        .'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip';

    /** Validation rules for a single uploaded file (20 MB cap + safe types). */
    public static function uploadFileRules(): array
    {
        return ['file', 'max:20480', 'mimes:'.self::ALLOWED_UPLOAD_EXTENSIONS];
    }

    protected $fillable = [
        'user_id', 'driver', 'disk', 'path', 'webp_path', 'url',
        'variants', 'variants_size',
        'original_name', 'display_name', 'mime_type', 'size', 'width', 'height',
        'mediable_type', 'mediable_id', 'context', 'visibility',
        'alt', 'caption',
    ];

    protected $casts = [
        'size' => 'integer',
        'variants' => 'array',
        'variants_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Media $media): void {
            if ($media->visibility && $media->visibility !== 'public') {
                $media->url = null;
            }
        });
    }

    // ── Relationships ──────────────────────────────────────────────────

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsToMany<MediaAlbum, $this> */
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

    /** @return MorphMany<MediaRating, $this> */
    public function ratings(): MorphMany
    {
        return $this->morphMany(MediaRating::class, 'ratable');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(MediaFavorite::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────

    public function getUrlAttribute(?string $value): ?string
    {
        return $this->requiresAuthorizedDelivery() || $this->disk === 'starcho_private'
            ? null
            : $value;
    }

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
        // Non-public assets must never expose a storage/CDN URL. The proxy route
        // applies the same access rules to originals and variants.
        if ($this->requiresAuthorizedDelivery() || $this->disk === 'starcho_private') {
            return route('media.files.show', ['media' => $this]);
        }

        // Local files must always respect the configured site/storage URL.
        // Some older rows stored APP_URL/localhost in url, so rebuild instead.
        if ($this->disk === 'public' || $this->driver === 'local') {
            $settings = StorageSetting::singleton();

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
        if ($this->requiresAuthorizedDelivery() || $this->disk === 'starcho_private') {
            return route('media.files.show', ['media' => $this]);
        }

        if (filled($this->webp_path)) {
            if ($this->disk === 'public' || $this->driver === 'local') {
                return StorageSetting::singleton()->localPublicUrl($this->webp_path);
            }

            if ($this->isR2()) {
                return $this->r2Url($this->webp_path);
            }

            return Storage::disk($this->disk)->url($this->webp_path);
        }

        return $this->public_url;
    }

    public function getPreviewUrlAttribute(): string
    {
        $settings = StorageSetting::singleton();

        if (! $settings->imageVariantsEnabled()) {
            return $this->public_url;
        }

        return $this->variantUrl($settings->imagePreviewVariantSize());
    }

    public function variantUrl(int|string|null $size = 240): string
    {
        $variant = $this->variant($size);

        if (! $variant) {
            return $this->public_url;
        }

        return route('media.files.show', ['media' => $this, 'variant' => $variant['key']]);
    }

    /**
     * Resolve the strictest policy imposed by the file and every album it is
     * attached to. Membership in a public album never weakens a stricter rule.
     */
    public function effectiveVisibility(): string
    {
        $visibility = in_array($this->visibility, self::VISIBILITIES, true)
            ? $this->visibility
            : 'private';
        $level = self::VISIBILITY_LEVELS[$visibility];

        $albums = $this->relationLoaded('albums') ? $this->albums : $this->albums()->get();

        foreach ($albums as $album) {
            $albumVisibility = $album->effectiveVisibility();
            $level = max($level, self::VISIBILITY_LEVELS[$albumVisibility]);
        }

        return array_search($level, self::VISIBILITY_LEVELS, true) ?: 'public';
    }

    public function requiresAuthorizedDelivery(): bool
    {
        return $this->effectiveVisibility() !== 'public';
    }

    /** Album passwords that must all be unlocked to view this file. */
    public function protectedAlbumIds(): array
    {
        $albums = $this->relationLoaded('albums') ? $this->albums : $this->albums()->get();

        return $albums
            ->filter(fn (MediaAlbum $album) => $album->effectiveVisibility() === 'protected')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function isAccessibleBy(?User $user): bool
    {
        if ($user && ($user->hasRole('root') || $user->hasRole('admin') || $user->can('view-admin'))) {
            return true;
        }

        if ($user && (int) $this->user_id === (int) $user->id) {
            return true;
        }

        $visibility = $this->effectiveVisibility();

        if ($visibility === 'public') {
            return true;
        }

        if ($visibility === 'authenticated') {
            return $user !== null;
        }

        $albums = $this->relationLoaded('albums') ? $this->albums : $this->albums()->get();

        if ($visibility === 'private') {
            return $user !== null && $albums->contains(
                fn (MediaAlbum $album) => $album->effectiveVisibility() === 'private'
                    && (int) $album->user_id === (int) $user->id
            );
        }

        $protectedAlbums = $albums->filter(fn (MediaAlbum $album) => $album->effectiveVisibility() === 'protected');

        if ($protectedAlbums->isEmpty()) {
            return false;
        }

        foreach ($protectedAlbums as $album) {
            $albumOwner = $user && (int) $album->user_id === (int) $user->id;

            if (! $albumOwner && (! $album->password || ! session('media_album_unlocked_'.$album->id))) {
                return false;
            }
        }

        return true;
    }

    public function variant(int|string|null $size = 240): ?array
    {
        $variants = collect($this->variants ?? [])
            ->filter(fn ($variant) => filled($variant['path'] ?? null));

        if ($variants->isEmpty()) {
            return null;
        }

        $key = (string) ($size ?: 240);

        if ($variants->has($key)) {
            return ['key' => $key] + $variants->get($key);
        }

        $available = $variants
            ->keys()
            ->map(fn ($value) => (int) $value)
            ->sort()
            ->values();

        $target = (int) $key;
        $best = $available->first(fn (int $value) => $value >= $target) ?? $available->last();

        return ['key' => (string) $best] + $variants->get((string) $best);
    }

    public function variantPath(int|string|null $size = 240): ?string
    {
        return $this->variant($size)['path'] ?? null;
    }

    public function variantUrls(): array
    {
        $urls = ['original' => $this->public_url];

        foreach (StorageSetting::singleton()->imageVariantSizes() as $size) {
            $urls[(string) $size] = $this->variantUrl($size);
        }

        return $urls;
    }

    public function totalSize(): int
    {
        return (int) $this->size + (int) $this->variants_size;
    }

    private function isR2(): bool
    {
        return $this->driver === 'r2' || $this->disk === 'starcho_r2';
    }

    private function r2Url(string $path): string
    {
        $settings = StorageSetting::singleton();

        if (filled($settings->r2_public_url)) {
            return rtrim((string) $settings->r2_public_url, '/').'/'.ltrim($path, '/');
        }

        return app(StorageService::class)
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
        if ($this->isImage()) {
            return 'image';
        }
        if ($this->isVideo()) {
            return 'video';
        }

        return 'document';
    }

    /** Human-readable file size. */
    public function sizeLabel(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function variantsSizeLabel(): string
    {
        $bytes = (int) $this->variants_size;

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}

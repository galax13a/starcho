<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use SoftDeletes, HasTranslations;

    const TYPE_POST = 'post';
    const TYPE_PAGE = 'page';

    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PRIVATE   = 'private';
    const STATUS_PASSWORD  = 'password_protected';

    const STATUS_LABELS = [
        'draft'              => 'Borrador',
        'published'          => 'Publicado',
        'scheduled'          => 'Programado',
        'private'            => 'Privado',
        'password_protected' => 'Con contraseña',
    ];

    const STATUS_COLORS = [
        'draft'              => 'zinc',
        'published'          => 'emerald',
        'scheduled'          => 'blue',
        'private'            => 'orange',
        'password_protected' => 'violet',
    ];

    const NAV_NONE   = 'none';
    const NAV_HEADER = 'header';
    const NAV_FOOTER = 'footer';
    const NAV_BOTH   = 'both';

    const NAV_LABELS = [
        'none'   => 'No mostrar',
        'header' => 'Header',
        'footer' => 'Footer',
        'both'   => 'Header y Footer',
    ];

    public $translatable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image_alt',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'og_title',
        'og_description',
    ];

    protected $fillable = [
        'type', 'title', 'slug', 'excerpt', 'content',
        'featured_image', 'featured_image_alt',
        'status', 'password', 'published_at',
        'author_id', 'parent_id', 'menu_order', 'nav_position', 'allow_comments',
        'seo_title', 'seo_description', 'seo_keywords',
        'og_title', 'og_description', 'og_image',
        'canonical_url', 'no_index', 'no_follow',
        'user_id',
    ];

    protected $casts = [
        'published_at'   => 'datetime',
        'allow_comments' => 'boolean',
        'no_index'       => 'boolean',
        'no_follow'      => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PostCategory::class, 'post_category_post');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PostTag::class, 'post_tag_post');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function gallery(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('context', 'gallery')
            ->orderBy('created_at');
    }

    public function getPublicUrlAttribute(): string
    {
        return $this->publicUrl(app()->getLocale());
    }

    public function publicUrl(string $locale): string
    {
        $slug = $this->getTranslation('slug', $locale, false)
            ?: collect($this->getTranslations('slug'))->first()
            ?: $this->getRawOriginal('slug');

        return $this->type === self::TYPE_PAGE
            ? url('/' . $locale . '/' . $slug)
            : url('/' . $locale . '/blog/' . $slug);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && ($this->published_at === null || $this->published_at->isPast());
    }

    public static function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $slug     = Str::slug($title);
        $original = $slug;
        $count    = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    public static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return static::whereRaw("JSON_SEARCH(slug, 'one', ?) IS NOT NULL", [$slug])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}

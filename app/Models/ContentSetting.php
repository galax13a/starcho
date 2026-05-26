<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ContentSetting extends Model
{
    private const CACHE_KEY = 'content_settings.singleton_id';

    protected $fillable = [
        'posts_per_page',
        'related_posts_count',
        'show_author',
        'show_date',
        'show_categories',
        'show_tags',
        'show_excerpt_in_list',
        'show_featured_image_in_list',
        'comments_enabled',
        'comments_require_approval',
        'blog_sidebar_enabled',
        'breadcrumbs_enabled',
        'track_broken_links',
        'broken_links_notify_email',
        'reading_time_enabled',
        'reading_words_per_minute',
        'featured_post_id',
        'blog_layout',
        'sitemap_include_pages',
        'sitemap_include_posts',
        'sitemap_excluded_urls',
    ];

    protected $casts = [
        'show_author'                 => 'boolean',
        'show_date'                   => 'boolean',
        'show_categories'             => 'boolean',
        'show_tags'                   => 'boolean',
        'show_excerpt_in_list'        => 'boolean',
        'show_featured_image_in_list' => 'boolean',
        'comments_enabled'            => 'boolean',
        'comments_require_approval'   => 'boolean',
        'blog_sidebar_enabled'        => 'boolean',
        'breadcrumbs_enabled'         => 'boolean',
        'track_broken_links'          => 'boolean',
        'reading_time_enabled'        => 'boolean',
        'sitemap_include_pages'       => 'boolean',
        'sitemap_include_posts'       => 'boolean',
        'sitemap_excluded_urls'       => 'array',
    ];

    public static function defaults(): array
    {
        return [
            'posts_per_page'              => 12,
            'related_posts_count'         => 3,
            'show_author'                 => true,
            'show_date'                   => true,
            'show_categories'             => true,
            'show_tags'                   => true,
            'show_excerpt_in_list'        => true,
            'show_featured_image_in_list' => true,
            'comments_enabled'            => true,
            'comments_require_approval'   => false,
            'blog_sidebar_enabled'        => true,
            'breadcrumbs_enabled'         => true,
            'track_broken_links'          => true,
            'broken_links_notify_email'   => null,
            'reading_time_enabled'        => true,
            'reading_words_per_minute'    => 200,
            'featured_post_id'            => null,
            'blog_layout'                 => 'grid',
            'sitemap_include_pages'       => true,
            'sitemap_include_posts'       => true,
            'sitemap_excluded_urls'       => null,
        ];
    }

    public static function singleton(): self
    {
        return static::firstOrCreate([], static::defaults());
    }

    public static function cached(): ?self
    {
        if (!Schema::hasTable('content_settings')) {
            return null;
        }

        $id = Cache::remember(self::CACHE_KEY, 3600, fn () => static::query()->value('id') ?? static::singleton()->id);

        return static::find($id) ?? static::singleton();
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}

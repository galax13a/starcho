<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    private const CACHE_KEY_ID = 'site_settings.singleton_id';
    private const CACHE_KEY_LEGACY = 'site_settings.singleton';

    protected $fillable = [
        'site_name',
        'site_tagline',
        'site_description',
        'meta_keywords',
        'meta_author',
        'canonical_url',
        'og_type',
        'og_title',
        'og_description',
        'twitter_card',
        'twitter_site',
        'twitter_creator',
        'facebook_app_id',
        'theme_color',
        'robots_index',
        'robots_follow',
        'home_page_enabled',
        'public_registration_enabled',
        'favicon_path',
        'og_image_path',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'home_page_enabled' => 'boolean',
        'public_registration_enabled' => 'boolean',
    ];

    public static function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'Starcho'),
            'site_tagline' => null,
            'site_description' => null,
            'meta_keywords' => null,
            'meta_author' => null,
            'canonical_url' => null,
            'og_type' => 'website',
            'og_title' => null,
            'og_description' => null,
            'twitter_card' => 'summary_large_image',
            'twitter_site' => null,
            'twitter_creator' => null,
            'facebook_app_id' => null,
            'theme_color' => '#111827',
            'robots_index' => true,
            'robots_follow' => true,
            'home_page_enabled' => true,
            'public_registration_enabled' => true,
            'favicon_path' => null,
            'og_image_path' => null,
        ];
    }

    public static function isHomePageEnabled(): bool
    {
        $settings = static::cached();

        return $settings?->home_page_enabled ?? true;
    }

    public static function isPublicRegistrationEnabled(): bool
    {
        $settings = static::cached();

        return $settings?->public_registration_enabled ?? true;
    }

    public static function singleton(): self
    {
        return static::firstOrCreate([], static::defaults());
    }

    public static function cached(): ?self
    {
        if (!Schema::hasTable('site_settings')) {
            return null;
        }

        // Limpia una cache legacy que pudo guardar un objeto serializado inválido
        // y causar __PHP_Incomplete_Class al deserializar.
        $legacy = Cache::get(self::CACHE_KEY_LEGACY);
        if (is_object($legacy) && get_class($legacy) === '__PHP_Incomplete_Class') {
            Cache::forget(self::CACHE_KEY_LEGACY);
        }

        $id = Cache::remember(self::CACHE_KEY_ID, 3600, fn () => static::query()->value('id') ?? static::singleton()->id);

        return static::find($id) ?? static::singleton();
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget(self::CACHE_KEY_ID);
            Cache::forget(self::CACHE_KEY_LEGACY);
        });

        static::deleted(function (): void {
            Cache::forget(self::CACHE_KEY_ID);
            Cache::forget(self::CACHE_KEY_LEGACY);
        });
    }
}

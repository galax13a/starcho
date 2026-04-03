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
        'app_name',
        'slogan',
        'site_tagline',
        'site_description',
        'meta_keywords',
        'meta_author',
        'support_email',
        'business_email',
        'company_name',
        'company_dni',
        'company_country',
        'company_city',
        'address',
        'founding_year',
        'google_maps_url',
        'support_whatsapp',
        'business_whatsapp',
        'server_timezone',
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
        'dark_mode_enabled',
        'hide_language_switcher',
        'default_site_locale',
        'favicon_path',
        'og_image_path',
        'social_facebook',
        'social_x',
        'social_telegram',
        'social_discord',
        'social_tiktok',
        'social_linkedin',
        'social_instagram',
        'social_youtube',
        'social_pinterest',
        'social_onlyfans',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'home_page_enabled' => 'boolean',
        'public_registration_enabled' => 'boolean',
        'dark_mode_enabled' => 'boolean',
        'hide_language_switcher' => 'boolean',
    ];

    public static function defaults(): array
    {
        return [
            'site_name' => config('app.name', 'Starcho'),
            'app_name' => null,
            'slogan' => null,
            'site_tagline' => null,
            'site_description' => null,
            'meta_keywords' => null,
            'meta_author' => null,
            'support_email' => null,
            'business_email' => null,
            'company_name' => null,
            'company_dni' => null,
            'company_country' => null,
            'company_city' => null,
            'address' => null,
            'founding_year' => null,
            'google_maps_url' => null,
            'support_whatsapp' => null,
            'business_whatsapp' => null,
            'server_timezone' => 'UTC',
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
            'dark_mode_enabled' => false,
            'hide_language_switcher' => false,
            'default_site_locale' => 'es',
            'favicon_path' => null,
            'og_image_path' => null,
            'social_facebook' => null,
            'social_x' => null,
            'social_telegram' => null,
            'social_discord' => null,
            'social_tiktok' => null,
            'social_linkedin' => null,
            'social_instagram' => null,
            'social_youtube' => null,
            'social_pinterest' => null,
            'social_onlyfans' => null,
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

    /**
     * Nombre de la aplicación desde la BD, con fallback a "Starcho".
     */
        public static function appName(): string
        {
            $settings = static::cached();
            $name = $settings?->app_name;

            return filled($name) ? $name : 'Starcho';
        }

        /**
         * ¿Está habilitado el modo oscuro en el home público?
         */
        public static function isDarkModeEnabled(): bool
        {
            $settings = static::cached();

            return $settings?->dark_mode_enabled ?? false;
        }

        public static function isLanguageSwitcherHidden(): bool
        {
            $settings = static::cached();

            return $settings?->hide_language_switcher ?? false;
        }

        public static function defaultSiteLocale(): string
        {
            $settings = static::cached();
            $locale = $settings?->default_site_locale;

            return filled($locale) ? (string) $locale : 'es';
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

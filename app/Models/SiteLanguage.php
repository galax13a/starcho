<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteLanguage extends Model
{
    private const CACHE_KEY_ACTIVE_CODES = 'site_languages.active_codes';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static function allOrdered(): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('site_languages')) {
            return collect();
        }

        return static::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    public static function activeCodes(): array
    {
        if (! Schema::hasTable('site_languages')) {
            return ['es', 'en', 'pt_BR'];
        }

        $cached = Cache::get(self::CACHE_KEY_ACTIVE_CODES);

        if (is_object($cached) && get_class($cached) === '__PHP_Incomplete_Class') {
            Cache::forget(self::CACHE_KEY_ACTIVE_CODES);
            $cached = null;
        }

        $codes = Cache::remember(self::CACHE_KEY_ACTIVE_CODES, 3600, function (): array {
            return static::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->pluck('code')
                ->filter(fn ($value) => is_string($value) && $value !== '')
                ->values()
                ->all();
        });

        return $codes === [] ? ['es', 'en'] : $codes;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE_CODES);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}

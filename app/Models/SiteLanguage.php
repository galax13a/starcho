<?php

namespace App\Models;

use App\Models\Concerns\MemoizesPerRequest;
use App\Support\SafeCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteLanguage extends Model
{
    use MemoizesPerRequest;

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

    /**
     * Lo invocan SetLocale y SetLocaleFromUrl en cada request, mas una decena de
     * controladores y componentes Livewire. Sin memoizacion, cada llamada repetia
     * el `Schema::hasTable()` (information_schema en MySQL) y la lectura de cache.
     *
     * @return list<string>
     */
    public static function activeCodes(): array
    {
        return static::memo('active_codes', function (): array {
            if (! Schema::hasTable('site_languages')) {
                return ['es', 'en', 'pt_BR'];
            }

            // SafeCache descarta y recalcula entradas que no sean arrays/escalares
            // (p. ej. Collections guardadas por versiones anteriores, que en Laravel 13
            // vuelven como __PHP_Incomplete_Class por serializable_classes => false).
            $codes = SafeCache::rememberPlain(self::CACHE_KEY_ACTIVE_CODES, 3600, function (): array {
                return static::query()
                    ->where('active', true)
                    ->orderBy('sort_order')
                    ->pluck('code')
                    ->filter(fn ($value) => is_string($value) && $value !== '')
                    ->values()
                    ->all();
            });

            return (! is_array($codes) || $codes === []) ? ['es', 'en'] : $codes;
        });
    }

    public static function active(): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('site_languages')) {
            return collect([
                (object)['code' => 'es', 'name' => 'Español'],
                (object)['code' => 'en', 'name' => 'English'],
            ]);
        }

        return static::query()->where('active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ACTIVE_CODES);
        static::flushMemo();
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}

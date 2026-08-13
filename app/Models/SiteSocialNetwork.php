<?php

namespace App\Models;

use App\Support\SafeCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSocialNetwork extends Model
{
    private const CACHE_KEY = 'site_social_networks.active_with_url';

    protected $fillable = [
        'key',
        'label',
        'icon',
        'color',
        'url',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Redes activas que tienen URL configurada (para el frontend público).
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function activeWithUrl(): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('site_social_networks')) {
            return collect();
        }

        // Solo se cachean ids (escalares). SafeCache descarta cualquier entrada
        // antigua que guardara la Collection de modelos y la recalcula.
        $ids = SafeCache::rememberPlain(self::CACHE_KEY, 3600, function (): array {
            return static::query()
                ->where('active', true)
                ->whereNotNull('url')
                ->where('url', '!=', '')
                ->orderBy('sort_order')
                ->pluck('id')
                ->all();
        });

        if (! is_array($ids) || $ids === []) {
            return collect();
        }

        return static::query()
            ->whereIn('id', $ids)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Todas las redes (para el panel admin).
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function allOrdered(): \Illuminate\Support\Collection
    {
        if (! Schema::hasTable('site_social_networks')) {
            return collect();
        }

        return static::orderBy('sort_order')->get();
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}

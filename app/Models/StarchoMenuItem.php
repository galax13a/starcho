<?php

namespace App\Models;

use App\Support\SafeCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class StarchoMenuItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'panel', 'module_key', 'parent_id', 'section', 'name',
        'icon', 'route', 'url', 'target', 'sort_order', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public $translatable = ['name'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StarchoMenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(StarchoMenuItem::class, 'parent_id')
            ->where('active', true)
            ->orderBy('sort_order');
    }

    public function allChildren(): HasMany
    {
        return $this->hasMany(StarchoMenuItem::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    private const CACHE_MENU_IDS = 'starcho_menu_items_ids';

    public static function getCachedMenu(string $panel = 'app'): Collection
    {
        $cacheKey = self::CACHE_MENU_IDS.'_'.$panel;

        // Solo se cachean los ids raiz; los items y sus relaciones se consultan en
        // vivo para que la vista reciba siempre una Eloquent Collection real.
        $rootIds = SafeCache::rememberPlain($cacheKey, 3600, function () use ($panel) {
            return static::whereNull('parent_id')
                ->where('active', true)
                ->where('panel', $panel)
                ->orderBy('sort_order')
                ->pluck('id')
                ->all();
        });

        if (! is_array($rootIds) || empty($rootIds)) {
            return static::with(['children.children'])
                ->whereNull('parent_id')
                ->where('active', true)
                ->where('panel', $panel)
                ->orderBy('sort_order')
                ->get(); // sections grouped by groupBy() on collection
        }

        return static::with(['children.children'])
            ->whereIn('id', $rootIds)
            ->orderBy('sort_order')
            ->get();
    }

    public static function clearMenuCache(): void
    {
        Cache::forget(self::CACHE_MENU_IDS.'_app');
        Cache::forget(self::CACHE_MENU_IDS.'_admin');
        Cache::forget(self::CACHE_MENU_IDS.'_home');
        Cache::forget(self::CACHE_MENU_IDS); // legacy
    }

    /**
     * Keep menu links to safe web destinations when they come from imports or
     * module configuration. Named routes are resolved separately.
     */
    public static function sanitizeUrl(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim($value);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        if (str_starts_with($url, '#')) {
            return $url;
        }

        $parts = parse_url($url);
        $scheme = is_array($parts) ? strtolower((string) ($parts['scheme'] ?? '')) : '';

        return in_array($scheme, ['http', 'https'], true) && filled($parts['host'] ?? null)
            ? $url
            : null;
    }

    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->route) {
            try {
                return route($this->route);
            } catch (\Throwable) {
                return self::sanitizeUrl($this->url);
            }
        }

        return self::sanitizeUrl($this->url);
    }

    public function isCurrentRoute(): bool
    {
        if ($this->route) {
            try {
                return request()->routeIs($this->route) || request()->routeIs($this->route.'.*');
            } catch (\Throwable) {
                return false;
            }
        }

        return false;
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        $defaultLocale = config('app.fallback_locale', 'en');

        $name = $this->getTranslation('name', $locale, false)
            ?: $this->getTranslation('name', $defaultLocale, false)
            ?: ($this->label ?? null)
            ?: ($this->route ?? '');

        return is_string($name) ? $name : '';
    }
}

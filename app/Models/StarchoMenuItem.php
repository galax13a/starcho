<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class StarchoMenuItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'module_key', 'parent_id', 'section', 'name',
        'icon', 'route', 'url', 'target', 'sort_order', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public $translatable = ['name'];

    public function parent()
    {
        return $this->belongsTo(StarchoMenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(StarchoMenuItem::class, 'parent_id')
            ->where('active', true)
            ->orderBy('sort_order');
    }

    public function allChildren()
    {
        return $this->hasMany(StarchoMenuItem::class, 'parent_id')
            ->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    private const CACHE_MENU_IDS = 'starcho_menu_items_ids';

    public static function getCachedMenu(): \Illuminate\Database\Eloquent\Collection
    {
        $rootIds = Cache::remember(self::CACHE_MENU_IDS, 3600, function () {
            return static::whereNull('parent_id')
                ->where('active', true)
                ->orderBy('sort_order')
                ->pluck('id')
                ->toArray();
        });

        if (!is_array($rootIds) || empty($rootIds)) {
            return static::with(['children.children'])
                ->whereNull('parent_id')
                ->where('active', true)
                ->orderBy('sort_order')
                ->get();
        }

        return static::with(['children.children'])
            ->whereIn('id', $rootIds)
            ->orderBy('sort_order')
            ->get();
    }

    public static function clearMenuCache(): void
    {
        Cache::forget(self::CACHE_MENU_IDS);
    }

    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->route) {
            try {
                return route($this->route);
            } catch (\Throwable) {
                return $this->url;
            }
        }
        return $this->url;
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

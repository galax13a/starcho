<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SitePageSetting extends Model
{
    protected $fillable = [
        'locale',
        'path',
        'title',
        'description',
        'meta_keywords',
        'og_title',
        'og_description',
        'robots_index',
        'robots_follow',
        'active',
    ];

    protected $casts = [
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'active' => 'boolean',
    ];

    public function setPathAttribute(string $value): void
    {
        $this->attributes['path'] = self::normalizePath($value);
    }

    public static function forPathAndLocale(string $path, string $locale): ?self
    {
        if (!Schema::hasTable('site_page_settings')) {
            return null;
        }

        $normalizedPath = self::normalizePath($path);
        $fallbackLocale = config('app.fallback_locale', 'en');

        return static::query()
            ->where('active', true)
            ->where('path', $normalizedPath)
            ->whereIn('locale', [$locale, $fallbackLocale])
            ->orderByRaw('CASE WHEN locale = ? THEN 0 ELSE 1 END', [$locale])
            ->first();
    }

    public static function normalizePath(string $path): string
    {
        $path = '/' . ltrim(trim($path), '/');
        $path = preg_replace('#/+#', '/', $path) ?: '/';

        return $path === '' ? '/' : $path;
    }
}

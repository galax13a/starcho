<?php

namespace App\Models;

use App\Support\SafeCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Translatable\HasTranslations;

class StarchoModule extends Model
{
    use HasTranslations;

    protected $fillable = ['key', 'name', 'description', 'icon', 'installed', 'active', 'config'];

    protected $casts = [
        'installed' => 'boolean',
        'active' => 'boolean',
        'config' => 'array',
    ];

    public $translatable = ['name', 'description'];

    public function menuItems()
    {
        return $this->hasMany(StarchoMenuItem::class, 'module_key', 'key');
    }

    /**
     * Crear automáticamente items de menú basados en la configuración del módulo
     */
    public function createMenuItems(): void
    {
        $menuConfig = data_get($this->config, 'menu_items', []);

        if (! is_array($menuConfig) || $menuConfig === []) {
            return;
        }

        foreach ($menuConfig as $item) {
            if (! is_array($item)) {
                continue;
            }

            $route = is_string($item['route'] ?? null) ? trim($item['route']) : '';

            if ($route === '' || $route === 'app') {
                continue;
            }

            $panel = in_array($item['panel'] ?? 'app', ['app', 'admin', 'home'], true)
                ? $item['panel']
                : 'app';
            $parentId = is_numeric($item['parent_id'] ?? null) ? (int) $item['parent_id'] : null;

            if (! $parentId && ! empty($item['parent_route'])) {
                $parentId = StarchoMenuItem::where('panel', $panel)
                    ->where('route', (string) $item['parent_route'])
                    ->value('id');
            }

            // Never attach a menu item to another panel. This also prevents a
            // malformed imported config from creating a cross-tree reference.
            if ($parentId !== null && ! StarchoMenuItem::where('panel', $panel)->whereKey($parentId)->exists()) {
                $parentId = null;
            }

            $existingItem = StarchoMenuItem::where('module_key', $this->key)
                ->where('route', $route)
                ->first();

            $url = StarchoMenuItem::sanitizeUrl($item['url'] ?? null);

            $target = in_array($item['target'] ?? '_self', ['_self', '_blank'], true)
                ? $item['target']
                : '_self';

            if (! $existingItem) {
                $nameData = $item['name'] ?? $item['label'] ?? null;

                $menuItem = new StarchoMenuItem([
                    'panel' => $panel,
                    'module_key' => $this->key,
                    'parent_id' => $parentId,
                    'section' => $item['section'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'route' => $route,
                    'url' => $url,
                    'sort_order' => is_numeric($item['sort_order'] ?? null) ? (int) $item['sort_order'] : 0,
                    'active' => true,
                    'target' => $target,
                ]);

                if (is_array($nameData)) {
                    foreach ($nameData as $locale => $translation) {
                        $menuItem->setTranslation('name', $locale, $translation);
                    }
                } elseif (is_string($nameData) && $nameData !== '') {
                    $menuItem->setTranslation('name', app()->getLocale(), $nameData);
                }

                $menuItem->save();
            } else {
                $existingItem->update([
                    'active' => true,
                    'parent_id' => $parentId,
                    'url' => $url,
                    'target' => $target,
                ]);
            }
        }

        StarchoMenuItem::clearMenuCache();
    }

    /**
     * Eliminar todos los items de menú del módulo
     */
    private function deleteMenuItems(): void
    {
        StarchoMenuItem::where('module_key', $this->key)->delete();
    }

    private function clearModuleCache(): void
    {
        Cache::forget("starcho_module_{$this->key}");
        Cache::forget('starcho_menu_items');
    }

    public function install(): void
    {
        DB::transaction(function (): void {
            $this->update(['installed' => true, 'active' => true]);
            $this->createMenuItems();
        });
        $this->clearModuleCache();
    }

    public function uninstall(): void
    {
        DB::transaction(function (): void {
            $this->deleteMenuItems();
            $this->update(['installed' => false, 'active' => false]);
        });
        $this->clearModuleCache();
    }

    public function activate(): void
    {
        if ($this->installed) {
            $this->update(['active' => true]);
            $this->menuItems()->update(['active' => true]);
            $this->clearModuleCache();
        }
    }

    public function deactivate(): void
    {
        $this->update(['active' => false]);
        $this->menuItems()->update(['active' => false]);
        $this->clearModuleCache();
    }

    public static function isActive(string $key): bool
    {
        return (bool) SafeCache::rememberPlain("starcho_module_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)
                ->where('installed', true)
                ->where('active', true)
                ->exists();
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

class StarchoModule extends Model
{
    use HasTranslations;

    protected $fillable = ['key', 'name', 'description', 'icon', 'installed', 'active', 'config'];

    protected $casts = [
        'installed' => 'boolean',
        'active'    => 'boolean',
        'config'    => 'array',
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
        $menuConfig = $this->config['menu_items'] ?? [];
        
        if (empty($menuConfig)) {
            return;
        }

        foreach ($menuConfig as $item) {
            // Evitar rutas inválidas/placeholder (ej. route: 'app') que rompen el menú
            if (empty($item['route']) || $item['route'] === 'app') {
                continue;
            }

            // Verificar si ya existe un item con la misma ruta para este módulo
            $existingItem = StarchoMenuItem::where('module_key', $this->key)
                ->where('route', $item['route'] ?? null)
                ->first();

            if (!$existingItem) {
                $nameData = $item['name'] ?? $item['label'] ?? null;

                $menuItem = new StarchoMenuItem([
                    'module_key' => $this->key,
                    'icon' => $item['icon'] ?? null,
                    'route' => $item['route'] ?? null,
                    'url' => $item['url'] ?? null,
                    'sort_order' => $item['sort_order'] ?? 0,
                    'active' => true,
                    'target' => $item['target'] ?? '_self',
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
                // Si existe, asegurarse de que esté activo
                $existingItem->update(['active' => true]);
            }
        }
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
        $this->update(['installed' => true, 'active' => true]);
        $this->createMenuItems();
        $this->clearModuleCache();
    }

    public function uninstall(): void
    {
        $this->deleteMenuItems();
        $this->update(['installed' => false, 'active' => false]);
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
        return Cache::remember("starcho_module_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->where('active', true)->exists();
        });
    }
}

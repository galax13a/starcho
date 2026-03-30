# Starcho: Módulos y Sistema de Menú

## Visión General

El sistema de módulos de Starcho permite:
1. **Instalar/desinstalar módulos** dinámicamente
2. **Crear/eliminar items de menú automáticamente** cuando instales/desinstales módulos
3. **Activar/desactivar módulos** sin eliminar sus datos
4. **Mostrar el menú en ambas áreas** (/app y /admin) de forma sincronizada

---

## Arquitectura

### Tablas de Base de Datos

#### `starcho_modules`
```
- id
- key (unique) - identificador único del módulo: 'tasks', 'contacts', etc.
- name - nombre mostrable
- description
- icon - nombre del icono
- installed (boolean) - si está instalado
- active (boolean) - si está activo
- config (json) - configuración del módulo INCLUYENDO items de menú
```

#### `starcho_menu_items`
```
- id
- module_key - FK a starcho_modules.key (NULL = core item sin módulo)
- parent_id - FK a starcho_menu_items.id (NULL = item padre)
- section - agrupación visual (ej: "App", "Sistema")
- label - texto mostrado en menú
- icon - nombre del icono
- route - named route (ej: 'app.tasks.index')
- url - URL alternativa si no hay route
- target - '_self' o '_blank'
- sort_order - número para ordenar
- active (boolean) - si se muestra en el menú
```

---

## Flujo de Instalación de Módulo

### 1. Definir el Módulo

En tu seeder o donde crees módulos, define la estructura:

```php
StarchoModule::updateOrCreate(
    ['key' => 'contacts'],
    [
        'name'        => 'Contactos',
        'description' => 'Gestión de contactos',
        'icon'        => 'user-group',
        'installed'   => false,  // FALSE inicialmente
        'active'      => false,
        'config'      => [
            'menu_items' => [
                [
                    'label'      => 'Contactos',
                    'icon'       => 'user-group',
                    'route'      => 'app.contacts.index',
                    'sort_order' => 30,
                    'target'     => '_self',
                ],
                // Puedes agregar más items para subcategorías
                [
                    'label'      => 'Crear Contacto',
                    'icon'       => 'plus',
                    'route'      => 'app.contacts.create',
                    'parent_id'  => null, // se resolvería después
                    'sort_order' => 31,
                ],
            ],
        ],
    ]
);
```

### 2. Usuario Instala el Módulo

Usuario va a `/admin/modules` y hace click en "Instalar":

```
POST /admin/modules/{module}/install → ModuleController@install()
```

Esto ejecuta: `$module->install()`

### 3. Modelo Crea Items de Menú

El método `install()` en `StarchoModule`:

```php
public function install(): void
{
    $this->update(['installed' => true, 'active' => true]);
    $this->createMenuItems();  // ← Crea los items automáticamente
    Cache::forget('starcho_menu_items');
}
```

El método `createMenuItems()`:

```php
public function createMenuItems(): void
{
    $menuConfig = $this->config['menu_items'] ?? [];
    
    foreach ($menuConfig as $item) {
        StarchoMenuItem::firstOrCreate(
            [
                'module_key' => $this->key,
                'label'      => $item['label'] ?? null,
                'route'      => $item['route'] ?? null,
            ],
            [
                'icon'       => $item['icon'] ?? null,
                'url'        => $item['url'] ?? null,
                'sort_order' => $item['sort_order'] ?? 0,
                'active'     => true,
                'target'     => $item['target'] ?? '_self',
            ]
        );
    }

    Cache::forget('starcho_menu_items');
}
```

### 4. Menú Se Actualiza Automáticamente

- El menú se renderiza en ambas áreas desde `StarchoMenuItem::getCachedMenu()`
- Los nuevos items aparecen en `/app` y `/admin` inmediatamente
- El cache se limpia, forzando recarga

---

## Operaciones sobre Módulos

| Operación | Método | Qué Ocurre |
|-----------|--------|-----------|
| **Instalar** | `$module->install()` | `installed=true`, `active=true`, crea items de menú, limpia cache |
| **Desinstalar** | `$module->uninstall()` | elimina items de menú, `installed=false`, `active=false`, limpia cache |
| **Activar** | `$module->activate()` | activa items (`active=true`), solo si `installed=true`, limpia cache |
| **Desactivar** | `$module->deactivate()` | desactiva items (`active=false`), limpia cache |

---

## Mostrar/Ocultar Items en Menú

### Por Estado del Módulo

Los items están **activos** mientras:
- El módulo está `installed=true` Y `active=true`

El menú solo muestra items donde `active=true`:

```php
// En StarchoMenuItem::getCachedMenu()
return static::with(['children.children'])
    ->whereNull('parent_id')
    ->where('active', true)  // ← Solo items activos
    ->orderBy('sort_order')
    ->get();
```

### Por Ruta Protegida

También puedes proteger rutas con middleware:

```php
Route::get('contacts', [ContactController::class, 'index'])
    ->middleware('module:contacts')
    ->name('app.contacts.index');
```

Crear un middleware que verifica si el módulo está activo.

---

## Estructura de Configuración Completa

```php
'config' => [
    'menu_items' => [
        [
            'label'       => 'Tareas',           // Requerido
            'icon'        => 'clipboard-document-list', // Opcional
            'route'       => 'app.tasks.index',  // Opcional (si no, usa url)
            'url'         => 'https://...',      // Opcional (fallback de route)
            'sort_order'  => 20,                 // Opcional (default: 0)
            'target'      => '_self',            // Opcional (default: '_self')
            'parent_id'   => null,               // Opcional para subcategorías
        ],
    ],
],
```

---

## CSS y Layouts

### Problema Resuelto

Anteriormente, navegar de `/app` a `/admin` con `wire:navigate` NO recargaba los CSS específicos de cada layout.

### Solución

Los links de navegación entre áreas ahora hacen un **full page reload**:

- En `/app`: Link a `/admin` NO tiene `wire:navigate` → full reload
- En `/admin`: Link a `/app` NO tiene `wire:navigate` → full reload

Así cada área carga sus CSS propios:
- `/app` carga: `starcho-app.css` + `app.js`
- `/admin` carga: `starcho-admin.css` + `admin.js`

---

## Ejemplo Completo: Crear un Nuevo Módulo

### 1. Crear la Migración (si necesita tablas)

```bash
php artisan make:migration create_invoices_table
```

### 2. Registrar el Módulo en el Seeder

```php
// database/seeders/StarchoSeeder.php
$modules = [
    [
        'key'         => 'invoices',
        'name'        => 'Facturas',
        'description' => 'Sistema de facturación',
        'icon'        => 'document-text',
        'installed'   => false,
        'active'      => false,
        'config'      => [
            'menu_items' => [
                [
                    'label'      => 'Facturas',
                    'icon'       => 'document-text',
                    'route'      => 'app.invoices.index',
                    'sort_order' => 40,
                ],
            ],
        ],
    ],
];
```

### 3. Crear las Rutas

```php
// routes/app.php (dentro del grupo /app)
Route::view('invoices', 'invoices.index')->name('invoices.index');
```

### 4. Crear la Vista

```blade
<!-- resources/views/invoices/index.blade.php -->
<x-layouts::app title="Facturas">
    <!-- contenido -->
</x-layouts::app>
```

### 5. El Usuario Instala en `/admin/modules`

- Se crea automáticamente el item "Facturas" en el menú
- Aparece en `/app` sidebar con icon + label
- Usuario puede desactivar/desinstalar cuando quiera

---

## Cache

El cache de menú se limpia automáticamente cuando:
- Instalar módulo
- Desinstalar módulo
- Activar módulo
- Desactivar módulo
- Crear/editar/eliminar items manualmente en `/admin/menu`

Key: `starcho_menu_items` (TTL: 1 hora)

```php
StarchoMenuItem::clearMenuCache(); // Limpiar manualmente
```

---

## Troubleshooting

### El menú no se actualiza después de instalar

1. Verifica que el módulo tenga `config.menu_items`
2. Limpia el cache: `php artisan cache:clear` o `/admin/cache`
3. Verifica que el módulo esté registrado en BD

### Los items no aparecen en el menú

- ¿El módulo está `active=true`?
- ¿Los items de menú están `active=true`?
- ¿El cache está limpio?

### Los CSS no se cargan al cambiar de área

- ¿Usaste `wire:navigate` en el link? → Solo funciona dentro de la misma área
- Actualiza la página manualmente (F5)

---

## API Pública (para admins/developers)

```php
// Crear/actualizar módulo
StarchoModule::updateOrCreate(['key' => 'mymodule'], [...]);

// Instalar módulo
$module = StarchoModule::where('key', 'mymodule')->first();
$module->install();

// Desinstalar módulo
$module->uninstall();

// Activar módulo
$module->activate();

// Desactivar módulo
$module->deactivate();

// Crear items de menú manualmente
$module->createMenuItems();

// Verificar si módulo está activo
StarchoModule::isActive('mymodule');

// Obtener menú cacheado
$menu = StarchoMenuItem::getCachedMenu();

// Limpiar cache
StarchoMenuItem::clearMenuCache();
```

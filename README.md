# Starcho — Laravel Starter Kit

Starter kit modular construido sobre **Laravel 13 + Livewire 4 + Flux UI v2**.
Incluye panel de administración (`/admin`), área de usuario (`/app`), sistema de módulos gestionable desde el admin y arquitectura de assets separada por área.

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13, PHP 8.3+ |
| Frontend reactividad | Livewire 4 + Alpine.js 3 |
| UI components | Flux UI v2 (área `/admin`) |
| Tablas reactivas | PowerGrid v6 |
| Estilos base | Tailwind CSS v4 (Vite plugin) |
| Build tool | Vite 6 |
| Autenticación | Laravel Breeze / Livewire starter kit |
| Roles y permisos | Spatie Laravel Permission v7 |
| Notificaciones JS | Notiflix |
| Tipografía `/app` | DM Sans + Space Mono (Google Fonts CDN) |
| Iconos `/app` | Font Awesome 6.5 (CDN) |
| Iconos `/admin` | Heroicons (Flux integrado) |

---

## Instalación

```bash
git clone <repo>
cd starcho
composer install
npm install
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env, luego:
php artisan migrate --seed
npm run dev
```

El seeder crea:
- Usuario administrador: `admin@starcho.com` / `password`
- Rol `admin` asignado al usuario
- Permisos básicos de roles/permisos/usuarios
- Módulo `tasks` (instalado y activo)
- Módulo `contacts` (disponible, no instalado)
- Ítems de menú para `/app`: Dashboard, Mis Tareas, Contactos

---

## Cambios recientes

### UI app alineada con admin
El área `/app` mantiene un lenguaje visual consistente con `/admin`, pero con assets y layout propios:

- `resources/css/starcho-app.css` contiene los componentes visuales compartidos del área app
- Las páginas de `tasks` y `contacts` usan cabeceras homogéneas con CTA principal en el header
- Los modales de tareas y contactos usan la variante visual `sc-modal-kick`
- Se ajustó el ancho útil del contenido principal para aprovechar mejor el espacio horizontal

### Internacionalización aplicada a la UI de app
La interfaz de `/app` ya no depende de textos hardcodeados en Blade para tareas, contactos y layout:

- `lang/es/tasks.php` y `lang/en/tasks.php` centralizan títulos, labels, placeholders, estados y botones de tareas
- `lang/es/contacts.php` y `lang/en/contacts.php` hacen lo mismo para contactos
- `lang/es/app_layout.php` y `lang/en/app_layout.php` cubren sidebar, topbar, notificaciones y logout
- Las vistas Blade consumen estas claves mediante `__()` y respetan automáticamente el locale activo del usuario

### Tasks (/app/tasks)
- Header internacionalizado con botón `Nueva Tarea` o `New Task`
- Cards de métricas internacionalizadas
- Modal admin-style con labels, estados, prioridades y acciones traducibles

### Contacts (/app/contacts)
- Header internacionalizado con botón `Nuevo Contacto` o `New Contact`
- 6 tarjetas de estadísticas internacionalizadas
- Modal visualmente alineado con tasks, con textos y placeholders traducibles
- Apertura del modal mediante evento Livewire `openContact`

### Layout de aplicación
- Sidebar y topbar internacionalizados
- Textos de búsqueda, notificaciones, perfil y cierre de sesión extraídos a archivos de idioma
- Modal de logout traducible por locale

### Estructura de módulos (/app y /admin)
- Se documentó el estándar de implementación en `MODULES_AND_MENU.md`.
- Incluye estructura de archivos, convención de eventos Livewire, patrón de modales Flux y checklist de publicación.

---

```
starcho/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── CacheController.php     — Limpieza de caché
│   │   │   │   ├── MenuController.php      — Constructor de menú
│   │   │   │   ├── ModuleController.php    — CRUD de módulos
│   │   │   │   ├── PermissionController.php
│   │   │   │   ├── RoleController.php
│   │   │   │   ├── TaskController.php
│   │   │   │   └── UserController.php
│   │   │   └── LanguageController.php      — Cambio de idioma (es/en/pt-BR)
│   │   └── Middleware/
│   │       └── SetLocale.php               — Aplica locale del usuario
│   ├── Livewire/
│   │   ├── Admin/
│   │   │   ├── MenuBuilder.php             — Árbol CRUD de ítems de menú
│   │   │   ├── ModulesManager.php          — Instalar/desinstalar módulos
│   │   │   ├── PermissionsTable.php        — PowerGrid de permisos
│   │   │   ├── RolesTable.php              — PowerGrid de roles
│   │   │   └── UsersTable.php              — PowerGrid de usuarios
│   │   └── App/
│   │       └── ContactsTable.php           — PowerGrid de contactos
│   ├── Models/
│   │   ├── Contact.php                     — SoftDeletes, statuses
│   │   ├── StarchoMenuItem.php             — Árbol de menú con caché
│   │   ├── StarchoModule.php               — Módulos instalables
│   │   └── User.php                        — HasRoles (Spatie)
│   └── PowerGrid/                          — Clases PowerGrid legacy
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_starcho_modules_table.php
│   │   ├── ..._create_starcho_menu_items_table.php
│   │   └── ..._create_contacts_table.php
│   └── seeders/
│       ├── AdminSeeder.php                 — Usuario admin + roles
│       └── StarchoSeeder.php               — Módulos + menú inicial
│
├── resources/
│   ├── css/
│   │   ├── app.css                         — Tailwind + Flux (base compartida)
│   │   ├── starcho-app.css                 — Estilos exclusivos de /app
│   │   └── starcho-admin.css              — Estilos exclusivos de /admin
│   ├── js/
│   │   ├── starcho.js                      — Librería compartida (Starcho.*)
│   │   ├── app.js                          — Entry point JS de /app
│   │   └── admin.js                        — Entry point JS de /admin
│   └── views/
│       ├── layouts/
│       │   ├── admin/sidebar.blade.php     — Layout Flux del /admin
│       │   └── app/sidebar.blade.php       — Layout custom del /app
│       ├── admin/                          — Vistas del panel admin
│       ├── livewire/                       — Componentes Livewire
│       └── partials/
│           └── head.blade.php              — <head> compartido (carga app.css)
│
├── routes/
│   ├── web.php                             — Rutas públicas (language switch)
│   ├── app.php                             — Rutas /app (auth + verified)
│   ├── admin.php                           — Rutas /admin (auth + role:admin)
│   └── settings.php                        — Rutas de configuración de perfil
│
└── lang/
    ├── es/                                 — Traducciones español
    ├── en/                                 — Traducciones inglés
    └── pt-BR/                              — Traducciones portugués brasileño
```

---

## Arquitectura de assets

### Principio: un bundle por área

Cada área de la aplicación carga solo sus assets. No existe un bundle monolítico.

```
partials/head.blade.php
  └── app.css (Tailwind + Flux — base compartida, siempre presente)

layouts/app/sidebar.blade.php
  ├── starcho-app.css (layout /app, sidebar, topbar, componentes)
  └── app.js          (starcho.js + PowerGrid)

layouts/admin/sidebar.blade.php
  ├── starcho-admin.css (overrides Flux, .sa-btn, .sa-card, .sa-stat-card)
  └── admin.js          (starcho.js + PowerGrid + adminLayout())
```

### vite.config.js — entradas configuradas

```js
input: [
    'resources/css/app.css',           // base Tailwind+Flux
    'resources/css/starcho-app.css',   // /app
    'resources/css/starcho-admin.css', // /admin
    'resources/js/app.js',             // /app JS
    'resources/js/admin.js',           // /admin JS
]
```

### resources/js/starcho.js — librería compartida

Código JS reutilizado entre `/app` y `/admin`. Exporta `window.Starcho` y define los componentes Alpine globales.

| Exportación | Tipo | Descripción |
|-------------|------|-------------|
| `Starcho.confirm(opts)` | función | Diálogo de confirmación con Notiflix |
| `Starcho.notify(type, msg)` | función | Despacha evento `notify` para toasts |
| `Starcho.alert(type, msg)` | alias | Alias de `notify` |
| `Starcho.dark.toggle()` | método | Alterna tema oscuro/claro |
| `Starcho.dark.set('dark')` | método | Fuerza un tema específico |
| `window.starchoDelete(id, name, event, component)` | función | Confirma eliminación y despacha evento Livewire |
| `window.starchoApp(openMenuIds)` | Alpine component | Estado global del layout `/app` |
| `window.adminLayout()` | Alpine component | Reservado para expansiones del `/admin` |

**Uso desde Blade/inline JS:**

```js
// Confirmación genérica
Starcho.confirm({
    title: 'Borrar registro',
    message: '¿Continuar?',
    onConfirm: () => { /* ... */ }
});

// Toast
Starcho.notify('success', 'Operación completada');

// Eliminar registro desde PowerGrid
starchoDelete(row.id, row.name, 'deleteRole', 'admin.roles-table');
```

### CSS custom properties

**`starcho-app.css`** — prefijo `--` (heredado del template original):

```css
--primary: #fe2c55    /* rojo Starcho */
--purple:  #7c3aed
--cyan:    #25f4ee
--bg, --bg2, --card   /* fondos */
--text, --text2, --text3, --text4
--border, --border2
--sidebar-w: 264px
--topbar-h:  64px
```

**`starcho-admin.css`** — prefijo `--sa-`:

```css
--sa-primary: #fe2c55
--sa-radius:  10px
--sa-shadow-sm, --sa-shadow-md
```

---

## Sistema de módulos

Los módulos permiten activar/desactivar funcionalidades completas desde el panel `/admin/modules`.

### Modelo `StarchoModule`

```php
// Instalar: activa el módulo y sus ítems de menú, limpia caché
$module->install();

// Desinstalar: desactiva el módulo y oculta sus ítems de menú
$module->uninstall();

// Solo activar/desactivar (sin tocar el schema)
$module->activate();
$module->deactivate();

// Verificar si un módulo está activo (cacheado 1h)
StarchoModule::isActive('contacts');
```

### Tabla `starcho_modules`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `key` | string unique | Identificador (`tasks`, `contacts`, ...) |
| `name` | string | Nombre legible |
| `description` | text | Descripción |
| `icon` | string | Clase heroicon/fa |
| `installed` | boolean | ¿El módulo fue instalado? |
| `active` | boolean | ¿Aparece en el menú? |
| `config` | json | Configuración extendida del módulo |

### Ciclo de vida de un módulo

```
Disponible → [install()] → Instalado+Activo → [deactivate()] → Instalado+Inactivo
                                    ↓                                    ↓
                             [uninstall()]                        [activate()]
                                    ↓
                            Disponible de nuevo
```

---

## Sistema de menú lateral (`/app`)

El menú de `/app` es **100% dinámico desde la base de datos**, cacheado 1 hora en Redis/file.

### Tabla `starcho_menu_items`

| Campo | Descripción |
|-------|-------------|
| `module_key` | Módulo propietario (nullable para ítems del core) |
| `parent_id` | Auto-referencia para árbol de 3 niveles |
| `section` | Etiqueta de sección (ej. "App", "Sistema") |
| `label` | Texto del ítem |
| `icon` | Nombre heroicon (mapeado a FA en el blade) |
| `route` | Nombre de ruta Laravel (`app.tasks.index`) |
| `url` | URL directa (para rutas externas) |
| `target` | `_self` o `_blank` |
| `sort_order` | Orden dentro del mismo padre |
| `active` | Visible o no en el menú |

### Caché

```php
// Obtener menú (usa cache automáticamente)
StarchoMenuItem::getCachedMenu();

// Invalidar (necesario al cambiar ítems de menú)
StarchoMenuItem::clearMenuCache();
// o desde el panel: POST /admin/cache/clear-menu
```

La caché del menú se invalida automáticamente al instalar/desinstalar/activar/desactivar un módulo.

### Mapa de iconos heroicon → Font Awesome

El layout `/app` usa Font Awesome pero la DB guarda nombres heroicon.
La traducción ocurre en el `@php` inicial de `layouts/app/sidebar.blade.php` mediante el array `$faMap`.

Para añadir iconos:

```php
// En layouts/app/sidebar.blade.php, array $faMap:
'mi-icono-heroicon' => 'fas fa-mi-icono-fa',
```

---

## Panel de administración (`/admin`)

Acceso: usuarios con rol `admin`. Ruta base: `/admin`.

### Secciones

| Sección | Ruta | Descripción |
|---------|------|-------------|
| Roles | `/admin/roles` | CRUD + import/export JSON |
| Permisos | `/admin/permissions` | CRUD + import/export JSON |
| Usuarios | `/admin/users` | CRUD + asignación de roles |
| Tareas | `/admin/tasks` | Listado global con PowerGrid |
| **Módulos** | `/admin/modules` | Instalar/activar/desactivar módulos |
| **Menú lateral** | `/admin/menu` | Árbol CRUD del menú de `/app` |
| **Caché** | `/admin/cache` | Limpiar cachés de la aplicación |

### Clases CSS del admin (`starcho-admin.css`)

| Clase | Uso |
|-------|-----|
| `.sa-btn`, `.sa-btn-primary`, `.sa-btn-danger`, ... | Botones estandarizados |
| `.sa-btn-sm`, `.sa-btn-icon` | Modificadores de tamaño |
| `.sa-card`, `.sa-card-header`, `.sa-card-body` | Tarjetas de contenido |
| `.sa-field`, `.sa-label`, `.sa-input`, `.sa-select` | Formularios |
| `.sa-badge-success`, `.sa-badge-danger`, ... | Estados y etiquetas |
| `.sa-dot-success`, `.sa-dot-danger` | Indicadores de punto |
| `.sa-page-header`, `.sa-page-header-actions` | Cabeceras de página |
| `.sa-stat-card`, `.sa-stats-grid` | Cards de métricas |
| `.sa-pg-action`, `.sa-pg-action-danger` | Botones de acción en PowerGrid |
| `.sa-toast-stack`, `.sa-toast-success`, ... | Sistema de toasts del admin |

---

## Área de usuario (`/app`)

Acceso: usuarios autenticados y verificados. Ruta base: `/app`.

### Layout (`layouts/app/sidebar.blade.php`)

- **Sidebar collapsible** (264px expandido / 68px colapsado) con persistencia en `localStorage`
- **Menú de 3 niveles** con animación smooth y conectores visuales
- **Topbar** sin botón de colapso: hamburger móvil, búsqueda, dark mode, notificaciones, logout
- **Popup de usuario** en el footer del sidebar (se abre hacia arriba)
- **Modal de logout** con confirmación
- **Toasts** mediante evento `@notify.window`
- **Dark mode** persistido en `localStorage['starcho_theme']`

### Componente Alpine `starchoApp(openMenuIds)`

Se monta en `<html x-data="starchoApp({!! json_encode($openMenuIds) !!})">`.
El array `openMenuIds` es calculado server-side para abrir automáticamente el menú padre de la ruta activa.

| Propiedad | Descripción |
|-----------|-------------|
| `isDark` | Tema oscuro activo |
| `sidebarCollapsed` | Sidebar en modo icónico |
| `mobOpen` | Sidebar visible en móvil |
| `showLogout` | Modal de confirmación de logout visible |
| `search` | Valor del campo de búsqueda |
| `openMenus` | IDs de submenús abiertos |

### Módulos del área /app

| Módulo | Ruta | Estado por defecto |
|--------|------|--------------------|
| Core (dashboard) | `/app` | Siempre activo |
| Tasks | `/app/tasks` | Instalado + activo |
| Contacts | `/app/contacts` | Vista disponible en el área app |

---

## Internacionalización

Idiomas disponibles: **Español** (default), **English**, **Português (BR)**.

Cambio de idioma:
```
GET /language/{locale}   ← locale: es | en | pt-BR
```

El locale se persiste en `users.locale` y se aplica vía `SetLocale` middleware en `bootstrap/app.php`.

### Archivos de traducción relevantes

```text
lang/
├── es/
│   ├── app_layout.php
│   ├── contacts.php
│   └── tasks.php
└── en/
    ├── app_layout.php
    ├── contacts.php
    └── tasks.php
```

### Cobertura actual

- Sidebar, topbar, logout y notificaciones del layout `/app`
- Títulos, subtítulos, cards y CTA de `tasks` y `contacts`
- Labels, placeholders, opciones de select y botones de ambos modales

Para añadir nuevos textos en la UI de `/app`, conviene seguir el mismo patrón por dominio funcional en lugar de ampliar `lang/es.json` o `lang/en.json`.

---

## PowerGrid — tablas reactivas

### Componentes existentes

| Componente | Área | Modelo |
|-----------|------|--------|
| `Admin\RolesTable` | /admin | Role (Spatie) |
| `Admin\PermissionsTable` | /admin | Permission (Spatie) |
| `Admin\UsersTable` | /admin | User |
| `App\ContactsTable` | /app | Contact |

### Confirmación de eliminación en PowerGrid

```php
// En columns() del componente PowerGrid:
->button('delete')
    ->attributes([
        'onclick' => "starchoDelete({id}, '{name}', 'deleteRole', 'admin.roles-table')"
    ])

// En el componente Livewire:
#[On('deleteRole')]
public function deleteRole(int $id): void
{
    Role::findOrFail($id)->delete();
    $this->dispatch('notify', type: 'success', message: 'Rol eliminado');
}
```

---

## Toasts / Notificaciones

**Desde JS:**
```js
Starcho.notify('success', 'Operación completada');
Starcho.notify('warning', 'Atención requerida');
Starcho.notify('error', 'Error al procesar');
```

**Desde Livewire:**
```php
$this->dispatch('notify', type: 'success', message: 'Guardado correctamente');
```

Tipos válidos: `success`, `warning`, `error`. Duración: 4 segundos.

---

## Gestión de caché

| Caché | Clave | TTL | Panel admin |
|-------|-------|-----|-------------|
| Menú lateral | `starcho_menu_items` | 1h | `/admin/cache` → Menú lateral |
| Estado de módulo | `starcho_module_{key}` | 1h | Se invalida automáticamente |
| Permisos Spatie | interna Spatie | sesión | `/admin/cache` → Permisos |
| App/rutas/config | `php artisan optimize` | — | `/admin/cache` → Optimizar |

---

## Añadir un nuevo módulo

1. **Registrar en `starcho_modules`** (via seeder o desde el panel):
   ```php
   StarchoModule::create([
       'key'         => 'mi-modulo',
       'name'        => 'Mi Módulo',
       'description' => 'Descripción breve',
       'icon'        => 'puzzle-piece',
       'installed'   => false,
       'active'      => false,
   ]);
   ```

2. **Crear ítems de menú** en `starcho_menu_items`:
   ```php
   StarchoMenuItem::create([
       'module_key' => 'mi-modulo',
       'label'      => 'Mi Módulo',
       'icon'       => 'star',
       'route'      => 'app.mi-modulo.index',
       'sort_order' => 10,
       'active'     => false,
   ]);
   ```

3. **Crear las rutas** en `routes/app.php`:
   ```php
   Route::view('mi-modulo', 'mi-modulo.index')->name('mi-modulo.index');
   ```

4. **Instalar desde el panel**: `/admin/modules` → botón "Instalar".

---

## Convenciones de código

### Blade / Livewire
- Área `/admin`: layout `x-layouts::admin`
- Área `/app`: layout `x-layouts::app`
- Componentes Livewire en `app/Livewire/{Area}/`
- Vistas Livewire en `resources/views/livewire/{area}/`

### CSS
- `/app`: clases sin prefijo (`.sidebar`, `.menu-link`, `.btn`) — namespace implícito por layout
- `/admin`: prefijo `.sa-` para evitar colisiones con clases Flux/Tailwind
- No usar `@apply` en CSS custom — solo reglas CSS nativas
- No duplicar propiedades ya definidas en `app.css` (Tailwind base)

### JS
- Código reutilizable → `starcho.js`
- Código específico de `/app` → `app.js`
- Código específico de `/admin` → `admin.js`
- Datos server-side a Alpine: `x-data="starchoApp({!! json_encode($data) !!})"`

---

## Comandos útiles

```bash
# Desarrollo con hot reload
npm run dev

# Producción
npm run build

# Reset completo de BD
php artisan migrate:fresh --seed

# Limpiar todo el caché
php artisan cache:clear && php artisan view:clear && php artisan config:clear

# Rutas registradas
php artisan route:list --path=admin
php artisan route:list --path=app
```

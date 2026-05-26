# Starcho — Laravel Starter Kit Modular

Starter kit modular construido sobre **Laravel 13 + Livewire 4 + Flux UI v2**.

Incluye panel de administración completo (`/admin`), área de usuario (`/app`), sistema de módulos instalables desde el admin, blog con soporte multilenguaje, gestión de medios/almacenamiento, suscripciones, geolocalizacion de usuarios, y arquitectura de assets separada por área.

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
| Autenticación | Laravel Fortify (2FA incluido) |
| Roles y permisos | Spatie Laravel Permission v7 |
| Traducción de modelos | Spatie Laravel Translatable |
| Import / Export | Maatwebsite Excel |
| Notificaciones JS | Notiflix |
| Gráficas | ApexCharts (CDN) |
| Tipografía `/app` | DM Sans + Space Mono (Google Fonts CDN) |
| Iconos `/app` | Font Awesome 6.5 (CDN) |
| Iconos `/admin` | Heroicons (Flux integrado) |

---

## Instalación rápida

```bash
git clone <repo>
cd starcho
php artisan starcho:install
npm run dev
```

El comando `php artisan starcho:install` realiza automáticamente:

- Creación de `.env` desde `.env.example` si no existe
- Solicitud interactiva del motor (`mysql` o `pgsql`) y credenciales de BD
- `composer install` y `npm install`
- Migraciones y seeder `StarchoInstallAppSeeder`
- Creación de `storage:link` cuando aplica
- Build de frontend con `npm run build` al final

Progreso por etapas durante la ejecución:

```
Paso 1/9  Preparando archivo de entorno
Paso 2/9  Configurando base de datos
Paso 5/9  Instalando dependencias
Paso 6/9  Migrando base de datos
Paso 8/9  Generando assets de producción
OK: ...   al finalizar cada etapa
```

Opciones útiles:

```bash
# Sin confirmación inicial
php artisan starcho:install --force

# Ayuda completa
php artisan help starcho:install
```

El seeder crea:

- Usuario administrador: `admin@starcho.com` / `password`
- Roles `admin` y `root` asignados
- Permisos básicos de roles/permisos/usuarios
- Módulo `tasks` (instalado y activo)
- Módulo `contacts` (disponible, no instalado)
- Ítems de menú para `/app`: Dashboard, Mis Tareas, Contactos

---

## Flujo de desarrollo local

```bash
composer install
npm install
php artisan migrate --seed --seeder=StarchoInstallAppSeeder
npm run dev
```

Build de producción:

```bash
npm run build
php artisan optimize
```

> Si los estilos no cargan en producción/local con build, verificar que `public/hot` no exista:
> ```bash
> Remove-Item public/hot
> php artisan optimize:clear
> ```

---

## Estructura del proyecto

```
starcho/
├── app/
│   ├── Console/Commands/
│   │   └── StarchoInstallCommand.php       — Instalador guiado interactivo
│   ├── Exports/                            — Clases Excel por área (app/admin)
│   ├── Imports/                            — Clases de importación por área
│   ├── Http/Controllers/
│   │   ├── Admin/
│   │   │   ├── CacheController.php         — Limpieza de caché
│   │   │   ├── ContentSettingsController   — Config de contenido/blog
│   │   │   ├── DashboardController.php     — Datos del dashboard admin
│   │   │   ├── GeoLocationsController.php  — Geolocalizaciones de usuarios
│   │   │   ├── MediaController.php         — Galería multimedia
│   │   │   ├── MenuController.php          — Constructor de menú
│   │   │   ├── ModuleController.php        — CRUD de módulos
│   │   │   ├── PermissionController.php
│   │   │   ├── PostController.php          — Blog posts
│   │   │   ├── RoleController.php
│   │   │   ├── SiteController.php          — Configuración del sitio
│   │   │   ├── StorageSettingsController   — Planes y storage
│   │   │   ├── TaskController.php
│   │   │   ├── UserBanController.php       — Baneo de usuarios
│   │   │   └── UserController.php
│   │   ├── BlogController.php              — Blog público
│   │   ├── LanguageController.php          — Cambio de idioma (es/en/pt-BR)
│   │   ├── PageController.php              — Páginas estáticas
│   │   └── SitemapController.php           — Sitemap XML
│   ├── Livewire/
│   │   ├── Admin/
│   │   │   ├── ContactsTable.php
│   │   │   ├── GeoLocationsTable.php
│   │   │   ├── MenuBuilder.php             — Árbol CRUD de ítems de menú
│   │   │   ├── ModulesManager.php          — Instalar/desinstalar módulos
│   │   │   ├── NotesTable.php
│   │   │   ├── PagesTable.php
│   │   │   ├── PermissionsTable.php
│   │   │   ├── PostCategoriesTable.php
│   │   │   ├── PostsTable.php
│   │   │   ├── PostTagsTable.php
│   │   │   ├── RolesTable.php
│   │   │   ├── TasksTable.php
│   │   │   ├── UserBansTable.php
│   │   │   └── UsersTable.php
│   │   ├── App/
│   │   │   ├── ContactsTable.php
│   │   │   └── NotesTable.php
│   │   ├── Tasks/
│   │   │   └── UserTasksTable.php
│   │   └── Concerns/
│   │       └── DispatchesStarchoNotify.php — Trait de notificaciones CRUD
│   ├── Models/
│   │   ├── AppSetting.php
│   │   ├── BrokenLink.php
│   │   ├── Contact.php                     — SoftDeletes + ownership + statuses
│   │   ├── ContentSetting.php
│   │   ├── GeoIPCache.php
│   │   ├── Media.php
│   │   ├── Note.php
│   │   ├── Post.php                        — Translatable (spatie)
│   │   ├── PostCategory.php
│   │   ├── PostTag.php
│   │   ├── SiteLanguage.php
│   │   ├── SitePageSetting.php
│   │   ├── SiteSetting.php
│   │   ├── SiteSocialNetwork.php
│   │   ├── StarchoMenuItem.php             — Árbol de menú con caché
│   │   ├── StarchoMenuSection.php
│   │   ├── StarchoModule.php               — Módulos instalables
│   │   ├── StoragePlan.php
│   │   ├── StorageSetting.php
│   │   ├── Subscription.php
│   │   ├── Task.php
│   │   ├── User.php                        — HasRoles + TwoFactor + HasBan
│   │   ├── UserBan.php
│   │   └── UserGeoLocation.php
│   └── Models/Concerns/
│       └── EnforcesOwnership.php           — Scope + hooks de ownership por user_id
│
├── database/
│   ├── migrations/                         — 48 migraciones ordenadas cronológicamente
│   └── seeders/
│       ├── AdminSeeder.php                 — Usuario admin + roles
│       ├── BlogPostAiLaravelSeeder.php
│       ├── BlogPostWebDesignSeeder.php
│       ├── BlogTaxonomySeeder.php
│       ├── ImportantPagesSeeder.php
│       ├── MenuSeeder.php
│       ├── SiteLanguagesSeeder.php
│       ├── SiteSocialNetworksSeeder.php
│       ├── StarchoInstallAppSeeder.php     — Punto único de inicialización
│       ├── StarchoSeeder.php               — Módulos + menú inicial
│       └── StoragePlansSeeder.php
│
├── resources/
│   ├── css/
│   │   ├── app.css                         — Tailwind + Flux (base compartida)
│   │   ├── starcho-admin.css               — Estilos exclusivos de /admin
│   │   ├── starcho-app.css                 — Estilos exclusivos de /app
│   │   ├── starcho-auth.css                — Estilos de login/register
│   │   ├── starcho-components.css          — Componentes Blade reutilizables
│   │   └── starcho-home.css                — Landing pública
│   ├── js/
│   │   ├── starcho.js                      — Librería compartida (window.Starcho)
│   │   ├── app.js                          — Entry point JS de /app
│   │   ├── admin.js                        — Entry point JS de /admin
│   │   └── starcho-editor-page.js          — Editor de páginas
│   └── views/
│       ├── layouts/
│       │   ├── admin/sidebar.blade.php     — Layout Flux del /admin
│       │   └── app/sidebar.blade.php       — Layout custom del /app
│       ├── admin/                          — Vistas del panel admin (20 secciones)
│       ├── components/                     — 36 componentes Blade reutilizables
│       ├── livewire/                       — Componentes Livewire
│       └── partials/
│           └── head.blade.php              — <head> compartido
│
├── routes/
│   ├── web.php                             — Rutas públicas (home, blog, pages, language)
│   ├── app.php                             — Rutas /app (auth + verified)
│   ├── admin.php                           — Rutas /admin (auth + role:admin)
│   └── settings.php                        — Rutas de configuración de perfil
│
└── lang/
    ├── es/                                 — Traducciones español (15 archivos)
    ├── en/                                 — Traducciones inglés (15 archivos)
    └── pt_BR/                              — Traducciones portugués brasileño
```

---

## Panel de administración (`/admin`)

Acceso: usuarios con rol `admin` o `root`. Ruta base: `/admin`.

| Sección | Ruta | Descripción |
|---------|------|-------------|
| Dashboard | `/admin` | Métricas generales con gráficas ApexCharts |
| Roles | `/admin/roles` | CRUD + import/export Excel |
| Permisos | `/admin/permissions` | CRUD + import/export Excel |
| Usuarios | `/admin/users` | CRUD + asignación de roles |
| Baneos | `/admin/users-ban` | Baneos temporales y permanentes |
| Geolocalizaciones | `/admin/geolocations` | Mapa y tabla de accesos por IP |
| Tareas | `/admin/tasks` | Listado global con PowerGrid |
| Contactos | `/admin/contacts` | Gestión global de contactos |
| Notas | `/admin/notes` | Gestión global de notas |
| Blog — Posts | `/admin/posts` | CRUD multilenguaje de posts |
| Blog — Categorías | `/admin/post-categories` | Taxonomías multilenguaje |
| Blog — Etiquetas | `/admin/post-tags` | Tags multilenguaje |
| Páginas | `/admin/pages` | Páginas estáticas con editor |
| Medios | `/admin/media` | Galería multimedia (storage plan) |
| Configuración del sitio | `/admin/site` | Branding, idiomas, redes sociales |
| Almacenamiento | `/admin/storage` | Planes de almacenamiento |
| Contenido | `/admin/content` | Opciones de sitemap y contenido |
| **Módulos** | `/admin/modules` | Instalar/activar/desactivar módulos |
| **Menú lateral** | `/admin/menu` | Árbol CRUD del menú de `/app` |
| **Caché** | `/admin/cache` | Limpiar cachés de la aplicación |

---

## Área de usuario (`/app`)

Acceso: usuarios autenticados y verificados. Ruta base: `/app`.

| Módulo | Ruta | Estado por defecto |
|--------|------|--------------------|
| Core (dashboard) | `/app` | Siempre activo |
| Tasks | `/app/tasks` | Instalado + activo |
| Contacts | `/app/contacts` | Disponible (instalar desde admin) |
| Notes | `/app/notes` | Disponible (instalar desde admin) |

### Layout (`layouts/app/sidebar.blade.php`)

- **Sidebar collapsible** (264px / 68px) con persistencia en `localStorage`
- **Menú de 3 niveles** con animación smooth y conectores visuales
- **Topbar**: búsqueda, dark mode, notificaciones, logout
- **Popup de usuario** en el footer del sidebar
- **Modal de logout** con confirmación
- **Toasts** mediante evento `@notify.window`
- **Dark mode** persistido en `localStorage['starcho_theme']`

---

## Sistema de módulos

Los módulos permiten activar/desactivar funcionalidades completas desde `/admin/modules`.

```php
// Instalar: activa el módulo y crea ítems de menú
$module->install();

// Desinstalar: elimina ítems de menú, no borra datos de negocio
$module->uninstall();

// Solo activar/desactivar sin tocar schema
$module->activate();
$module->deactivate();

// Verificar estado (cacheado 1h)
StarchoModule::isActive('contacts');
```

### Ciclo de vida

```
Disponible → [install()] → Instalado+Activo → [deactivate()] → Instalado+Inactivo
                                   ↓                                   ↓
                            [uninstall()]                        [activate()]
                                   ↓
                           Disponible de nuevo
```

> Los datos del módulo **nunca se borran** con `uninstall()`. Solo se oculta el módulo del menú.

### Registrar un módulo nuevo

```php
StarchoModule::updateOrCreate(
    ['key' => 'invoices'],
    [
        'name'        => 'Facturas',
        'description' => 'Sistema de facturación',
        'icon'        => 'document-text',
        'installed'   => false,
        'active'      => false,
        'config'      => [
            'menu_items' => [
                [
                    'panel'      => 'app',
                    'section'    => 'App',
                    'name'       => ['es' => 'Facturas', 'en' => 'Invoices', 'pt_BR' => 'Faturas'],
                    'icon'       => 'document-text',
                    'route'      => 'app.invoices.index',
                    'sort_order' => 40,
                    'target'     => '_self',
                ],
            ],
        ],
    ]
);
```

---

## Sistema de menú lateral (`/app`)

El menú es **100% dinámico desde la base de datos**, cacheado 1 hora.

```php
// Obtener menú (usa caché automáticamente)
StarchoMenuItem::getCachedMenu();

// Invalidar caché
StarchoMenuItem::clearMenuCache();
```

La caché se invalida automáticamente al instalar/desinstalar/activar/desactivar módulos o editar ítems desde `/admin/menu`.

---

## Arquitectura de assets

Cada área carga solo sus propios assets. No existe bundle monolítico.

```
partials/head.blade.php
  └── app.css           (Tailwind + Flux — base compartida)

layouts/app/sidebar.blade.php
  ├── starcho-app.css   (layout /app, sidebar, topbar, componentes)
  └── app.js            (starcho.js + PowerGrid)

layouts/admin/sidebar.blade.php
  ├── starcho-admin.css (overrides Flux, .sa-btn, .sa-card, .sa-stat-card)
  └── admin.js          (starcho.js + PowerGrid + adminLayout())
```

### vite.config.js — entradas configuradas

```js
input: [
    'resources/css/app.css',
    'resources/css/starcho-app.css',
    'resources/css/starcho-admin.css',
    'resources/css/starcho-auth.css',
    'resources/css/starcho-home.css',
    'resources/css/starcho-components.css',
    'resources/js/app.js',
    'resources/js/admin.js',
]
```

---

## Componentes Blade reutilizables

| Componente | Propósito |
|-----------|-----------|
| `x-starcho-popup-kick` / `stripe` / `tiktok` | Modales por skin visual del módulo |
| `x-starcho-btn-kick` / `stripe` / `tiktok` | CTA principal por skin |
| `x-starcho-card-app-kick` / `stripe` / `tiktok` | Tarjetas de métricas por skin |
| `x-starcho-card-admin-stats` | Tarjeta de estadísticas para `/admin` |
| `x-starcho-crud1` | Acciones editar/eliminar en PowerGrid |
| `x-starcho-btn-view-table` | Toggle de columnas visibles en PowerGrid |
| `x-starcho-btn-excel` | Botón de exportación Excel (bulk) |
| `x-starcho-noty` | Icono de notificaciones con dropdown |
| `x-starcho-alert` | Toast del sistema (evento `notify`) |
| `x-starcho-active` | Estado activo/inactivo con icono semántico |
| `x-starcho-active-switch` | Switch booleano en formularios |
| `x-starcho-status` | Estado multilenguaje con color e icono |
| `x-starcho-chart` | Gráfica ApexCharts (8 tipos) |
| `x-starcho-popup-admin-import` | Modal de importación admin |
| `x-starcho-popup-standar` | Modal genérico reutilizable |
| `x-starcho-popup-logout` | Modal de confirmación de logout |
| `x-starcho-btn-primary` | Botón primario genérico |
| `x-starcho-home-header` / `footer` / `lang` | Componentes de la landing pública |
| `x-starcho-site-name` | Nombre del sitio dinámico desde BD |

### Skins visuales por módulo `/app`

| Skin | Módulo | Popup | Botón | Card |
|------|--------|-------|-------|------|
| **Kick** | tasks | `starcho-popup-kick` | `starcho-btn-kick` | `starcho-card-app-kick` |
| **Stripe** | contacts | `starcho-popup-stripe` | `starcho-btn-stripe` | `starcho-card-app-stripe` |
| **TikTok** | notes | `starcho-popup-tiktok` | `starcho-btn-tiktok` | `starcho-card-app-tiktok` |

---

## Gráficas (`x-starcho-chart`)

Componente universal basado en **ApexCharts + Alpine.js**. Soporta 8 tipos:

`donut` | `pie` | `bar` | `area` | `line` | `radialBar` | `heatmap` | `scatter`

```blade
@assets
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
@endassets

<x-starcho-chart
    type="donut"
    :title="'Tareas por estado'"
    :series="$byStatus->values()->toArray()"
    :labels="$byStatus->keys()->toArray()"
    :total-label="'Total'"
/>

<x-starcho-chart
    type="bar"
    :title="'Actividad (7 días)'"
    :series="[['name' => 'Tareas', 'data' => $last7Days]]"
    :categories="$last7DaysLabels"
    :height="180"
/>
```

---

## Seguridad y ownership

Se aplica control de ownership por modelo para entidades con `user_id`.

```php
// app/Models/Concerns/EnforcesOwnership.php
// Aplica global scope por user_id para usuarios no admin/root
// En creating: asigna user_id automáticamente
// En updating/deleting: valida propiedad del registro
```

Modelos con `EnforcesOwnership`:
- `Task`
- `Contact`
- `Note`

Roles que bypass el scope: `root`, `admin`.

---

## Notificaciones CRUD (`DispatchesStarchoNotify`)

Trait Livewire para toasts estandarizados en todos los módulos:

```php
// En cualquier componente Livewire (app o admin)
$this->notifyCrud('tasks', 'created', ['name' => $taskTitle]);
$this->notifyCrud('tasks', 'updated', ['name' => $taskTitle]);
$this->notifyCrud('tasks', 'deleted');
$this->notifyCrud('tasks', 'not_found');
$this->notifyCrud('tasks', 'forbidden');
```

Mapeo automático de tipo de toast:
- `success`: created, updated
- `warning`: deleted
- `failure`: not_found, forbidden, error

Desde JS:
```js
Starcho.notify('success', 'Operación completada');
Starcho.notify('warning', 'Registro eliminado');
Starcho.notify('error', 'Error al procesar');
```

---

## PowerGrid — tablas reactivas

### Componentes existentes

| Componente | Área | Modelo |
|-----------|------|--------|
| `Admin\RolesTable` | /admin | Role (Spatie) |
| `Admin\PermissionsTable` | /admin | Permission (Spatie) |
| `Admin\UsersTable` | /admin | User |
| `Admin\UserBansTable` | /admin | UserBan |
| `Admin\TasksTable` | /admin | Task |
| `Admin\ContactsTable` | /admin | Contact |
| `Admin\NotesTable` | /admin | Note |
| `Admin\PostsTable` | /admin | Post |
| `Admin\PostCategoriesTable` | /admin | PostCategory |
| `Admin\PostTagsTable` | /admin | PostTag |
| `Admin\PagesTable` | /admin | SitePageSetting |
| `Admin\GeoLocationsTable` | /admin | UserGeoLocation |
| `Tasks\UserTasksTable` | /app | Task |
| `App\ContactsTable` | /app | Contact |
| `App\NotesTable` | /app | Note |

### Setup estándar por tabla

```php
public function setUp(): array
{
    $this->persist(['columns'], 'app'); // o 'admin'

    return [
        PowerGrid::header()
            ->showSearchInput()
            ->showToggleColumns()
            ->includeViewOnTop('mi-modulo.pg-header'),
        PowerGrid::footer()
            ->showPerPage(15)
            ->showRecordCount(),
    ];
}
```

---

## Import / Export (Excel)

Disponible en todos los módulos del área admin y en los módulos del área app (tasks, contacts, notes).

| Módulo | Export admin | Import admin | Export app | Import app |
|--------|-------------|-------------|-----------|-----------|
| Roles | `AdminRolesExport` | `AdminRolesImport` | — | — |
| Permisos | `AdminPermissionsExport` | `AdminPermissionsImport` | — | — |
| Usuarios | `AdminUsersExport` | `AdminUsersImport` | — | — |
| Tasks | `AdminTasksExport` | `AdminTasksImport` | `AppTasksExport` | `AppTasksImport` |
| Contacts | `AdminContactsExport` | `AdminContactsImport` | `AppContactsExport` | `AppContactsImport` |
| Notes | `AdminNotesExport` | `AdminNotesImport` | `AppNotesExport` | `AppNotesImport` |
| Menú | `AdminMenuExport` | `AdminMenuImport` | — | — |
| Módulos | `AdminModulesExport` | `AdminModulesImport` | — | — |

---

## Internacionalización

Idiomas: **Español** (default), **English**, **Português (BR)**.

```
GET /language/{locale}    locale: es | en | pt-BR
```

El locale se persiste en `users.locale` y se aplica vía middleware `SetLocale`.

### Archivos de traducción

```
lang/
├── es/   (15 archivos: actions, admin_pages, admin_ui, app_dashboard,
│          app_layout, app_pages, auth, contacts, http-statuses,
│          js, notes, pagination, passwords, tasks, validation)
├── en/   (mismos 15 archivos)
└── pt_BR/ (mismos archivos)
```

---

## Sistema de suscripciones y almacenamiento

### Suscripciones

- Cada usuario tiene un nivel de suscripción (`free`, u otros planes).
- Al crear un usuario se genera automáticamente una suscripción activa.
- El modelo `Subscription` registra nivel, estado (`is_active`), fechas y datos del período.

### Almacenamiento

- Los usuarios pueden tener un `StoragePlan` asignado con límite de bytes.
- El modelo `Media` registra cada archivo subido con su tamaño y ruta.
- El usuario tiene métodos: `storageRemaining()`, `storageExceeded()`, `storagePct()`.
- Los planes de almacenamiento se gestionan desde `/admin/storage`.

---

## Blog multilenguaje

El módulo de blog usa **Spatie Laravel Translatable** para posts, categorías y etiquetas.

- Posts con soporte de slug, categorías, etiquetas, posición en navegación y campos de sitemap.
- Categorías y etiquetas completamente traducibles.
- Blog público accesible desde el frontend en `/blog`.
- Sitemap automático en `/sitemap.xml`.
- Las páginas estáticas se gestionan desde `/admin/pages` con editor.

---

## Geolocalización de usuarios

- Registra la IP, país, ciudad y coordenadas de cada acceso autenticado.
- Caché de resolución de IP en `GeoIPCache` para evitar consultas repetidas.
- Tabla de geolocalizaciones accesible desde `/admin/geolocations`.

---

## Bans de usuarios

- Baneos temporales (con fecha de expiración) y permanentes.
- Flujo 100% Livewire desde `/admin/users-ban`.
- El modelo `UserBan` registra motivo, tipo, fechas y quién aplicó el ban.
- El trait `HasBan` en `User` expone métodos de consulta de estado de ban.

---

## Gestión de caché

| Caché | Clave | TTL | Dónde limpiar |
|-------|-------|-----|----------------|
| Menú lateral | `starcho_menu_items` | 1h | `/admin/cache` o automático |
| Estado de módulo | `starcho_module_{key}` | 1h | Automático al instalar/activar |
| Permisos Spatie | interna Spatie | sesión | `/admin/cache` → Permisos |
| App/rutas/config | `php artisan optimize` | — | `/admin/cache` → Optimizar |

---

## CSS custom properties

**`starcho-app.css`**:
```css
--primary: #fe2c55     /* rojo Starcho */
--purple:  #7c3aed
--cyan:    #25f4ee
--sidebar-w: 264px
--topbar-h:  64px
```

**`starcho-admin.css`** (prefijo `--sa-`):
```css
--sa-primary: #fe2c55
--sa-radius:  10px
```

**Reglas CSS**:
- No usar `@apply` — solo reglas CSS nativas.
- `/app`: clases sin prefijo (`.sidebar`, `.menu-link`).
- `/admin`: prefijo `.sa-` para evitar colisiones con Flux/Tailwind.

---

## Librería JS (`starcho.js`)

| Exportación | Tipo | Descripción |
|-------------|------|-------------|
| `Starcho.confirm(opts)` | función | Diálogo de confirmación con Notiflix |
| `Starcho.notify(type, msg)` | función | Despacha evento `notify` para toasts |
| `Starcho.dark.toggle()` | método | Alterna tema oscuro/claro |
| `Starcho.dark.set('dark')` | método | Fuerza tema específico |
| `window.starchoDelete(...)` | función | Confirma eliminación y despacha evento Livewire |
| `window.starchoApp(openMenuIds)` | Alpine component | Estado global del layout `/app` |
| `window.adminLayout()` | Alpine component | Estado global del layout `/admin` |

---

## Workflow para crear un módulo nuevo

### 1. Estructura de archivos

```text
# Para /app
app/Livewire/App/<Modulo>Table.php
resources/views/<modulo>/index.blade.php
resources/views/<modulo>/pg-header.blade.php
resources/views/livewire/app/<modulo>-modal.blade.php
routes/app.php  ← agregar ruta
lang/es/<modulo>.php
lang/en/<modulo>.php
lang/pt_BR/<modulo>.php
```

### 2. Registrar en `starcho_modules`

```php
StarchoModule::updateOrCreate(['key' => 'mi-modulo'], [
    'name'      => 'Mi Módulo',
    'installed' => false,
    'active'    => false,
    'config'    => ['menu_items' => [...]],
]);
```

### 3. Checklist de publicación

1. Seguridad: `EnforcesOwnership` si tiene `user_id`.
2. Notificaciones: `notifyCrud(...)` en cada acción CRUD.
3. Componentes: reutilizar `x-starcho-popup-*`, `x-starcho-crud1`, etc.
4. PowerGrid: persistencia de columnas por panel (`app`/`admin`).
5. Traducciones: textos en `lang/es`, `lang/en`, `lang/pt_BR`.
6. Build: `npm run build` y `php artisan view:cache` sin errores.

---

## Comandos útiles

```bash
# Desarrollo con hot reload
npm run dev

# Producción
npm run build

# Reset completo de BD
php artisan migrate:fresh --seed --seeder=StarchoInstallAppSeeder

# Limpiar todo el caché
php artisan cache:clear && php artisan view:clear && php artisan config:clear

# Optimizar para producción
php artisan optimize

# Ver rutas registradas por área
php artisan route:list --path=admin
php artisan route:list --path=app

# Linter PHP
./vendor/bin/pint

# Tests
php artisan test
```

---

## Agente especializado

El proyecto incluye un agente para acelerar el desarrollo sobre la arquitectura Starcho:

- Archivo: `.github/agents/galax-starcho.agent.md`
- Fuente de verdad: `README.md` y `MODULES_AND_MENU.md`
- Enfoque: Laravel 13 + Livewire 4 + PowerGrid + arquitectura modular Starcho

---

## Credenciales de desarrollo

| Campo | Valor |
|-------|-------|
| Email | `admin@starcho.com` |
| Password | `password` |
| Rol | `admin` |

---

## Licencia

MIT

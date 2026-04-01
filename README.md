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

### Nuevos diseños reutilizables para módulos (/app)

Se incorporó un patrón profesional para crear módulos con UI consistente y mantenible usando componentes Blade reutilizables.

Diseños disponibles:
- `Kick` (tasks):
    - Popup: `starcho-popup-kick`
    - Botón: `starcho-btn-kick`
    - Card stats: `starcho-card-app-kick`
- `Stripe` (contacts):
    - Popup: `starcho-popup-stripe`
    - Botón: `starcho-btn-stripe`
    - Card stats: `starcho-card-app-stripe`
- `TikTok` (notes):
    - Popup: `starcho-popup-tiktok`
    - Botón: `starcho-btn-tiktok`
    - Card stats: `starcho-card-app-tiktok`

Archivos base:
- `resources/views/components/starcho-popup-kick.blade.php`
- `resources/views/components/starcho-popup-stripe.blade.php`
- `resources/views/components/starcho-popup-tiktok.blade.php`
- `resources/views/components/starcho-btn-kick.blade.php`
- `resources/views/components/starcho-btn-stripe.blade.php`
- `resources/views/components/starcho-btn-tiktok.blade.php`
- `resources/views/components/starcho-card-app-kick.blade.php`
- `resources/views/components/starcho-card-app-stripe.blade.php`
- `resources/views/components/starcho-card-app-tiktok.blade.php`

### Cómo construir un nuevo módulo usando estos diseños

1. Elegir diseño visual (`Kick`, `Stripe` o `TikTok`) según el módulo.
2. Crear el componente Livewire del modal (ejemplo: `livewire/app/project-modal.blade.php`) reutilizando `starcho-popup-*`.
3. Definir formulario y validaciones en el componente Livewire PHP (save, reset, eventos open/edit).
4. Renderizar CTA principal en la vista `index` con `starcho-btn-*`.
5. Renderizar tarjetas de métricas con `starcho-card-app-*` dentro de los componentes Livewire de stats.
5. Reusar convención de eventos:
     - Abrir nuevo: `open<Entity>`
     - Abrir edición: `open<Entity>` con id
     - Guardar: `wire:submit="save<Entity>"`
6. Integrar tabla PowerGrid y acciones CRUD con `starcho-crud1` + `HasStarchoCrudActions`.
7. Si el modal crece en campos, usar body con scroll interno y footer fijo (ya resuelto en estilos base de cada popup).

### API recomendada de `starcho-popup-*`

Props comunes disponibles para los tres popups:
- `name`: nombre del modal Flux.
- `title`: título principal.
- `titleAccent`: parte destacada del título.
- `subtitle`: texto secundario bajo título.
- `icon`: icono Font Awesome.
- `width`: ancho del modal.
- `submitAction`: método Livewire para submit.
- `loadingTarget`: target de loading de botón guardar.
- `cancelLabel`, `saveLabel`, `savingLabel`: textos de acciones.

Slots:
- `default`: cuerpo del formulario.
- `actions` (opcional): footer personalizado para casos especiales.

### API recomendada de `starcho-btn-*`

Props comunes:
- `variant`: `primary` o `ghost`.
- `icon`: icono Font Awesome.
- `label`: texto del botón.
- `onclick` o `wireClick`: acción.
- `loadingTarget`, `loadingLabel`: estado de carga.

### API recomendada de `starcho-card-app-*`

Props comunes:
- `label`: texto de la métrica.
- `value`: valor principal a mostrar.
- `icon`: clase de icono Font Awesome.
- `iconBg`: color de fondo del icono.
- `iconColor`: color del icono.
- `valueClass`: clase visual adicional para el valor.

Uso recomendado:
- `x-starcho-card-app-kick` para módulos app con skin Kick como tasks.
- `x-starcho-card-app-stripe` para módulos app con skin Stripe como contacts.
- `x-starcho-card-app-tiktok` para módulos app con skin TikTok como notes.

Ejemplo:

```blade
<x-starcho-card-app-kick
    :label="$stat['label']"
    :value="$stat['value']"
    :icon="$stat['icon']"
    :icon-bg="$stat['icon_bg']"
    :icon-color="$stat['icon_color']"
    :value-class="$stat['color']"
/>
```

### API recomendada de `starcho-noty`

Prop | Tipo | Default | Descripción
-----|------|---------|------------
`theme` | string | `'app'` | `app` o `admin`, elige clases CSS del panel correspondiente
`buttonClass` | string | auto | Clase del botón (auto-detectada por theme)
`wrapperClass` | string | auto | Clase del wrapper del dropdown
`dropdownClass` | string | auto | Clase del panel desplegable

Uso mínimo:
```blade
<x-starcho-noty theme="app" />
<x-starcho-noty theme="admin" />
```

El componente lee traducciones de `app_layout.notifications`, `app_layout.no_notifications` y `app_layout.view_all_activity`. No duplicar HTML de campana de notificaciones en ningún layout.

### API recomendada de `starcho-alert`

Prop | Tipo | Default | Descripción
-----|------|---------|------------
`theme` | string | `'app'` | `app` o `admin`, elige clases CSS del panel correspondiente

Uso mínimo:
```blade
<x-starcho-alert theme="app" />
<x-starcho-alert theme="admin" />
```

Escucha el evento Alpine `notify.window` con payload `{ type, message }`. Para disparar un toast desde cualquier parte del proyecto:
```js
window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Guardado correctamente' } }));
```
O desde Livewire:
```php
$this->dispatch('notify', type: 'success', message: __('messages.saved'));
```

### API recomendada de `x-starcho-chart`

Componente universal de gráficas basado en **ApexCharts** + **Alpine.js 3**. Se usa en dashboards, stats de módulos y cualquier pantalla que requiera visualización de datos en app o admin.

Prop | Tipo | Default | Descripción
-----|------|---------|------------
`type` | string | `'bar'` | `donut` \| `pie` \| `bar` \| `area` \| `line` \| `radialBar` \| `heatmap` \| `scatter`
`:series` | array | `[]` | Datos de la serie. Donut/Pie/RadialBar: `[12, 8, 3]`. Bar/Area/Line: `[['name'=>'X','data'=>[1,2,3]]]`
`:title` | string | `''` | Título visible sobre la gráfica
`:labels` | array | `[]` | Etiquetas de segmentos para `donut`, `pie` y `radialBar`
`:categories` | array | `[]` | Etiquetas del eje X para `bar`, `area`, `line`, `heatmap`
`:colors` | array | paleta Starcho | Paleta hex personalizada
`height` | int | `240` | Alto en px del canvas
`:total-label` | string | `'Total'` | Texto del total visible en el centro del donut
`:gradient` | bool | `true` | Activa relleno degradado en barras

Uso mínimo:
```blade
{{-- Requerir ApexCharts UNA vez en el layout o vista --}}
@assets
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
@endassets

{{-- Donut de estados --}}
<x-starcho-chart
    type="donut"
    :title="__('admin_ui.tasks.chart.by_status')"
    :series="$byStatus->values()->toArray()"
    :labels="$byStatus->keys()->toArray()"
    :colors="['#64748b','#3b82f6','#10b981','#6b7280']"
    :total-label="__('admin_ui.tasks.stats.total')"
/>

{{-- Barras de actividad semanal --}}
<x-starcho-chart
    type="bar"
    :title="'Actividad (7 días)'"
    :series="[['name' => 'Tareas', 'data' => $last7Days]]"
    :categories="$last7DaysLabels"
    :height="180"
/>

{{-- Área de tendencia mensual --}}
<x-starcho-chart
    type="area"
    :title="'Tendencia 6 meses'"
    :series="[['name' => 'Registros', 'data' => $monthly]]"
    :categories="['Ene','Feb','Mar','Abr','May','Jun']"
    :colors="['#a855f7']"
/>
```

**Portabilidad a otros proyectos Laravel:**
Copiar `resources/views/components/starcho-chart.blade.php`. Dependencias: ApexCharts (CDN o npm) y Alpine.js 3. No requiere ninguna clase PHP adicional.

**Módulos que ya lo usan:** `admin/tasks` (3 gráficas: donut, bar, area).

**Regla:** No instanciar `ApexCharts` con JS ad-hoc en vistas. Usar siempre `x-starcho-chart` para mantener tema dark/light automático y paleta Starcho.

---

### Buenas prácticas para mantener escalabilidad

- No duplicar HTML de modales entre módulos; extender desde `starcho-popup-*`.
- No duplicar HTML de tarjetas de estadísticas; usar `starcho-card-app-*` según la skin del módulo.
- No duplicar icono de notificaciones ni toast en layouts; usar `x-starcho-noty` y `x-starcho-alert`.
- Mantener textos en `lang/` y pasar labels por props.
- Mantener eventos Livewire con prefijos consistentes por entidad.
- En acciones de tablas, centralizar en `starcho-crud1` para evitar divergencias visuales.
- Evitar estilos inline nuevos; preferir clases existentes del diseño seleccionado.

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
| `Admin\TasksTable` | /admin | Task |
| `Admin\ContactsTable` | /admin | Contact |
| `Admin\NotesTable` | /admin | Note |
| `Tasks\UserTasksTable` | /app | Task |
| `App\ContactsTable` | /app | Contact |
| `App\NotesTable` | /app | Note |

### Persistencia de columnas visibles (nuevo estándar)

Todas las tablas de negocio en `/app` y `/admin` deben persistir el estado de columnas visibles para evitar que el usuario pierda su preferencia al refrescar.

```php
public function setUp(): array
{
    $this->persist(['columns'], 'app'); // o 'admin'

    return [
        PowerGrid::header()
            ->showSearchInput()
            ->showToggleColumns(),
        PowerGrid::footer()
            ->showPerPage(15)
            ->showRecordCount(),
    ];
}
```

Uso actual aplicado:
- `app/Livewire/App/ContactsTable.php`
- `app/Livewire/App/NotesTable.php`
- `app/Livewire/Tasks/UserTasksTable.php`
- `app/Livewire/Admin/ContactsTable.php`
- `app/Livewire/Admin/NotesTable.php`
- `app/Livewire/Admin/TasksTable.php`

### Botón reutilizable para ver/ocultar columnas

El botón de toggle de columnas ya no se replica como HTML inline. Ahora se centraliza en:

- `resources/views/components/starcho-btn-view-table.blade.php`

Integración en el tema Tailwind de PowerGrid:

- `resources/views/vendor/livewire-powergrid/components/frameworks/tailwind/header/toggle-columns.blade.php`

API del componente:
- `tableName` (requerido): nombre de tabla usado para `data-cy`.
- `title` (opcional): tooltip/aria label. Por defecto usa `__('admin_ui.powergrid.toggle_columns')`.

Ejemplo de uso:

```blade
<x-starcho-btn-view-table
    :table-name="$tableName"
    :title="__('admin_ui.powergrid.toggle_columns')"
/>
```

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

## Workflow profesional para crear módulos (app/admin)

Esta es la forma recomendada para crear módulos productivos y consistentes con el enfoque Starcho.

### 1) Elegir el área y crear estructura base

Módulo para `/app`:

```text
app/Livewire/App/MiModuloTable.php
resources/views/mi-modulo/index.blade.php
resources/views/mi-modulo/pg-header.blade.php
resources/views/livewire/app/mi-modulo-modal.blade.php
routes/app.php
lang/es/mi-modulo.php
lang/en/mi-modulo.php
lang/pt_BR/mi-modulo.php
```

Módulo para `/admin`:

```text
app/Livewire/Admin/MiModuloTable.php
resources/views/admin/mi-modulo/index.blade.php
resources/views/admin/mi-modulo/pg-header.blade.php
resources/views/livewire/admin/mi-modulo-modal.blade.php
routes/admin.php
lang/es/mi-modulo.php
lang/en/mi-modulo.php
lang/pt_BR/mi-modulo.php
```

### 2) Aplicar setup estándar de PowerGrid

Todo módulo nuevo debe incluir:

- Búsqueda (`showSearchInput()`)
- Toggle de columnas (`showToggleColumns()`)
- Persistencia de columnas (`persist(['columns'], 'app'|'admin')`)
- Header propio (`includeViewOnTop('...pg-header')`)

Plantilla base:

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

### 3) Reutilizar componentes, no duplicar HTML

Para mantener consistencia visual y acelerar desarrollo, usa componentes Blade reutilizables:

| Componente | Propósito | Uso típico |
|-----------|-----------|------------|
| `x-starcho-btn-view-table` | Botón de mostrar/ocultar columnas PowerGrid | Header de tabla |
| `x-starcho-crud1` | Acciones CRUD en tabla (edit/delete) | Columna acciones |
| `x-starcho-btn-kick` / `x-starcho-btn-stripe` / `x-starcho-btn-tiktok` | CTA principal por estilo visual | Header de módulo |
| `x-starcho-popup-kick` / `x-starcho-popup-stripe` / `x-starcho-popup-tiktok` | Wrapper de modal reutilizable | Formularios create/edit |
| `x-starcho-card-app-kick` / `x-starcho-card-app-stripe` / `x-starcho-card-app-tiktok` | Tarjetas de métricas por skin visual | Componentes stats en /app |
| `x-starcho-noty` | Icono de notificaciones con dropdown | Topbar app y admin |
| `x-starcho-alert` | Toast/alerta de sistema (evento `notify`) | Layout app y admin |
| `x-starcho-chart` | Gráfica ApexCharts universal (8 tipos) | Stats y dashboards en app y admin |
| `x-starcho-popup-admin-import` | Modal de importación en admin | Módulos administrativos |

Regla: si un bloque UI se repite en 2 o más módulos, se convierte en componente.

### 4) Registrar el módulo en `starcho_modules`

```php
StarchoModule::updateOrCreate(
    ['key' => 'mi-modulo'],
    [
        'name'        => 'Mi Módulo',
        'description' => 'Descripción breve',
        'icon'        => 'puzzle-piece',
        'installed'   => false,
        'active'      => false,
        'config'      => [
            'menu_items' => [
                [
                    'panel'      => 'app', // o 'admin'
                    'section'    => 'App',
                    'name'       => [
                        'es' => 'Mi Módulo',
                        'en' => 'My Module',
                        'pt_BR' => 'Meu Módulo',
                    ],
                    'icon'       => 'star',
                    'route'      => 'app.mi-modulo.index',
                    'sort_order' => 50,
                    'target'     => '_self',
                ],
            ],
        ],
    ]
);
```

### 5) Flujo de instalación/operación

1. Instalar desde `/admin/modules`.
2. Verificar que crea ítems de menú según `config.menu_items`.
3. Confirmar que el módulo aparece en menú dinámico.
4. Probar create/edit/delete + refresco de tabla.
5. Validar persistencia de columnas al recargar.

### 6) Checklist de publicación (app y admin)

1. Rutas registradas en `routes/app.php` o `routes/admin.php`.
2. Tabla PowerGrid funcional con persistencia de columnas.
3. Header de módulo usando componentes reutilizables.
4. Modal create/edit implementado con `x-starcho-popup-*` o variante admin.
5. Acciones de tabla centralizadas (preferiblemente con `HasStarchoCrudActions` + `x-starcho-crud1`).
6. Traducciones en `lang/es`, `lang/en`, `lang/pt_BR`.
7. Build y vistas sin errores (`npm run build`, `php artisan view:cache`).

## Agente galax-starcho

El repositorio incluye un agente especializado para acelerar cambios sobre la arquitectura Starcho:

- Archivo: `.github/agents/galax-starcho.agent.md`
- Enfoque: Laravel 13 + Livewire 4 + PowerGrid + arquitectura modular Starcho
- Fuente de verdad: `README.md` y `MODULES_AND_MENU.md`

### Qué debe hacer este agente

- Priorizar componentes reutilizables antes que HTML duplicado.
- Aplicar persistencia de columnas en PowerGrid segun el panel (`app` o `admin`).
- Respetar la separacion de assets y layouts entre `/app` y `/admin`.
- Registrar modulos con `StarchoModule::updateOrCreate()` y `config.menu_items`.
- Mantener textos de UI en `lang/es`, `lang/en` y `lang/pt_BR`.
- Aplicar la familia `x-starcho-card-app-*` cuando un módulo app exponga métricas o KPIs en cards.

### Flujo esperado cuando crea o modifica modulos

1. Leer `README.md` y `MODULES_AND_MENU.md` antes de proponer arquitectura.
2. Detectar si el cambio pertenece a `/app` o `/admin`.
3. Crear tabla Livewire, vista index, header PowerGrid, modal y rutas.
4. Reutilizar componentes existentes como `x-starcho-btn-view-table`, `x-starcho-crud1` y la familia `x-starcho-popup-*`.
5. Si el módulo app tiene estadísticas, encapsularlas con `x-starcho-card-app-kick`, `x-starcho-card-app-stripe` o `x-starcho-card-app-tiktok` según la skin.
6. Validar menu, traducciones, persistencia de columnas y coherencia visual.

### Criterio profesional para componentes

- Si un bloque UI aparece en 2 o mas modulos, debe convertirse en componente Blade.
- Los componentes deben servir tanto a `/app` como a `/admin` cuando la responsabilidad sea comun.
- El ejemplo actual es `x-starcho-btn-view-table`, centralizado en el tema Tailwind de PowerGrid para evitar duplicacion y mantener consistencia.
- Para `/app`, las métricas visuales por módulo deben componerse con la familia `x-starcho-card-app-*` en lugar de repetir HTML inline en views Livewire.

## Conservar datos del módulo (importante)

Starcho separa **estado del módulo** de **datos de negocio**.

| Acción | Menú | Estado módulo | Datos del módulo |
|-------|------|---------------|------------------|
| `deactivate()` | Se oculta | `installed=true`, `active=false` | Se conservan |
| `activate()` | Se muestra | `installed=true`, `active=true` | Se conservan |
| `uninstall()` | Se eliminan ítems de menú | `installed=false`, `active=false` | Se conservan (no se dropean tablas) |

### Recomendación para producción

1. Usa **Desactivar** para ocultar funcionalidad temporalmente.
2. Usa **Desinstalar** solo para retirar el módulo del menú y del flujo activo.
3. No hagas `dropIfExists(...)` en migraciones de mantenimiento normal.
4. Si necesitas borrar datos, hazlo con comando explícito y respaldos previos.

### Patrón seguro de migraciones

```php
public function down(): void
{
    // Evita pérdidas accidentales de datos en entornos productivos.
    // Si se requiere limpieza real, hacerlo con comando manual controlado.
}
```

### Patrón seguro de seeders

```php
StarchoModule::updateOrCreate(['key' => 'mi-modulo'], [...]);
```

Este patrón conserva configuración existente y evita duplicados al redeploy.

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

# Starcho CRM — Laravel 13 Rapid Starter Kit

> **The ultimate starter kit for Laravel 13 + Livewire 4 + PowerGrid 6.**
> Full admin panel, Tasks module, multi-language, Excel export, roles & permissions — ready to customize and ship.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Reactive UI | Livewire 4 + Flux UI v2 |
| Data tables | PowerGrid 6 (custom `StarchoTheme`) |
| Roles & Permissions | Spatie Laravel Permission v7 |
| Export | Maatwebsite Excel v3 |
| Auth | Laravel Fortify + Livewire starter kit |
| Page routing | Laravel Folio v1 |
| Frontend | Tailwind CSS + Alpine.js |
| Languages | ES · EN · PT-BR |

---

## Features

### Landing page (`/`)
- Built with **Laravel Folio** — file-based routing from `resources/views/pages/`
- Fully responsive dark/light mode with Alpine.js
- Language switcher (ES / EN / PT-BR) — persisted server-side via session
- Auto-detects if user is logged in: replaces Login/Register buttons with **"Go to app"**
- Sections: Hero · Marquee · Features · CRUD demo · Included · Demo · Pricing · Testimonials · CTA · Footer

### App (`/app`)
- Authenticated area for regular users
- **Dashboard** — `GET /app/`
- **My Tasks** — `GET /app/tasks` — personal task management with 6 stat cards, create/edit/delete via popup modal
- Feature-flag controlled: admin can hide Tasks from app sidebar

### Admin panel (`/admin`) — requires `admin` role
| Route | Module |
|---|---|
| `/admin/roles` | Roles CRUD + JSON import/export |
| `/admin/permissions` | Permissions CRUD + JSON import/export |
| `/admin/users` | Users management with role assignment |
| `/admin/tasks` | Task admin dashboard with ApexCharts + Excel export |
| `/admin/cache` | Cache management (clear all, views, routes, config, permissions, optimize) |

### Tasks module
- Admin view: 7 stat cards, 3 ApexCharts (donut by status, bar last 7 days, area last 6 months), full table with filters
- User view: 6 personal stat cards, table filtered to own tasks only
- Popup modals for create/edit (shared between admin and user pages)
- Soft deletes, status & priority enums, due dates, assignment to users
- Excel export via Maatwebsite Excel
- Feature flag (`AppSetting::get('tasks_enabled')`) to toggle Tasks visibility in app sidebar

### Multi-language
- Supported: `es` (Spanish) · `en` (English) · `pt_BR` (Portuguese)
- Session-based via `SetLocale` middleware
- User locale persisted to `users.locale` column
- Switch via `GET /language/{locale}`

---

## Requirements

- PHP 8.3+
- Composer 2
- Node.js 20+
- MySQL 8+ or SQLite

---

## Installation

```bash
# 1. Clone
git clone https://github.com/galax13a/starcho.git
cd starcho

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies & build assets
npm install && npm run build

# 4. Environment
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env, then migrate + seed
php artisan migrate
php artisan db:seed --class=AdminSeeder

# 6. Serve
php artisan serve
```

The seeder creates:
- **Admin user** — `admin@starcho.test` / `password` — role `admin`
- All permissions and roles pre-configured

---

## Route Structure

```
/                       Landing page (Folio — resources/views/pages/index.blade.php)
/language/{locale}      Switch locale (es | en | pt_BR)
/login                  Fortify authentication
/register               Fortify registration

/app                    Dashboard (auth + verified)
/app/tasks              My Tasks (auth + verified)

/admin                  → redirects to /admin/roles
/admin/roles            Roles management
/admin/permissions      Permissions management
/admin/users            Users management
/admin/tasks            Tasks admin dashboard
/admin/tasks/export     Excel export
/admin/cache            Cache management
```

---

## Project Structure

```
app/
├── Exports/
│   └── TasksExport.php          # Maatwebsite Excel export
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── CacheController.php
│   │   │   ├── PermissionController.php
│   │   │   ├── RoleController.php
│   │   │   ├── TaskController.php
│   │   │   └── UserController.php
│   │   └── LanguageController.php
│   └── Middleware/
│       └── SetLocale.php
├── Livewire/
│   ├── Admin/
│   │   ├── PermissionsTable.php
│   │   ├── RolesTable.php
│   │   ├── TasksTable.php       # PowerGrid — admin tasks
│   │   └── UsersTable.php
│   └── Tasks/
│       └── UserTasksTable.php   # PowerGrid — user tasks
├── Models/
│   ├── AppSetting.php           # Feature flags with cache
│   ├── Task.php                 # Tasks model (SoftDeletes)
│   └── User.php
└── PowerGrid/
    └── StarchoTheme.php         # Custom PowerGrid theme

resources/views/
├── pages/
│   └── index.blade.php          # Landing page (Folio)
├── admin/
│   ├── roles/                   # Roles views + PowerGrid header
│   ├── permissions/             # Permissions views + PowerGrid header
│   ├── users/                   # Users views + PowerGrid header
│   ├── tasks/                   # Tasks admin dashboard
│   └── cache/
├── layouts/
│   ├── admin.blade.php          # Admin layout wrapper
│   ├── admin/sidebar.blade.php  # Admin sidebar
│   └── app/sidebar.blade.php    # App sidebar
├── livewire/admin/
│   └── task-modal.blade.php     # Volt modal — create/edit tasks
└── tasks/
    └── index.blade.php          # User tasks page

routes/
├── web.php      # Home + language switch + settings
├── app.php      # /app routes (auth required)
└── admin.php    # /admin routes (admin role required)
```

---

## PowerGrid Custom Theme

The `StarchoTheme` extends PowerGrid's Tailwind theme with:
- Rounded inputs and pagination buttons
- Violet active page highlight
- Zinc border styling
- Inline SVG search icon with magenta clear button
- Consistent `h-8 px-3` toolbar buttons

---

## AppSetting Feature Flags

```php
// Read (cache-backed, 1 hour TTL)
AppSetting::get('tasks_enabled', '1');

// Write (invalidates cache)
AppSetting::set('tasks_enabled', '0');
```

Available flags:
| Key | Default | Description |
|---|---|---|
| `tasks_enabled` | `1` | Show/hide Tasks in app sidebar |

---

## Commands after pulling

```bash
composer install
npm install && npm run build
php artisan migrate
php artisan route:clear
php artisan view:clear
php artisan config:clear
```

---

## License

MIT — free for personal and commercial use.

---

*Built with [live4crud-tailwind](https://packagist.org/packages/galax13a/live4crud-tailwind) · Laravel 13 · Livewire 4 · PowerGrid 6*

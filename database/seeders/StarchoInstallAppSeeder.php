<?php

namespace Database\Seeders;

use App\Models\StarchoMenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder unificado de instalacion de Starcho.
 *
 * Este seeder es autocontenido: embebe los datos del estado actual
 * (app_settings, permissions, roles, role_has_permissions, site_settings,
 * site_languages, site_page_settings, starcho_modules, starcho_menu_sections
 * y starcho_menu_items) directamente como arrays PHP.
 *
 * NO siembra datos de posts, paginas CMS, comentarios ni archivos multimedia.
 * Esas tablas se crean vacias y se pueblan despues desde la administracion.
 */
class StarchoInstallAppSeeder extends Seeder
{
    public function run(): void
    {
        $tables = $this->stateSnapshot();

        DB::transaction(function () use ($tables): void {
            $this->seedAppSettings($tables['app_settings']);
            $this->seedPermissions($tables['permissions']);
            $this->seedRoles($tables['roles']);
            $this->seedRolePermissions(
                $tables['role_has_permissions'],
                $tables['roles'],
                $tables['permissions']
            );
            $this->seedAdminUser();
            $this->seedSiteSettings($tables['site_settings']);
            $this->seedSiteLanguages($tables['site_languages']);
            $this->seedSitePageSettings($tables['site_page_settings']);
            $this->seedModules($tables['starcho_modules']);
            $this->seedMenuSections($tables['starcho_menu_sections']);
            $this->seedMenuItems($tables['starcho_menu_items']);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        StarchoMenuItem::clearMenuCache();

        $this->command?->info('Starcho install app seeder ejecutado correctamente.');
    }

    /**
     * Snapshot del estado actual embebido. Excluye posts, paginas,
     * comentarios y media intencionalmente.
     */
    protected function stateSnapshot(): array
    {
        return [
            'app_settings' => [
        [
            'id' => 1,
            'key' => 'tasks_enabled',
            'value' => '1',
            'created_at' => '2026-03-31 23:55:12',
            'updated_at' => '2026-03-31 23:55:17',
        ],
    ],
            'permissions' => [
        [
            'id' => 1,
            'name' => 'view-roles',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 2,
            'name' => 'create-roles',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 3,
            'name' => 'edit-roles',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 4,
            'name' => 'delete-roles',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 5,
            'name' => 'view-permissions',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 6,
            'name' => 'create-permissions',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 7,
            'name' => 'edit-permissions',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 8,
            'name' => 'delete-permissions',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 9,
            'name' => 'view-users',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:10',
        ],
        [
            'id' => 10,
            'name' => 'edit-users',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:10',
        ],
        [
            'id' => 11,
            'name' => 'manage-cache',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:29',
            'updated_at' => '2026-03-30 19:32:10',
        ],
        [
            'id' => 12,
            'name' => 'create-users',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 18:10:13',
            'updated_at' => '2026-03-30 19:32:10',
        ],
        [
            'id' => 13,
            'name' => 'delete-users',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 18:10:13',
            'updated_at' => '2026-03-30 19:32:10',
        ],
        [
            'id' => 14,
            'name' => 'view-admin',
            'guard_name' => 'web',
            'created_at' => '2026-03-30 19:17:18',
            'updated_at' => '2026-03-30 19:32:09',
        ],
        [
            'id' => 15,
            'name' => 'manage-site',
            'guard_name' => 'web',
            'created_at' => '2026-03-30 19:32:10',
            'updated_at' => '2026-03-30 19:32:10',
        ],
    ],
            'roles' => [
        [
            'id' => 1,
            'name' => 'admin',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 16:15:30',
            'updated_at' => '2026-03-25 16:15:30',
        ],
        [
            'id' => 2,
            'name' => 'editor',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 18:10:14',
            'updated_at' => '2026-03-25 18:10:14',
        ],
        [
            'id' => 3,
            'name' => 'moderator',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 18:10:14',
            'updated_at' => '2026-03-25 18:10:14',
        ],
        [
            'id' => 4,
            'name' => 'user',
            'guard_name' => 'web',
            'created_at' => '2026-03-25 18:10:14',
            'updated_at' => '2026-03-25 18:10:14',
        ],
        [
            'id' => 6,
            'name' => 'box',
            'guard_name' => 'web',
            'created_at' => '2026-03-26 15:08:26',
            'updated_at' => '2026-03-30 19:30:04',
        ],
        [
            'id' => 7,
            'name' => 'guest',
            'guard_name' => 'web',
            'created_at' => '2026-03-29 16:36:41',
            'updated_at' => '2026-03-29 16:36:41',
        ],
        [
            'id' => 8,
            'name' => 'root',
            'guard_name' => 'web',
            'created_at' => '2026-03-30 19:17:18',
            'updated_at' => '2026-03-30 19:17:18',
        ],
    ],
            'role_has_permissions' => [
        [
            'permission_id' => 1,
            'role_id' => 1,
        ],
        [
            'permission_id' => 2,
            'role_id' => 1,
        ],
        [
            'permission_id' => 3,
            'role_id' => 1,
        ],
        [
            'permission_id' => 4,
            'role_id' => 1,
        ],
        [
            'permission_id' => 5,
            'role_id' => 1,
        ],
        [
            'permission_id' => 6,
            'role_id' => 1,
        ],
        [
            'permission_id' => 7,
            'role_id' => 1,
        ],
        [
            'permission_id' => 8,
            'role_id' => 1,
        ],
        [
            'permission_id' => 9,
            'role_id' => 1,
        ],
        [
            'permission_id' => 10,
            'role_id' => 1,
        ],
        [
            'permission_id' => 11,
            'role_id' => 1,
        ],
        [
            'permission_id' => 12,
            'role_id' => 1,
        ],
        [
            'permission_id' => 13,
            'role_id' => 1,
        ],
        [
            'permission_id' => 14,
            'role_id' => 1,
        ],
        [
            'permission_id' => 9,
            'role_id' => 3,
        ],
        [
            'permission_id' => 14,
            'role_id' => 8,
        ],
    ],
            'site_settings' => [
        [
            'id' => 1,
            'site_name' => 'Starcho Starter Kit',
            'app_name' => null,
            'site_tagline' => null,
            'site_description' => 'Starcho CRM – Starter kit para Laravel 13 con Livewire 4, PowerGrid y CRUD automático. Desarrollo rápido de aplicaciones CRM y SaaS.',
            'meta_keywords' => 'Laravel 13, CRM Starter Kit, live4crud-tailwind, PowerGrid, Livewire, Rapid Development',
            'meta_author' => 'Starcho Labs',
            'support_email' => null,
            'business_email' => null,
            'company_name' => null,
            'company_dni' => null,
            'company_country' => null,
            'company_city' => null,
            'support_whatsapp' => null,
            'business_whatsapp' => null,
            'server_timezone' => 'UTC',
            'social_facebook' => null,
            'social_x' => null,
            'social_telegram' => null,
            'social_discord' => null,
            'social_tiktok' => null,
            'social_linkedin' => null,
            'social_instagram' => null,
            'social_youtube' => null,
            'social_pinterest' => null,
            'canonical_url' => 'http://localhost:8000',
            'og_type' => 'website',
            'og_title' => 'Starcho CRM – Laravel 13 Rapid Starter Kit',
            'og_description' => 'Construye CRUDs completos en segundos con live4crud-tailwind.',
            'twitter_card' => 'summary_large_image',
            'twitter_site' => null,
            'twitter_creator' => null,
            'facebook_app_id' => null,
            'theme_color' => '#111827',
            'robots_index' => 1,
            'robots_follow' => 1,
            'home_page_enabled' => 1,
            'public_registration_enabled' => 1,
            'favicon_path' => 'site/TmVt5zTB3CqsnQfgDKz5XdZlCa80aR8UtDZaRqmD.png',
            'og_image_path' => null,
            'created_at' => '2026-03-30 17:37:51',
            'updated_at' => '2026-03-31 04:40:33',
        ],
    ],
            'site_languages' => [
                ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Espanol', 'active' => true, 'sort_order' => 1],
                ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'active' => true, 'sort_order' => 2],
                ['code' => 'pt_BR', 'name' => 'Portuguese (Brazil)', 'native_name' => 'Portugues (Brasil)', 'active' => false, 'sort_order' => 3],
                ['code' => 'fr', 'name' => 'French', 'native_name' => 'Francais', 'active' => false, 'sort_order' => 4],
                ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'active' => false, 'sort_order' => 5],
                ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'active' => false, 'sort_order' => 6],
                ['code' => 'zh_CN', 'name' => 'Chinese (Simplified)', 'native_name' => 'JianTi ZhongWen', 'active' => false, 'sort_order' => 7],
                ['code' => 'ja', 'name' => 'Japanese', 'native_name' => 'Nihongo', 'active' => false, 'sort_order' => 8],
            ],
            'site_page_settings' => [
        [
            'id' => 1,
            'locale' => 'en',
            'path' => '/',
            'title' => 'Starcho CRM – Laravel 13 Rapid Starter Kit',
            'description' => 'Starcho CRM – Starter kit para Laravel 13 con Livewire 4, PowerGrid y CRUD automático. Desarrollo rápido de aplicaciones CRM y SaaS.',
            'meta_keywords' => 'Laravel 13, CRM Starter Kit, live4crud-tailwind, PowerGrid, Livewire, Rapid Development',
            'og_title' => 'Starcho CRM – Laravel 13 Rapid Starter Kit',
            'og_description' => 'Construye CRUDs completos en segundos con live4crud-tailwind.',
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 1,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 2,
            'locale' => 'en',
            'path' => '/auth/confirm-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 3,
            'locale' => 'en',
            'path' => '/auth/forgot-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 4,
            'locale' => 'en',
            'path' => '/auth/login',
            'title' => 'Galax :::',
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 5,
            'locale' => 'en',
            'path' => '/auth/register',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 6,
            'locale' => 'en',
            'path' => '/auth/reset-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 7,
            'locale' => 'en',
            'path' => '/auth/two-factor-challenge',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 8,
            'locale' => 'en',
            'path' => '/auth/verify-email',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 9,
            'locale' => 'es',
            'path' => '/',
            'title' => 'Starcho CRM – Laravel 13 Rapid Starter Kit',
            'description' => 'Starcho CRM – Starter kit para Laravel 13 con Livewire 4, PowerGrid y CRUD automático. Desarrollo rápido de aplicaciones CRM y SaaS.',
            'meta_keywords' => 'Laravel 13, CRM Starter Kit, live4crud-tailwind, PowerGrid, Livewire, Rapid Development',
            'og_title' => 'Starcho CRM – Laravel 13 Rapid Starter Kit',
            'og_description' => 'Construye CRUDs completos en segundos con live4crud-tailwind.',
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 1,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 10,
            'locale' => 'es',
            'path' => '/auth/confirm-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 11,
            'locale' => 'es',
            'path' => '/auth/forgot-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 12,
            'locale' => 'es',
            'path' => '/auth/login',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 13,
            'locale' => 'es',
            'path' => '/auth/register',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 14,
            'locale' => 'es',
            'path' => '/auth/reset-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 15,
            'locale' => 'es',
            'path' => '/auth/two-factor-challenge',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 16,
            'locale' => 'es',
            'path' => '/auth/verify-email',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 17,
            'locale' => 'pt_BR',
            'path' => '/',
            'title' => 'Starcho CRM – Laravel 13 Rapid Starter Kit',
            'description' => 'Starcho CRM – Starter kit para Laravel 13 con Livewire 4, PowerGrid y CRUD automático. Desarrollo rápido de aplicaciones CRM y SaaS.',
            'meta_keywords' => 'Laravel 13, CRM Starter Kit, live4crud-tailwind, PowerGrid, Livewire, Rapid Development',
            'og_title' => 'Starcho CRM – Laravel 13 Rapid Starter Kit',
            'og_description' => 'Construye CRUDs completos en segundos con live4crud-tailwind.',
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 1,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 18,
            'locale' => 'pt_BR',
            'path' => '/auth/confirm-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 19,
            'locale' => 'pt_BR',
            'path' => '/auth/forgot-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 20,
            'locale' => 'pt_BR',
            'path' => '/auth/login',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 21,
            'locale' => 'pt_BR',
            'path' => '/auth/register',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 22,
            'locale' => 'pt_BR',
            'path' => '/auth/reset-password',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 23,
            'locale' => 'pt_BR',
            'path' => '/auth/two-factor-challenge',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
        [
            'id' => 24,
            'locale' => 'pt_BR',
            'path' => '/auth/verify-email',
            'title' => null,
            'description' => null,
            'meta_keywords' => null,
            'og_title' => null,
            'og_description' => null,
            'robots_index' => 1,
            'robots_follow' => 1,
            'active' => 0,
            'created_at' => '2026-03-30 18:09:04',
            'updated_at' => '2026-03-30 18:09:04',
        ],
    ],
            'starcho_modules' => [
        [
            'id' => 1,
            'key' => 'tasks',
            'name' => '{"en": "Tasks", "es": "Tareas"}',
            'description' => '{"en": "Personal and team task management with statuses, priorities and due dates.", "es": "Gestión de tareas personales y de equipo con estados, prioridades y fechas límite."}',
            'icon' => 'clipboard-document-list',
            'installed' => 1,
            'active' => 1,
            'config' => '{"menu_items": [{"icon": "fas fa-clipboard-list", "name": {"en": "My Tasks", "es": "Mis Tareas"}, "panel": "app", "route": "app.tasks.index", "section": null, "sort_order": 20}, {"icon": "fas fa-clipboard-list", "name": {"en": "Tasks", "es": "Tareas"}, "panel": "admin", "route": "admin.tasks.index", "section": "Acceso", "sort_order": 40}]}',
            'created_at' => '2026-03-29 16:36:41',
            'updated_at' => '2026-04-01 18:35:27',
        ],
        [
            'id' => 2,
            'key' => 'contacts',
            'name' => '{"en": "Contacts", "es": "Contactos"}',
            'description' => '{"en": "Basic CRM to manage leads, prospects and customers.", "es": "CRM básico para gestionar leads, prospectos y clientes."}',
            'icon' => 'user-group',
            'installed' => 1,
            'active' => 1,
            'config' => '{"menu_items": [{"icon": "fas fa-address-book", "name": {"en": "Contacts", "es": "Contactos"}, "panel": "app", "route": "app.contacts.index", "section": null, "sort_order": 30}, {"icon": "fas fa-address-book", "name": {"en": "Contacts", "es": "Contactos"}, "panel": "admin", "route": "admin.contacts.index", "section": "Acceso", "sort_order": 50}]}',
            'created_at' => '2026-03-29 16:36:41',
            'updated_at' => '2026-04-01 18:38:00',
        ],
        [
            'id' => 3,
            'key' => 'site',
            'name' => '{"en": "Site", "es": "Sitio", "pt_BR": "Site"}',
            'description' => '{"en": "Manage SEO, favicon and global website metadata.", "es": "Administra SEO, favicon y metadatos globales del sitio web.", "pt_BR": "Gerencie SEO, favicon e metadados globais do site."}',
            'icon' => 'globe',
            'installed' => 1,
            'active' => 1,
            'config' => '{"menu_items": [{"icon": "fas fa-globe", "name": {"en": "Website", "es": "Sitio web", "pt_BR": "Site"}, "panel": "admin", "route": "admin.site.index", "section": "Sistema", "sort_order": 65}], "settings_route": "admin.site.index"}',
            'created_at' => '2026-03-30 17:37:47',
            'updated_at' => '2026-04-01 18:37:49',
        ],
        [
            'id' => 4,
            'key' => 'notes',
            'name' => '{"en": "Notes", "es": "Notas", "pt_BR": "Notas"}',
            'description' => '{"en": "Notes system with colors, filters and stats for app and admin.", "es": "Sistema de notas con colores, filtros y métricas para app y admin.", "pt_BR": "Sistema de notas com cores, filtros e estatísticas para app e admin."}',
            'icon' => 'document-text',
            'installed' => 1,
            'active' => 1,
            'config' => '{"menu_items": [{"icon": "fas fa-note-sticky", "name": {"en": "Notes", "es": "Notas", "pt_BR": "Notas"}, "panel": "app", "route": "app.notes.index", "section": null, "sort_order": 35}, {"icon": "fas fa-note-sticky", "name": {"en": "Notes", "es": "Notas", "pt_BR": "Notas"}, "panel": "admin", "route": "admin.notes.index", "section": "Acceso", "sort_order": 55}], "settings_route": "admin.notes.index"}',
            'created_at' => '2026-03-30 20:03:31',
            'updated_at' => '2026-04-01 18:40:05',
        ],
        [
            'id' => 5,
            'key' => 'starcho-ip',
            'name' => '{"en": "IP Geolocation", "es": "Geolocalización IP"}',
            'description' => '{"en": "Capture and track user IP geolocation on registration", "es": "Capturar y rastrear geolocalización de usuarios al registrarse"}',
            'icon' => 'fas fa-globe',
            'installed' => 1,
            'active' => 1,
            'config' => '{"enabled": false, "provider": "ipquery", "cache_ttl": 86400, "menu_items": [{"icon": "fas fa-globe", "name": {"en": "IP Geolocation", "es": "Geolocalizacion IP", "pt_BR": "Geolocalizacao IP"}, "panel": "admin", "route": "admin.geolocations.index", "section": "Sistema", "sort_order": 66, "parent_route": "admin.site.index"}], "exclude_localhost": true, "exclude_private_ips": true}',
            'created_at' => '2026-04-01 04:59:26',
            'updated_at' => '2026-04-01 18:40:40',
        ],
        [
            'id' => 6,
            'key' => 'users-ban',
            'name' => '{"en": "Ban Users", "es": "Banear Usuarios", "pt_BR": "Banir Usuarios"}',
            'description' => '{"en": "Restrict or block user access for a set time or permanently.", "es": "Restringe o bloquea el acceso de usuarios por tiempo determinado o de forma permanente.", "pt_BR": "Restrinja ou bloqueie o acesso de usuarios por tempo determinado ou permanentemente."}',
            'icon' => 'no-symbol',
            'installed' => 1,
            'active' => 1,
            'config' => '{"menu_items": [{"icon": "fas fa-ban", "name": {"en": "Ban Users", "es": "Banear Usuarios", "pt_BR": "Banir Usuarios"}, "panel": "admin", "route": "admin.users-ban.index", "section": "Sistema", "sort_order": 66, "parent_route": "admin.site.index"}], "settings_route": "admin.users-ban.index"}',
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:37:52',
        ],
    ],
            'starcho_menu_sections' => [
        [
            'id' => 1,
            'panel' => 'admin',
            'label' => 'Acceso',
            'sort_order' => 10,
            'created_at' => '2026-04-01 14:17:55',
            'updated_at' => '2026-04-01 14:17:55',
        ],
        [
            'id' => 2,
            'panel' => 'admin',
            'label' => 'App',
            'sort_order' => 20,
            'created_at' => '2026-04-01 14:17:55',
            'updated_at' => '2026-04-01 14:17:55',
        ],
        [
            'id' => 3,
            'panel' => 'admin',
            'label' => 'Sistema',
            'sort_order' => 30,
            'created_at' => '2026-04-01 14:17:55',
            'updated_at' => '2026-04-01 14:17:55',
        ],
    ],
            'starcho_menu_items' => [
        [
            'id' => 1,
            'panel' => 'app',
            'module_key' => null,
            'parent_id' => null,
            'section' => null,
            'label' => null,
            'name' => '{"en": "Dashboard", "es": "Dashboard"}',
            'icon' => 'fas fa-home',
            'route' => 'app.dashboard',
            'url' => null,
            'target' => '_self',
            'sort_order' => 10,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:35:28',
        ],
        [
            'id' => 2,
            'panel' => 'app',
            'module_key' => 'tasks',
            'parent_id' => null,
            'section' => null,
            'label' => null,
            'name' => '{"en": "My Tasks", "es": "Mis Tareas"}',
            'icon' => 'fas fa-clipboard-list',
            'route' => 'app.tasks.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 20,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:35:28',
        ],
        [
            'id' => 3,
            'panel' => 'app',
            'module_key' => 'contacts',
            'parent_id' => null,
            'section' => null,
            'label' => null,
            'name' => '{"en": "Contacts", "es": "Contactos"}',
            'icon' => 'fas fa-address-book',
            'route' => 'app.contacts.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 30,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:38:00',
        ],
        [
            'id' => 4,
            'panel' => 'app',
            'module_key' => 'notes',
            'parent_id' => null,
            'section' => null,
            'label' => null,
            'name' => '{"en": "Notes", "es": "Notas", "pt_BR": "Notas"}',
            'icon' => 'fas fa-note-sticky',
            'route' => 'app.notes.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 35,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:40:05',
        ],
        [
            'id' => 5,
            'panel' => 'admin',
            'module_key' => null,
            'parent_id' => null,
            'section' => 'Acceso',
            'label' => null,
            'name' => '{"en": "Roles", "es": "Roles"}',
            'icon' => 'fas fa-shield-alt',
            'route' => 'admin.roles.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 10,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:35:28',
        ],
        [
            'id' => 6,
            'panel' => 'admin',
            'module_key' => null,
            'parent_id' => null,
            'section' => 'Acceso',
            'label' => null,
            'name' => '{"en": "Permissions", "es": "Permisos"}',
            'icon' => 'fas fa-key',
            'route' => 'admin.permissions.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 20,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:35:28',
        ],
        [
            'id' => 7,
            'panel' => 'admin',
            'module_key' => null,
            'parent_id' => null,
            'section' => 'Acceso',
            'label' => null,
            'name' => '{"en": "Users", "es": "Usuarios"}',
            'icon' => 'fas fa-users',
            'route' => 'admin.users.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 30,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:35:28',
        ],
        [
            'id' => 8,
            'panel' => 'admin',
            'module_key' => 'tasks',
            'parent_id' => null,
            'section' => 'Acceso',
            'label' => null,
            'name' => '{"en": "Tasks", "es": "Tareas"}',
            'icon' => 'fas fa-clipboard-list',
            'route' => 'admin.tasks.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 40,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:28',
            'updated_at' => '2026-04-01 18:35:28',
        ],
        [
            'id' => 9,
            'panel' => 'admin',
            'module_key' => 'contacts',
            'parent_id' => null,
            'section' => 'Acceso',
            'label' => null,
            'name' => '{"en": "Contacts", "es": "Contactos"}',
            'icon' => 'fas fa-address-book',
            'route' => 'admin.contacts.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 50,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:29',
            'updated_at' => '2026-04-01 18:38:00',
        ],
        [
            'id' => 10,
            'panel' => 'admin',
            'module_key' => 'notes',
            'parent_id' => null,
            'section' => 'Acceso',
            'label' => null,
            'name' => '{"en": "Notes", "es": "Notas", "pt_BR": "Notas"}',
            'icon' => 'fas fa-note-sticky',
            'route' => 'admin.notes.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 55,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:29',
            'updated_at' => '2026-04-01 18:40:05',
        ],
        [
            'id' => 11,
            'panel' => 'admin',
            'module_key' => null,
            'parent_id' => null,
            'section' => 'Sistema',
            'label' => null,
            'name' => '{"en": "Modules", "es": "Módulos"}',
            'icon' => 'fas fa-puzzle-piece',
            'route' => 'admin.modules.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 60,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:29',
            'updated_at' => '2026-04-01 18:35:29',
        ],
        [
            'id' => 12,
            'panel' => 'admin',
            'module_key' => 'site',
            'parent_id' => null,
            'section' => 'Sistema',
            'label' => null,
            'name' => '{"en": "Website", "es": "Sitio web", "pt_BR": "Site"}',
            'icon' => 'fas fa-globe',
            'route' => 'admin.site.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 65,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:29',
            'updated_at' => '2026-04-01 18:37:49',
        ],
        [
            'id' => 13,
            'panel' => 'admin',
            'module_key' => null,
            'parent_id' => null,
            'section' => 'Sistema',
            'label' => null,
            'name' => '{"en": "Side Menu", "es": "Menú lateral"}',
            'icon' => 'fas fa-bars',
            'route' => 'admin.menu.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 70,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:29',
            'updated_at' => '2026-04-01 18:35:29',
        ],
        [
            'id' => 14,
            'panel' => 'admin',
            'module_key' => null,
            'parent_id' => null,
            'section' => 'Sistema',
            'label' => null,
            'name' => '{"en": "Cache", "es": "Caché"}',
            'icon' => 'fas fa-sync-alt',
            'route' => 'admin.cache.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 80,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:29',
            'updated_at' => '2026-04-01 18:35:29',
        ],
        [
            'id' => 15,
            'panel' => 'admin',
            'module_key' => null,
            'parent_id' => null,
            'section' => 'App',
            'label' => null,
            'name' => '{"en": "App Panel", "es": "Panel App"}',
            'icon' => 'fas fa-home',
            'route' => 'app.dashboard',
            'url' => null,
            'target' => '_self',
            'sort_order' => 90,
            'active' => 1,
            'created_at' => '2026-04-01 18:35:29',
            'updated_at' => '2026-04-01 18:35:29',
        ],
        [
            'id' => 16,
            'panel' => 'admin',
            'module_key' => 'users-ban',
            'parent_id' => 12,
            'section' => 'Sistema',
            'label' => null,
            'name' => '{"en": "Ban Users", "es": "Banear Usuarios", "pt_BR": "Banir Usuarios"}',
            'icon' => 'fas fa-ban',
            'route' => 'admin.users-ban.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 66,
            'active' => 1,
            'created_at' => '2026-04-01 18:37:52',
            'updated_at' => '2026-04-01 18:37:52',
        ],
        [
            'id' => 17,
            'panel' => 'admin',
            'module_key' => 'starcho-ip',
            'parent_id' => 12,
            'section' => 'Sistema',
            'label' => null,
            'name' => '{"en": "IP Geolocation", "es": "Geolocalizacion IP", "pt_BR": "Geolocalizacao IP"}',
            'icon' => 'fas fa-globe',
            'route' => 'admin.geolocations.index',
            'url' => null,
            'target' => '_self',
            'sort_order' => 66,
            'active' => 1,
            'created_at' => '2026-04-01 18:40:40',
            'updated_at' => '2026-04-01 18:40:40',
        ],
    ],
        ];
    }

    protected function seedAppSettings(array $rows): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedPermissions(array $rows): void
    {
        foreach ($rows as $row) {
            Permission::findOrCreate($row['name'], $row['guard_name'] ?? 'web');
        }
    }

    protected function seedRoles(array $rows): void
    {
        foreach ($rows as $row) {
            Role::findOrCreate($row['name'], $row['guard_name'] ?? 'web');
        }
    }

    protected function seedRolePermissions(array $pivotRows, array $roleRows, array $permissionRows): void
    {
        $rolesById = collect($roleRows)->mapWithKeys(fn (array $row) => [$row['id'] => $row['name']]);
        $permissionsById = collect($permissionRows)->mapWithKeys(fn (array $row) => [$row['id'] => $row['name']]);
        $permissionMap = [];

        foreach ($pivotRows as $pivotRow) {
            $roleName = $rolesById[$pivotRow['role_id']] ?? null;
            $permissionName = $permissionsById[$pivotRow['permission_id']] ?? null;

            if (! $roleName || ! $permissionName) {
                continue;
            }

            $permissionMap[$roleName] ??= [];
            $permissionMap[$roleName][] = $permissionName;
        }

        foreach ($permissionMap as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions(array_values(array_unique($permissions)));
        }
    }

    protected function seedAdminUser(): void
    {
        $data = [
            'name' => 'Administrador',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'locale' => 'es',
        ];

        foreach (['avatar', 'whatsapp', 'whatsapp_verified_at', 'subscription_level'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $data[$column] = $column === 'subscription_level' ? 'free' : null;
            }
        }

        $user = User::updateOrCreate(
            ['email' => 'admin@starcho.com'],
            $data
        );

        $user->syncRoles(['admin']);

        if (Schema::hasTable('subscriptions') && ! $user->subscriptions()->exists()) {
            $user->subscriptions()->create([
                'level' => $user->subscription_level ?: 'free',
                'is_active' => true,
                'starts_at' => now(),
            ]);
        }
    }

    protected function seedSiteSettings(array $rows): void
    {
        if (! Schema::hasTable('site_settings') || $rows === []) {
            return;
        }

        $row = $rows[0];
        $id = $row['id'] ?? 1;
        $payload = array_merge($row, ['id' => $id]);

        // Filtra solo a columnas que existan en el esquema actual.
        $payload = array_filter(
            $payload,
            fn ($key) => Schema::hasColumn('site_settings', $key),
            ARRAY_FILTER_USE_KEY
        );

        if (Schema::hasColumn('site_settings', 'default_site_locale') && ! isset($payload['default_site_locale'])) {
            $payload['default_site_locale'] = 'es';
        }

        if (Schema::hasColumn('site_settings', 'hide_language_switcher') && ! isset($payload['hide_language_switcher'])) {
            $payload['hide_language_switcher'] = false;
        }

        DB::table('site_settings')->updateOrInsert(
            ['id' => $id],
            $payload
        );
    }

    protected function seedSiteLanguages(array $rows): void
    {
        if (! Schema::hasTable('site_languages')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('site_languages')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'] ?? $row['code'],
                    'native_name' => $row['native_name'] ?? null,
                    'active' => (bool) ($row['active'] ?? false),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedSitePageSettings(array $rows): void
    {
        if (! Schema::hasTable('site_page_settings')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('site_page_settings')->updateOrInsert(
                [
                    'locale' => $row['locale'],
                    'path' => $row['path'],
                ],
                [
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'meta_keywords' => $row['meta_keywords'],
                    'og_title' => $row['og_title'],
                    'og_description' => $row['og_description'],
                    'robots_index' => $row['robots_index'],
                    'robots_follow' => $row['robots_follow'],
                    'active' => $row['active'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedModules(array $rows): void
    {
        if (! Schema::hasTable('starcho_modules')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('starcho_modules')->updateOrInsert(
                ['key' => $row['key']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'icon' => $row['icon'],
                    'installed' => $row['installed'],
                    'active' => $row['active'],
                    'config' => $row['config'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedMenuSections(array $rows): void
    {
        if (! Schema::hasTable('starcho_menu_sections')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('starcho_menu_sections')->updateOrInsert(
                [
                    'panel' => $row['panel'],
                    'label' => $row['label'],
                ],
                [
                    'sort_order' => $row['sort_order'] ?? 0,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedMenuItems(array $rows): void
    {
        if ($rows === [] || ! Schema::hasTable('starcho_menu_items')) {
            return;
        }

        $rowsById = collect($rows)->mapWithKeys(fn (array $row) => [$row['id'] => $row]);

        // Dos pasadas: primero raices (sin parent), despues hijos.
        foreach ([false, true] as $withParent) {
            foreach ($rows as $row) {
                $hasParent = ! empty($row['parent_id']);

                if ($hasParent !== $withParent) {
                    continue;
                }

                $parentId = null;

                if ($hasParent) {
                    $parentRow = $rowsById[$row['parent_id']] ?? null;
                    if (! $parentRow) {
                        continue;
                    }

                    $parentId = DB::table('starcho_menu_items')
                        ->where('panel', $parentRow['panel'])
                        ->where(function ($query) use ($parentRow): void {
                            if (! empty($parentRow['route'])) {
                                $query->where('route', $parentRow['route']);
                            } else {
                                $query->where('url', $parentRow['url']);
                            }
                        })
                        ->value('id');

                    if (! $parentId) {
                        continue;
                    }
                }

                $lookup = ['panel' => $row['panel']];

                if (! empty($row['route'])) {
                    $lookup['route'] = $row['route'];
                } else {
                    $lookup['url'] = $row['url'];
                }

                DB::table('starcho_menu_items')->updateOrInsert(
                    $lookup,
                    [
                        'module_key' => $row['module_key'],
                        'parent_id' => $parentId,
                        'section' => $row['section'],
                        'name' => $row['name'] ?? null,
                        'label' => $row['label'] ?? null,
                        'icon' => $row['icon'],
                        'route' => $row['route'],
                        'url' => $row['url'],
                        'target' => $row['target'] ?? '_self',
                        'sort_order' => $row['sort_order'] ?? 0,
                        'active' => $row['active'] ?? true,
                        'created_at' => $row['created_at'] ?? now(),
                        'updated_at' => $row['updated_at'] ?? now(),
                    ]
                );
            }
        }
    }
}

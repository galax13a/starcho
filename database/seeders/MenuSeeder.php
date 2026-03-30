<?php

namespace Database\Seeders;

use App\Models\StarchoMenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        StarchoMenuItem::truncate();

        $items = [
            // ── APP panel ──────────────────────────────────────────────────
            [
                'panel'      => 'app',
                'module_key' => null,
                'section'    => null,
                'name'       => ['es' => 'Dashboard', 'en' => 'Dashboard'],
                'icon'       => 'fas fa-home',
                'route'      => 'app.dashboard',
                'sort_order' => 10,
                'active'     => true,
            ],
            [
                'panel'      => 'app',
                'module_key' => 'tasks',
                'section'    => null,
                'name'       => ['es' => 'Mis Tareas', 'en' => 'My Tasks'],
                'icon'       => 'fas fa-clipboard-list',
                'route'      => 'app.tasks.index',
                'sort_order' => 20,
                'active'     => true,
            ],
            [
                'panel'      => 'app',
                'module_key' => 'contacts',
                'section'    => null,
                'name'       => ['es' => 'Contactos', 'en' => 'Contacts'],
                'icon'       => 'fas fa-address-book',
                'route'      => 'app.contacts.index',
                'sort_order' => 30,
                'active'     => false,
            ],

            // ── ADMIN panel — sección Acceso ───────────────────────────────
            [
                'panel'      => 'admin',
                'module_key' => null,
                'section'    => 'Acceso',
                'name'       => ['es' => 'Roles', 'en' => 'Roles'],
                'icon'       => 'fas fa-shield-alt',
                'route'      => 'admin.roles.index',
                'sort_order' => 10,
                'active'     => true,
            ],
            [
                'panel'      => 'admin',
                'module_key' => null,
                'section'    => 'Acceso',
                'name'       => ['es' => 'Permisos', 'en' => 'Permissions'],
                'icon'       => 'fas fa-key',
                'route'      => 'admin.permissions.index',
                'sort_order' => 20,
                'active'     => true,
            ],
            [
                'panel'      => 'admin',
                'module_key' => null,
                'section'    => 'Acceso',
                'name'       => ['es' => 'Usuarios', 'en' => 'Users'],
                'icon'       => 'fas fa-users',
                'route'      => 'admin.users.index',
                'sort_order' => 30,
                'active'     => true,
            ],
            [
                'panel'      => 'admin',
                'module_key' => 'tasks',
                'section'    => 'Acceso',
                'name'       => ['es' => 'Tareas', 'en' => 'Tasks'],
                'icon'       => 'fas fa-clipboard-list',
                'route'      => 'admin.tasks.index',
                'sort_order' => 40,
                'active'     => true,
            ],
            [
                'panel'      => 'admin',
                'module_key' => 'contacts',
                'section'    => 'Acceso',
                'name'       => ['es' => 'Contactos', 'en' => 'Contacts'],
                'icon'       => 'fas fa-address-book',
                'route'      => 'admin.contacts.index',
                'sort_order' => 50,
                'active'     => false,
            ],

            // ── ADMIN panel — sección Sistema ──────────────────────────────
            [
                'panel'      => 'admin',
                'module_key' => null,
                'section'    => 'Sistema',
                'name'       => ['es' => 'Módulos', 'en' => 'Modules'],
                'icon'       => 'fas fa-puzzle-piece',
                'route'      => 'admin.modules.index',
                'sort_order' => 60,
                'active'     => true,
            ],
            [
                'panel'      => 'admin',
                'module_key' => 'site',
                'section'    => 'Sistema',
                'name'       => ['es' => 'Sitio web', 'en' => 'Website', 'pt_BR' => 'Site'],
                'icon'       => 'fas fa-globe',
                'route'      => 'admin.site.index',
                'sort_order' => 65,
                'active'     => false,
            ],
            [
                'panel'      => 'admin',
                'module_key' => null,
                'section'    => 'Sistema',
                'name'       => ['es' => 'Menú lateral', 'en' => 'Side Menu'],
                'icon'       => 'fas fa-bars',
                'route'      => 'admin.menu.index',
                'sort_order' => 70,
                'active'     => true,
            ],
            [
                'panel'      => 'admin',
                'module_key' => null,
                'section'    => 'Sistema',
                'name'       => ['es' => 'Caché', 'en' => 'Cache'],
                'icon'       => 'fas fa-sync-alt',
                'route'      => 'admin.cache.index',
                'sort_order' => 80,
                'active'     => true,
            ],

            // ── ADMIN panel — sección App ──────────────────────────────────
            [
                'panel'      => 'admin',
                'module_key' => null,
                'section'    => 'App',
                'name'       => ['es' => 'Panel App', 'en' => 'App Panel'],
                'icon'       => 'fas fa-home',
                'route'      => 'app.dashboard',
                'sort_order' => 90,
                'active'     => true,
            ],
        ];

        foreach ($items as $data) {
            $item = new StarchoMenuItem([
                'panel'      => $data['panel'],
                'module_key' => $data['module_key'],
                'section'    => $data['section'],
                'icon'       => $data['icon'],
                'route'      => $data['route'] ?? null,
                'url'        => $data['url'] ?? null,
                'sort_order' => $data['sort_order'],
                'active'     => $data['active'],
                'target'     => '_self',
            ]);

            foreach ($data['name'] as $locale => $translation) {
                $item->setTranslation('name', $locale, $translation);
            }

            $item->save();
        }

        StarchoMenuItem::clearMenuCache();

        $this->command->info('Menu seeded: ' . StarchoMenuItem::count() . ' items (app + admin)');
    }
}

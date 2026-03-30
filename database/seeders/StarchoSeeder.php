<?php

namespace Database\Seeders;

use App\Models\StarchoModule;
use Illuminate\Database\Seeder;

class StarchoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Register available modules ──────────────────────────────────────────
        $modules = [
            [
                'key'         => 'tasks',
                'name'        => ['es' => 'Tareas', 'en' => 'Tasks'],
                'description' => ['es' => 'Gestión de tareas personales y de equipo con estados, prioridades y fechas límite.', 'en' => 'Personal and team task management with statuses, priorities and due dates.'],
                'icon'        => 'clipboard-document-list',
                'installed'   => true,
                'active'      => true,
                'config'      => [
                    'menu_items' => [
                        [
                            'panel'      => 'app',
                            'section'    => null,
                            'name'       => ['es' => 'Mis Tareas', 'en' => 'My Tasks'],
                            'icon'       => 'fas fa-clipboard-list',
                            'route'      => 'app.tasks.index',
                            'sort_order' => 20,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Acceso',
                            'name'       => ['es' => 'Tareas', 'en' => 'Tasks'],
                            'icon'       => 'fas fa-clipboard-list',
                            'route'      => 'admin.tasks.index',
                            'sort_order' => 40,
                        ],
                    ],
                ],
            ],
            [
                'key'         => 'contacts',
                'name'        => ['es' => 'Contactos', 'en' => 'Contacts'],
                'description' => ['es' => 'CRM básico para gestionar leads, prospectos y clientes.', 'en' => 'Basic CRM to manage leads, prospects and customers.'],
                'icon'        => 'user-group',
                'installed'   => false,
                'active'      => false,
                'config'      => [
                    'menu_items' => [
                        [
                            'panel'      => 'app',
                            'section'    => null,
                            'name'       => ['es' => 'Contactos', 'en' => 'Contacts'],
                            'icon'       => 'fas fa-address-book',
                            'route'      => 'app.contacts.index',
                            'sort_order' => 30,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Acceso',
                            'name'       => ['es' => 'Contactos', 'en' => 'Contacts'],
                            'icon'       => 'fas fa-address-book',
                            'route'      => 'admin.contacts.index',
                            'sort_order' => 50,
                        ],
                    ],
                ],
            ],
            [
                'key'         => 'site',
                'name'        => ['es' => 'Sitio', 'en' => 'Site', 'pt_BR' => 'Site'],
                'description' => ['es' => 'Administra SEO, favicon y metadatos globales del sitio web.', 'en' => 'Manage SEO, favicon and global website metadata.', 'pt_BR' => 'Gerencie SEO, favicon e metadados globais do site.'],
                'icon'        => 'globe',
                'installed'   => false,
                'active'      => false,
                'config'      => [
                    'settings_route' => 'admin.site.index',
                    'menu_items' => [
                        [
                            'panel'      => 'admin',
                            'section'    => 'Sistema',
                            'name'       => ['es' => 'Sitio web', 'en' => 'Website', 'pt_BR' => 'Site'],
                            'icon'       => 'fas fa-globe',
                            'route'      => 'admin.site.index',
                            'sort_order' => 65,
                        ],
                    ],
                ],
            ],
            [
                'key'         => 'notes',
                'name'        => ['es' => 'Notas', 'en' => 'Notes', 'pt_BR' => 'Notas'],
                'description' => ['es' => 'Sistema de notas con colores, filtros y métricas para app y admin.', 'en' => 'Notes system with colors, filters and stats for app and admin.', 'pt_BR' => 'Sistema de notas com cores, filtros e estatísticas para app e admin.'],
                'icon'        => 'document-text',
                'installed'   => false,
                'active'      => false,
                'config'      => [
                    'settings_route' => 'admin.notes.index',
                    'menu_items' => [
                        [
                            'panel'      => 'app',
                            'section'    => null,
                            'name'       => ['es' => 'Notas', 'en' => 'Notes', 'pt_BR' => 'Notas'],
                            'icon'       => 'fas fa-note-sticky',
                            'route'      => 'app.notes.index',
                            'sort_order' => 35,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Acceso',
                            'name'       => ['es' => 'Notas', 'en' => 'Notes', 'pt_BR' => 'Notas'],
                            'icon'       => 'fas fa-note-sticky',
                            'route'      => 'admin.notes.index',
                            'sort_order' => 55,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($modules as $data) {
            StarchoModule::updateOrCreate(['key' => $data['key']], $data);
        }

        $this->call(MenuSeeder::class);

        $this->command->info('Starcho modules seeded.');
    }
}

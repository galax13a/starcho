<?php

namespace Database\Seeders;

use App\Models\StarchoMenuItem;
use App\Models\StarchoModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class StarchoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Register available modules ──────────────────────────────────────────
        $modules = [
            [
                'key'         => 'tasks',
                'name'        => 'Tareas',
                'description' => 'Gestión de tareas personales y de equipo con estados, prioridades y fechas límite.',
                'icon'        => 'clipboard-document-list',
                'installed'   => true,
                'active'      => true,
                'config'      => [
                    'menu_items' => [
                        [
                            'name' => [
                                'es' => 'Mis Tareas',
                                'en' => 'My Tasks',
                            ],
                            'icon' => 'clipboard-document-list',
                            'route' => 'app.tasks.index',
                            'sort_order' => 20,
                        ],
                    ],
                ],
            ],
            [
                'key'         => 'contacts',
                'name'        => 'Contactos',
                'description' => 'CRM básico para gestionar leads, prospectos y clientes.',
                'icon'        => 'user-group',
                'installed'   => false,
                'active'      => false,
                'config'      => [
                    'menu_items' => [
                        [
                            'name' => [
                                'es' => 'Contactos',
                                'en' => 'Contacts',
                            ],
                            'icon' => 'user-group',
                            'route' => 'app.contacts.index',
                            'sort_order' => 30,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($modules as $data) {
            StarchoModule::updateOrCreate(['key' => $data['key']], $data);
        }

        // ── Seed core menu items (no module_key) ─────────────────────────────
        StarchoMenuItem::updateOrCreate(
            ['route' => 'app.dashboard', 'module_key' => null],
            [
                'name'      => ['en' => 'Dashboard', 'es' => 'Dashboard'],
                'label'     => 'Dashboard',
                'icon'       => 'home',
                'sort_order' => 10,
                'active'     => true,
            ]
        );

        // ── Activate menu items for installed modules ────────────────────────
        $installedModules = StarchoModule::where('installed', true)->get();
        foreach ($installedModules as $module) {
            $module->createMenuItems();
        }

        Cache::forget('starcho_menu_items');
        Cache::forget('starcho_menu_items_ids');

        $this->command->info('Starcho modules and menu items seeded.');
    }
}


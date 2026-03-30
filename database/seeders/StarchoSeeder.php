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
        ];

        foreach ($modules as $data) {
            StarchoModule::updateOrCreate(['key' => $data['key']], $data);
        }

        $this->call(MenuSeeder::class);

        $this->command->info('Starcho modules seeded.');
    }
}

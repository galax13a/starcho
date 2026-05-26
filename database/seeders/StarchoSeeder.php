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
            [
                'key'         => 'posts',
                'name'        => ['es' => 'Blog & Páginas', 'en' => 'Blog & Pages', 'pt_BR' => 'Blog & Páginas'],
                'description' => ['es' => 'Editor de posts y páginas del sitio con SEO, categorías, etiquetas, autor e imagen destacada.', 'en' => 'Blog posts and site pages editor with SEO, categories, tags, author and featured image.', 'pt_BR' => 'Editor de posts e páginas do site com SEO, categorias, tags, autor e imagem destacada.'],
                'icon'        => 'newspaper',
                'installed'   => false,
                'active'      => false,
                'config'      => [
                    'menu_items' => [
                        [
                            'panel'      => 'admin',
                            'section'    => 'Contenido',
                            'name'       => ['es' => 'Posts', 'en' => 'Posts', 'pt_BR' => 'Posts'],
                            'icon'       => 'fas fa-newspaper',
                            'route'      => 'admin.posts.index',
                            'sort_order' => 60,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Contenido',
                            'name'       => ['es' => 'Páginas', 'en' => 'Pages', 'pt_BR' => 'Páginas'],
                            'icon'       => 'fas fa-file-alt',
                            'route'      => 'admin.pages.index',
                            'sort_order' => 61,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Contenido',
                            'name'       => ['es' => 'Categorías', 'en' => 'Categories', 'pt_BR' => 'Categorias'],
                            'icon'       => 'fas fa-folder-open',
                            'route'      => 'admin.post-categories.index',
                            'sort_order' => 62,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Contenido',
                            'name'       => ['es' => 'Etiquetas', 'en' => 'Tags', 'pt_BR' => 'Etiquetas'],
                            'icon'       => 'fas fa-tags',
                            'route'      => 'admin.post-tags.index',
                            'sort_order' => 63,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Contenido',
                            'name'       => ['es' => 'Multimedia', 'en' => 'Media Gallery', 'pt_BR' => 'Multimídia'],
                            'icon'       => 'fas fa-photo-film',
                            'route'      => 'admin.media.index',
                            'sort_order' => 64,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Contenido',
                            'name'       => ['es' => 'Configuración', 'en' => 'Content Settings', 'pt_BR' => 'Configurações'],
                            'icon'       => 'fas fa-sliders',
                            'route'      => 'admin.content.settings',
                            'sort_order' => 65,
                        ],
                        [
                            'panel'      => 'admin',
                            'section'    => 'Contenido',
                            'name'       => ['es' => 'Links Rotos', 'en' => 'Broken Links', 'pt_BR' => 'Links Quebrados'],
                            'icon'       => 'fas fa-link-slash',
                            'route'      => 'admin.content.broken-links',
                            'sort_order' => 66,
                        ],
                    ],
                ],
            ],
            [
                'key'         => 'ip-geolocation',
                'name'        => ['es' => 'Geolocalización IP', 'en' => 'IP Geolocation', 'pt_BR' => 'Geolocalização IP'],
                'description' => ['es' => 'Consulta y registra la ubicación geográfica de las IPs que visitan el sitio.', 'en' => 'Query and log the geographic location of IPs visiting the site.', 'pt_BR' => 'Consulta e registra a localização geográfica dos IPs que visitam o site.'],
                'icon'        => 'map-pin',
                'installed'   => true,
                'active'      => true,
                'config'      => [
                    'menu_items' => [
                        [
                            'panel'        => 'admin',
                            'section'      => 'Sistema',
                            'parent_route' => 'admin.site.index',
                            'name'         => ['es' => 'Geolocalización IP', 'en' => 'IP Geolocation', 'pt_BR' => 'Geolocalização IP'],
                            'icon'         => 'fas fa-map-marker-alt',
                            'route'        => 'admin.geolocations.index',
                            'sort_order'   => 67,
                        ],
                    ],
                ],
            ],
            [
                'key'         => 'users-ban',
                'name'        => ['es' => 'Banear Usuarios', 'en' => 'Ban Users', 'pt_BR' => 'Banir Usuarios'],
                'description' => ['es' => 'Restringe o bloquea el acceso de usuarios por tiempo determinado o de forma permanente.', 'en' => 'Restrict or block user access for a set time or permanently.', 'pt_BR' => 'Restrinja ou bloqueie o acesso de usuarios por tempo determinado ou permanentemente.'],
                'icon'        => 'no-symbol',
                'installed'   => false,
                'active'      => false,
                'config'      => [
                    'settings_route' => 'admin.users-ban.index',
                    'menu_items' => [
                        [
                            'panel'        => 'admin',
                            'section'      => 'Sistema',
                            'parent_route' => 'admin.site.index',
                            'name'         => ['es' => 'Banear Usuarios', 'en' => 'Ban Users', 'pt_BR' => 'Banir Usuarios'],
                            'icon'         => 'fas fa-ban',
                            'route'        => 'admin.users-ban.index',
                            'sort_order'   => 66,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($modules as $data) {
            StarchoModule::updateOrCreate(['key' => $data['key']], $data);
        }

        $this->call(MenuSeeder::class);
        $this->call(StoragePlansSeeder::class);

        $this->command->info('Starcho modules seeded.');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $websiteParentId = DB::table('starcho_menu_items')
            ->where('panel', 'admin')
            ->where('route', 'admin.site.index')
            ->value('id');

        // Update module config so future install/activate keeps this nested under Website.
        $module = DB::table('starcho_modules')->where('key', 'starcho-ip')->first();

        if ($module) {
            $config = [];

            if (!empty($module->config)) {
                $decoded = json_decode((string) $module->config, true);
                if (is_array($decoded)) {
                    $config = $decoded;
                }
            }

            $config['menu_items'] = [
                [
                    'panel'       => 'admin',
                    'section'     => 'Sistema',
                    'name'        => [
                        'es' => 'Geolocalizacion IP',
                        'en' => 'IP Geolocation',
                        'pt_BR' => 'Geolocalizacao IP',
                    ],
                    'icon'        => 'fas fa-globe',
                    'route'       => 'admin.geolocations.index',
                    'parent_route'=> 'admin.site.index',
                    'sort_order'  => 66,
                ],
            ];

            DB::table('starcho_modules')
                ->where('key', 'starcho-ip')
                ->update([
                    'config' => json_encode($config, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
        }

        $menuItem = DB::table('starcho_menu_items')
            ->where('panel', 'admin')
            ->where('module_key', 'starcho-ip')
            ->where('route', 'admin.geolocations.index')
            ->first();

        if ($menuItem) {
            DB::table('starcho_menu_items')
                ->where('id', $menuItem->id)
                ->update([
                    'parent_id' => $websiteParentId,
                    'section' => 'Sistema',
                    'sort_order' => 66,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('starcho_menu_items')->insert([
                'panel' => 'admin',
                'module_key' => 'starcho-ip',
                'parent_id' => $websiteParentId,
                'section' => 'Sistema',
                'name' => json_encode([
                    'es' => 'Geolocalizacion IP',
                    'en' => 'IP Geolocation',
                    'pt_BR' => 'Geolocalizacao IP',
                ], JSON_UNESCAPED_UNICODE),
                'icon' => 'fas fa-globe',
                'route' => 'admin.geolocations.index',
                'target' => '_self',
                'sort_order' => 66,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        \App\Models\StarchoMenuItem::clearMenuCache();
    }

    public function down(): void
    {
        $menuItem = DB::table('starcho_menu_items')
            ->where('panel', 'admin')
            ->where('module_key', 'starcho-ip')
            ->where('route', 'admin.geolocations.index')
            ->first();

        if ($menuItem) {
            DB::table('starcho_menu_items')
                ->where('id', $menuItem->id)
                ->update([
                    'parent_id' => null,
                    'sort_order' => 8,
                    'updated_at' => now(),
                ]);
        }

        \App\Models\StarchoMenuItem::clearMenuCache();
    }
};

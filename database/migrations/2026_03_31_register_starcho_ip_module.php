<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar módulo starcho-ip si no existe
        DB::table('starcho_modules')->updateOrInsert(
            ['key' => 'starcho-ip'],
            [
                'name' => json_encode(['es' => 'Geolocalización IP', 'en' => 'IP Geolocation'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode(['es' => 'Capturar y rastrear geolocalización de usuarios al registrarse', 'en' => 'Capture and track user IP geolocation on registration'], JSON_UNESCAPED_UNICODE),
                'icon' => 'fas fa-globe',
                'installed' => true,
                'active' => false,
                'config' => json_encode([
                    'enabled' => false,
                    'provider' => 'ipquery',
                    'cache_ttl' => 86400,
                    'exclude_localhost' => true,
                    'exclude_private_ips' => true,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Insertar menú item si no existe
        DB::table('starcho_menu_items')->updateOrInsert(
            ['label' => 'IP Geolocation', 'panel' => 'admin'],
            [
                'module_key' => 'starcho-ip',
                'panel' => 'admin',
                'route' => 'admin.geolocations.index',
                'icon' => 'fas fa-globe',
                'parent_id' => null,
                'sort_order' => 8,
                'active' => true,
                'target' => '_self',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('starcho_menu_items')->where('module_key', 'starcho-ip')->delete();
        DB::table('starcho_modules')->where('key', 'starcho-ip')->delete();
    }
};

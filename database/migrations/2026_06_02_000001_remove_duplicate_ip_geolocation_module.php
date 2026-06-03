<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes the duplicate "IP Geolocation" module.
 *
 * Two module rows existed for the same feature:
 *   - "starcho-ip"      → the real one, referenced by UserObserver, CaptureGeoIPJob,
 *                          config/starcho_ip.php and GeoLocationsController.
 *   - "ip-geolocation"  → a stray duplicate seeded only by StarchoSeeder, not used by any code.
 *
 * This drops the stray "ip-geolocation" module and its menu items.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('starcho_menu_items')->where('module_key', 'ip-geolocation')->delete();
        DB::table('starcho_modules')->where('key', 'ip-geolocation')->delete();
    }

    public function down(): void
    {
        // Intentionally irreversible: the duplicate should not be recreated.
    }
};

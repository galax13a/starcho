<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds an "AI" menu item nested under the Website (admin.site.index) menu entry,
 * mirroring how IP Geolocation is nested.
 */
return new class extends Migration
{
    public function up(): void
    {
        $websiteParentId = DB::table('starcho_menu_items')
            ->where('panel', 'admin')
            ->where('route', 'admin.site.index')
            ->value('id');

        $exists = DB::table('starcho_menu_items')
            ->where('panel', 'admin')
            ->where('route', 'admin.ai.index')
            ->exists();

        if (! $exists) {
            DB::table('starcho_menu_items')->insert([
                'panel'      => 'admin',
                'module_key' => null,
                'parent_id'  => $websiteParentId,
                'section'    => 'Sistema',
                'name'       => json_encode(['es' => 'IA', 'en' => 'AI', 'pt_BR' => 'IA'], JSON_UNESCAPED_UNICODE),
                'icon'       => 'fas fa-robot',
                'route'      => 'admin.ai.index',
                'target'     => '_self',
                'sort_order' => 64,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (class_exists(\App\Models\StarchoMenuItem::class)) {
            \App\Models\StarchoMenuItem::clearMenuCache();
        }
    }

    public function down(): void
    {
        DB::table('starcho_menu_items')
            ->where('panel', 'admin')
            ->where('route', 'admin.ai.index')
            ->delete();

        if (class_exists(\App\Models\StarchoMenuItem::class)) {
            \App\Models\StarchoMenuItem::clearMenuCache();
        }
    }
};

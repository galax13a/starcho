<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('starcho_modules', 'starcho_modules_state_index')) {
            Schema::table('starcho_modules', function (Blueprint $table): void {
                $table->index(['installed', 'active'], 'starcho_modules_state_index');
            });
        }

        if (! Schema::hasIndex('starcho_menu_items', 'starcho_menu_items_tree_index')) {
            Schema::table('starcho_menu_items', function (Blueprint $table): void {
                $table->index(['panel', 'parent_id', 'active', 'sort_order'], 'starcho_menu_items_tree_index');
            });
        }

        if (! Schema::hasIndex('starcho_menu_items', 'starcho_menu_items_module_index')) {
            Schema::table('starcho_menu_items', function (Blueprint $table): void {
                $table->index(['module_key', 'route'], 'starcho_menu_items_module_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('starcho_menu_items', 'starcho_menu_items_module_index')) {
            Schema::table('starcho_menu_items', function (Blueprint $table): void {
                $table->dropIndex('starcho_menu_items_module_index');
            });
        }

        if (Schema::hasIndex('starcho_menu_items', 'starcho_menu_items_tree_index')) {
            Schema::table('starcho_menu_items', function (Blueprint $table): void {
                $table->dropIndex('starcho_menu_items_tree_index');
            });
        }

        if (Schema::hasIndex('starcho_modules', 'starcho_modules_state_index')) {
            Schema::table('starcho_modules', function (Blueprint $table): void {
                $table->dropIndex('starcho_modules_state_index');
            });
        }
    }
};

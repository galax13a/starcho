<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('starcho_menu_items', 'name')) {
            Schema::table('starcho_menu_items', function (Blueprint $table) {
                $table->json('name')->nullable()->after('label');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('starcho_menu_items', 'name')) {
            Schema::table('starcho_menu_items', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
};

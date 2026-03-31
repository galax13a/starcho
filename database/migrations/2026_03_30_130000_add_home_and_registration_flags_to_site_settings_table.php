<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('site_settings', 'home_page_enabled')) {
                $table->boolean('home_page_enabled')->default(true)->after('robots_follow');
            }

            if (!Schema::hasColumn('site_settings', 'public_registration_enabled')) {
                $table->boolean('public_registration_enabled')->default(true)->after('home_page_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            if (Schema::hasColumn('site_settings', 'public_registration_enabled')) {
                $table->dropColumn('public_registration_enabled');
            }

            if (Schema::hasColumn('site_settings', 'home_page_enabled')) {
                $table->dropColumn('home_page_enabled');
            }
        });
    }
};

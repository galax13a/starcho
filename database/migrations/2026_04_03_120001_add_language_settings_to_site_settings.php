<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'hide_language_switcher')) {
                $table->boolean('hide_language_switcher')->default(false)->after('dark_mode_enabled');
            }

            if (! Schema::hasColumn('site_settings', 'default_site_locale')) {
                $table->string('default_site_locale', 20)->default('es')->after('hide_language_switcher');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumnIfExists('hide_language_switcher');
            $table->dropColumnIfExists('default_site_locale');
        });
    }
};

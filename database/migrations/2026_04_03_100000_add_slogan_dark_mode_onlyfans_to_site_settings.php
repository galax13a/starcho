<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'slogan')) {
                $table->string('slogan')->nullable()->after('app_name');
            }
            if (! Schema::hasColumn('site_settings', 'dark_mode_enabled')) {
                $table->boolean('dark_mode_enabled')->default(false)->after('public_registration_enabled');
            }
            if (! Schema::hasColumn('site_settings', 'social_onlyfans')) {
                $table->string('social_onlyfans')->nullable()->after('social_pinterest');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumnIfExists('slogan');
            $table->dropColumnIfExists('dark_mode_enabled');
            $table->dropColumnIfExists('social_onlyfans');
        });
    }
};

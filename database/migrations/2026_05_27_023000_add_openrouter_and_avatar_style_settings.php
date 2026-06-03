<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('ai_settings', 'openrouter_api_key')) {
                $table->text('openrouter_api_key')->nullable()->after('anthropic_api_key');
            }

            if (! Schema::hasColumn('ai_settings', 'model_settings')) {
                $table->json('model_settings')->nullable()->after('default_model');
            }
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'avatar_style')) {
                $table->string('avatar_style', 20)->default('image')->after('default_site_locale');
            }

            if (! Schema::hasColumn('site_settings', 'profile_avatar_upload_enabled')) {
                $table->boolean('profile_avatar_upload_enabled')->default(true)->after('avatar_style');
            }

            if (! Schema::hasColumn('site_settings', 'avatar_service_url')) {
                $table->string('avatar_service_url', 500)->nullable()->after('profile_avatar_upload_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table): void {
            foreach (['openrouter_api_key', 'model_settings'] as $column) {
                if (Schema::hasColumn('ai_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            foreach (['avatar_style', 'profile_avatar_upload_enabled', 'avatar_service_url'] as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

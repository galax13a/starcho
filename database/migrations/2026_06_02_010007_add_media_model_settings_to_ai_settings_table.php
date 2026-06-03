<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores the image/video model catalogs in the DB (like model_settings does for
 * text), so models can be added/removed/toggled per provider from the panel.
 *
 * Shape (same as model_settings):
 *   { "openai": [ {"id": "gpt-image-1", "active": true}, ... ], "fal": [...] }
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('ai_settings', 'image_model_settings')) {
                $table->json('image_model_settings')->nullable()->after('video_model');
            }
            if (! Schema::hasColumn('ai_settings', 'video_model_settings')) {
                $table->json('video_model_settings')->nullable()->after('image_model_settings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn(['image_model_settings', 'video_model_settings']);
        });
    }
};

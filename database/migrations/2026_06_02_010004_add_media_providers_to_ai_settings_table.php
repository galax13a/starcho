<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            // fal.ai key for video generation (encrypted via model cast)
            $table->text('fal_api_key')->nullable()->after('openrouter_api_key');

            $table->string('image_provider', 40)->default('openai')->after('default_model');
            $table->string('image_model', 180)->default('gpt-image-1')->after('image_provider');
            $table->string('video_provider', 40)->default('fal')->after('image_model');
            $table->string('video_model', 180)
                ->default('fal-ai/kling-video/v1/standard/text-to-video')
                ->after('video_provider');
        });
    }

    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $table->dropColumn(['fal_api_key', 'image_provider', 'image_model', 'video_provider', 'video_model']);
        });
    }
};

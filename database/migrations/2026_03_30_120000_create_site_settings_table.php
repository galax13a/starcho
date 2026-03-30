<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('Starcho');
            $table->string('site_tagline')->nullable();
            $table->text('site_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_author')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_type', 40)->default('website');
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('twitter_card', 40)->default('summary_large_image');
            $table->string('twitter_site', 120)->nullable();
            $table->string('twitter_creator', 120)->nullable();
            $table->string('facebook_app_id', 120)->nullable();
            $table->string('theme_color', 20)->default('#111827');
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('favicon_path')->nullable();
            $table->string('og_image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

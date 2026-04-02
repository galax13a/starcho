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
            $table->string('app_name')->nullable();
            $table->string('site_tagline')->nullable();
            $table->text('site_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_author')->nullable();
            $table->string('support_email')->nullable();
            $table->string('business_email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_dni')->nullable();
            $table->string('company_country')->nullable();
            $table->string('company_city')->nullable();
            $table->string('support_whatsapp')->nullable();
            $table->string('business_whatsapp')->nullable();
            $table->string('server_timezone')->nullable()->default('UTC');
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
            $table->boolean('home_page_enabled')->default(true);
            $table->boolean('public_registration_enabled')->default(true);
            $table->string('favicon_path')->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('social_facebook')->nullable();
            $table->string('social_x')->nullable();
            $table->string('social_telegram')->nullable();
            $table->string('social_discord')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_youtube')->nullable();
            $table->string('social_pinterest')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

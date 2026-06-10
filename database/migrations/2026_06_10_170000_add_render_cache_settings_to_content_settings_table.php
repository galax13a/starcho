<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_settings', function (Blueprint $table): void {
            $table->boolean('render_cache_enabled')->default(false)->after('sitemap_excluded_urls');
            $table->boolean('render_cache_posts_enabled')->default(true)->after('render_cache_enabled');
            $table->boolean('render_cache_pages_enabled')->default(true)->after('render_cache_posts_enabled');
            $table->boolean('render_cache_guest_only')->default(true)->after('render_cache_pages_enabled');
            $table->boolean('render_cache_per_locale')->default(true)->after('render_cache_guest_only');
            $table->unsignedInteger('render_cache_ttl_minutes')->default(60)->after('render_cache_per_locale');
            $table->string('render_cache_strategy', 40)->default('balanced')->after('render_cache_ttl_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('content_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'render_cache_enabled',
                'render_cache_posts_enabled',
                'render_cache_pages_enabled',
                'render_cache_guest_only',
                'render_cache_per_locale',
                'render_cache_ttl_minutes',
                'render_cache_strategy',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('content_settings')) {
            return;
        }

        $now = now();
        $defaults = [
            'render_cache_enabled' => false,
            'render_cache_posts_enabled' => true,
            'render_cache_pages_enabled' => true,
            'render_cache_guest_only' => true,
            'render_cache_per_locale' => true,
            'render_cache_ttl_minutes' => 60,
            'render_cache_strategy' => 'balanced',
            'updated_at' => $now,
        ];

        $defaults = $this->filterColumns('content_settings', $defaults);

        if (DB::table('content_settings')->where('id', 1)->exists()) {
            DB::table('content_settings')->where('id', 1)->update($defaults);

            return;
        }

        $base = [
            'id' => 1,
            'posts_per_page' => 12,
            'related_posts_count' => 3,
            'show_author' => true,
            'show_date' => true,
            'show_categories' => true,
            'show_tags' => true,
            'show_excerpt_in_list' => true,
            'show_featured_image_in_list' => true,
            'comments_enabled' => true,
            'comments_require_approval' => false,
            'blog_sidebar_enabled' => true,
            'breadcrumbs_enabled' => true,
            'track_broken_links' => true,
            'reading_time_enabled' => true,
            'reading_words_per_minute' => 200,
            'blog_layout' => 'grid',
            'sitemap_include_pages' => true,
            'sitemap_include_posts' => true,
            'created_at' => $now,
        ];

        DB::table('content_settings')->insert($this->filterColumns(
            'content_settings',
            array_merge($base, $defaults)
        ));
    }

    public function down(): void
    {
        // Backfill migration only: keep user settings intact on rollback.
    }

    private function filterColumns(string $table, array $payload): array
    {
        $columns = array_flip(Schema::getColumnListing($table));

        return array_intersect_key($payload, $columns);
    }
};

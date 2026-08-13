<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Get active locale codes before touching the schema
        try {
            $codes = DB::table('site_languages')
                ->where('active', true)
                ->orderBy('sort_order')
                ->pluck('code')
                ->all();
        } catch (Throwable) {
            $codes = [];
        }
        if (empty($codes)) {
            $codes = ['es', 'en'];
        }

        // 1. Convert plain-string slugs to JSON BEFORE changing column type
        $rows = DB::table('posts')->select('id', 'slug')->get();
        foreach ($rows as $row) {
            if ($row->slug === null) {
                continue;
            }
            // Skip if already valid JSON object
            $decoded = json_decode($row->slug, true);
            if (is_array($decoded)) {
                continue;
            }

            $json = [];
            foreach ($codes as $code) {
                $json[$code] = $row->slug;
            }
            DB::table('posts')->where('id', $row->id)
                ->update(['slug' => json_encode($json, JSON_UNESCAPED_UNICODE)]);
        }

        // 2. Drop the old unique index if it still exists. Schema::hasIndex()
        // is supported by all database drivers used by the application; raw
        // `SHOW INDEX` made every SQLite migration (and therefore the test
        // suite) fail before the application could boot.
        if (Schema::hasIndex('posts', 'posts_slug_unique')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropUnique('posts_slug_unique');
            });
        }

        // 3. Change slug to JSON
        Schema::table('posts', function (Blueprint $table) {
            $table->json('slug')->change();
        });
    }

    public function down(): void
    {
        // Convert JSON back to primary-locale string and restore the unique constraint
        $rows = DB::table('posts')->select('id', 'slug')->get();
        foreach ($rows as $row) {
            if ($row->slug === null) {
                continue;
            }
            $decoded = json_decode($row->slug, true);
            if (! is_array($decoded)) {
                continue;
            }
            $plain = reset($decoded);
            DB::table('posts')->where('id', $row->id)->update(['slug' => $plain]);
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->change();
            $table->unique('slug');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $primaryLocale = 'es';

    public function up(): void
    {
        // ── posts: convert translatable columns to JSON ─────────────────────
        Schema::table('posts', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('excerpt')->nullable()->change();
            $table->json('content')->nullable()->change();
            $table->json('featured_image_alt')->nullable()->change();
            $table->json('seo_title')->nullable()->change();
            $table->json('seo_description')->nullable()->change();
            $table->json('seo_keywords')->nullable()->change();
            $table->json('og_title')->nullable()->change();
            $table->json('og_description')->nullable()->change();
        });

        // Migrate existing plain-text rows to locale-keyed JSON
        DB::table('posts')->get()->each(function ($row) {
            $updates = [];
            foreach (['title', 'excerpt', 'content', 'featured_image_alt',
                      'seo_title', 'seo_description', 'seo_keywords',
                      'og_title', 'og_description'] as $field) {
                $raw = $row->$field;
                if ($raw !== null && !$this->looksLikeJson($raw)) {
                    $updates[$field] = json_encode([$this->primaryLocale => $raw]);
                }
            }
            if ($updates) {
                DB::table('posts')->where('id', $row->id)->update($updates);
            }
        });

        // ── post_categories: name/description already json via HasTranslations
        //    but the columns might be varchar — fix them
        Schema::table('post_categories', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        DB::table('post_categories')->get()->each(function ($row) {
            $updates = [];
            foreach (['name', 'description'] as $field) {
                $raw = $row->$field;
                if ($raw !== null && !$this->looksLikeJson($raw)) {
                    $updates[$field] = json_encode([$this->primaryLocale => $raw]);
                }
            }
            if ($updates) {
                DB::table('post_categories')->where('id', $row->id)->update($updates);
            }
        });

        // ── post_tags: name already json via HasTranslations
        Schema::table('post_tags', function (Blueprint $table) {
            $table->json('name')->change();
        });

        DB::table('post_tags')->get()->each(function ($row) {
            $raw = $row->name;
            if ($raw !== null && !$this->looksLikeJson($raw)) {
                DB::table('post_tags')->where('id', $row->id)
                    ->update(['name' => json_encode([$this->primaryLocale => $raw])]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title')->change();
            $table->text('excerpt')->nullable()->change();
            $table->longText('content')->nullable()->change();
            $table->string('featured_image_alt')->nullable()->change();
            $table->string('seo_title')->nullable()->change();
            $table->text('seo_description')->nullable()->change();
            $table->string('seo_keywords')->nullable()->change();
            $table->string('og_title')->nullable()->change();
            $table->text('og_description')->nullable()->change();
        });

        Schema::table('post_categories', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
        });

        Schema::table('post_tags', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }

    private function looksLikeJson(string $value): bool
    {
        $trimmed = trim($value);
        return str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[');
    }
};

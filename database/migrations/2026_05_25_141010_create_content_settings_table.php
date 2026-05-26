<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('posts_per_page')->default(12);
            $table->unsignedTinyInteger('related_posts_count')->default(3);
            $table->boolean('show_author')->default(true);
            $table->boolean('show_date')->default(true);
            $table->boolean('show_categories')->default(true);
            $table->boolean('show_tags')->default(true);
            $table->boolean('show_excerpt_in_list')->default(true);
            $table->boolean('show_featured_image_in_list')->default(true);
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('comments_require_approval')->default(false);
            $table->boolean('blog_sidebar_enabled')->default(true);
            $table->boolean('breadcrumbs_enabled')->default(true);
            $table->boolean('track_broken_links')->default(true);
            $table->string('broken_links_notify_email')->nullable();
            $table->string('reading_time_enabled')->default(true);
            $table->unsignedSmallInteger('reading_words_per_minute')->default(200);
            $table->unsignedBigInteger('featured_post_id')->nullable();
            $table->string('blog_layout')->default('grid'); // grid | list
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_settings');
    }
};

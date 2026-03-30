<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 20);
            $table->string('path');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['locale', 'path']);
            $table->index(['path', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_page_settings');
    }
};

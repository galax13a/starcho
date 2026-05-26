<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('password_enabled')->default(false);
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('media_album_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_album_id')->constrained('media_albums')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['media_album_id', 'media_id']);
        });

        Schema::create('media_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('media_taggables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_tag_id')->constrained('media_tags')->cascadeOnDelete();
            $table->morphs('taggable');
            $table->timestamps();
            $table->unique(['media_tag_id', 'taggable_type', 'taggable_id']);
        });

        Schema::create('media_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('commentable');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('media_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('ratable');
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
            $table->unique(['user_id', 'ratable_type', 'ratable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_ratings');
        Schema::dropIfExists('media_comments');
        Schema::dropIfExists('media_taggables');
        Schema::dropIfExists('media_tags');
        Schema::dropIfExists('media_album_media');
        Schema::dropIfExists('media_albums');
    }
};

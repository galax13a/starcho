<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Storage location
            $table->string('driver', 20)->default('local'); // local | s3 | do_spaces | r2
            $table->string('disk', 40)->default('public');  // Laravel disk name used
            $table->string('path');                         // stored path on disk
            $table->string('webp_path')->nullable();        // WebP version path (images only)
            $table->string('url')->nullable();              // full public URL (cloud)

            // File meta
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Polymorphic owner (post, page, etc.)
            $table->nullableMorphs('mediable');

            // Optional context tag (featured_image, gallery, editor, etc.)
            $table->string('context', 60)->nullable();

            $table->string('alt')->nullable();
            $table->string('caption')->nullable();

            $table->timestamps();

            $table->index('driver');
            $table->index('context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

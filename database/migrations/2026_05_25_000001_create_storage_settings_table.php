<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_settings', function (Blueprint $table) {
            $table->id();
            // Active driver: local | s3 | do_spaces | r2
            $table->string('default_driver', 20)->default('local');

            // Amazon S3
            $table->string('s3_key')->nullable();
            $table->string('s3_secret')->nullable();
            $table->string('s3_region', 50)->nullable()->default('us-east-1');
            $table->string('s3_bucket')->nullable();
            $table->string('s3_endpoint')->nullable();
            $table->boolean('s3_use_path_style')->default(false);
            $table->string('s3_url')->nullable();

            // DigitalOcean Spaces
            $table->string('do_key')->nullable();
            $table->string('do_secret')->nullable();
            $table->string('do_region', 50)->nullable()->default('nyc3');
            $table->string('do_bucket')->nullable();
            $table->string('do_endpoint')->nullable();
            $table->string('do_cdn_url')->nullable();

            // Cloudflare R2
            $table->string('r2_account_id')->nullable();
            $table->string('r2_key')->nullable();
            $table->string('r2_secret')->nullable();
            $table->string('r2_bucket')->nullable();
            $table->string('r2_endpoint')->nullable();
            $table->string('r2_public_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_settings');
    }
};

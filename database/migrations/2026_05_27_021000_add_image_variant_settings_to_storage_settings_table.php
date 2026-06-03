<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            $table->boolean('image_variants_enabled')->default(false)->after('local_url');
            $table->json('image_variant_sizes')->nullable()->after('image_variants_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            $table->dropColumn(['image_variants_enabled', 'image_variant_sizes']);
        });
    }
};

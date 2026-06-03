<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('storage_settings', 'image_preview_variant_size')) {
                $table->unsignedSmallInteger('image_preview_variant_size')->default(240)->after('image_variant_sizes');
            }

            if (! Schema::hasColumn('storage_settings', 'avatar_size')) {
                $table->unsignedSmallInteger('avatar_size')->default(190)->after('image_preview_variant_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('storage_settings', function (Blueprint $table): void {
            foreach (['avatar_size', 'image_preview_variant_size'] as $column) {
                if (Schema::hasColumn('storage_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_ai_generations', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->after('duration_ms');
            $table->text('rating_notes')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('post_ai_generations', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_notes']);
        });
    }
};

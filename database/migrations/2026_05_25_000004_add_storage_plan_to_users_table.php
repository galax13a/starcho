<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('storage_plan_id')
                ->nullable()
                ->after('id')
                ->constrained('storage_plans')
                ->nullOnDelete();

            // Running total of bytes used across all media owned by this user
            $table->unsignedBigInteger('storage_used_bytes')
                ->default(0)
                ->after('storage_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['storage_plan_id']);
            $table->dropColumn(['storage_plan_id', 'storage_used_bytes']);
        });
    }
};

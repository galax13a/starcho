<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::getColumnType('tasks', 'title') !== 'text') {
                $table->text('title')->change();
            }

            if (Schema::getColumnType('tasks', 'description') !== 'text') {
                $table->text('description')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('description')->nullable()->change();
        });
    }
};

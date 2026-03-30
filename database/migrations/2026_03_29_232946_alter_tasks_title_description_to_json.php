<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convert existing string data to JSON
        DB::table('tasks')->get()->each(function ($task) {
            $titleJson = json_encode(['en' => $task->title]);
            $descriptionJson = $task->description ? json_encode(['en' => $task->description]) : null;

            DB::table('tasks')
                ->where('id', $task->id)
                ->update([
                    'title' => $titleJson,
                    'description' => $descriptionJson,
                ]);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('title')->change();
            $table->text('description')->nullable()->change();
        });
    }
};

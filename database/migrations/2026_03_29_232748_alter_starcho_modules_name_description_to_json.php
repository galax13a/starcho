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
        DB::table('starcho_modules')->get()->each(function ($module) {
            $nameJson = json_encode(['en' => $module->name]);
            $descriptionJson = $module->description ? json_encode(['en' => $module->description]) : null;

            DB::table('starcho_modules')
                ->where('id', $module->id)
                ->update([
                    'name' => $nameJson,
                    'description' => $descriptionJson,
                ]);
        });

        Schema::table('starcho_modules', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starcho_modules', function (Blueprint $table) {
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
        });

        // Optionally convert back, but since we're reversing, maybe not necessary
    }
};

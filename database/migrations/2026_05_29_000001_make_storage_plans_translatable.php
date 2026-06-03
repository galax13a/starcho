<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add translatable description column.
        if (! Schema::hasColumn('storage_plans', 'description')) {
            Schema::table('storage_plans', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }

        // Widen `name` to hold JSON translations.
        Schema::table('storage_plans', function (Blueprint $table) {
            $table->text('name')->change();
        });

        // Convert existing plain-string names into Spatie translation JSON.
        $defaultLocales = ['es', 'en'];

        DB::table('storage_plans')->orderBy('id')->get(['id', 'name'])->each(function ($plan) use ($defaultLocales) {
            $current = (string) $plan->name;

            // Skip if already JSON.
            $decoded = json_decode($current, true);
            if (is_array($decoded)) {
                return;
            }

            $translations = [];
            foreach ($defaultLocales as $locale) {
                $translations[$locale] = $current;
            }

            DB::table('storage_plans')
                ->where('id', $plan->id)
                ->update(['name' => json_encode($translations, JSON_UNESCAPED_UNICODE)]);
        });
    }

    public function down(): void
    {
        // Collapse JSON name back to a plain string (first available translation).
        DB::table('storage_plans')->orderBy('id')->get(['id', 'name'])->each(function ($plan) {
            $decoded = json_decode((string) $plan->name, true);

            if (is_array($decoded)) {
                $value = $decoded['es'] ?? reset($decoded) ?: '';
                DB::table('storage_plans')
                    ->where('id', $plan->id)
                    ->update(['name' => (string) $value]);
            }
        });

        Schema::table('storage_plans', function (Blueprint $table) {
            $table->string('name')->change();
        });

        if (Schema::hasColumn('storage_plans', 'description')) {
            Schema::table('storage_plans', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};

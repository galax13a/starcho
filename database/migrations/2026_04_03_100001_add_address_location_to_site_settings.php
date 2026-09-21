<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'address')) {
                $table->string('address', 500)->nullable()->after('company_city');
            }
            if (! Schema::hasColumn('site_settings', 'founding_year')) {
                $table->smallInteger('founding_year')->unsigned()->nullable()->after('address');
            }
            if (! Schema::hasColumn('site_settings', 'google_maps_url')) {
                $table->string('google_maps_url', 1000)->nullable()->after('founding_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            foreach (['address', 'founding_year', 'google_maps_url'] as $column) {
                if (Schema::hasColumn('site_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

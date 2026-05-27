<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('site_settings', 'home_source')) {
                $table->string('home_source', 20)->default('folio')->after('home_page_enabled');
            }

            if (! Schema::hasColumn('site_settings', 'home_page_id')) {
                $table->foreignId('home_page_id')->nullable()->after('home_source')->constrained('posts')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('site_settings', 'home_page_id')) {
                $table->dropConstrainedForeignId('home_page_id');
            }

            if (Schema::hasColumn('site_settings', 'home_source')) {
                $table->dropColumn('home_source');
            }
        });
    }
};

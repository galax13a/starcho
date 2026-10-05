<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_settings', function (Blueprint $table): void {
            // Keep private storage independent from the public upload driver.
            $table->string('private_driver', 20)->default('local');
            // Provider-specific buckets let old private media keep resolving after a driver switch.
            $table->string('private_s3_bucket')->nullable();
            $table->string('private_do_bucket')->nullable();
        });

        Schema::table('media', function (Blueprint $table): void {
            // Persist the exact private bucket per asset so later admin changes do not orphan it.
            $table->string('private_bucket')->nullable()->after('disk');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn('private_bucket');
        });

        Schema::table('storage_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'private_driver',
                'private_s3_bucket',
                'private_do_bucket',
            ]);
        });
    }
};

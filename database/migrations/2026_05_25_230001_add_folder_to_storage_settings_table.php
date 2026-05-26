<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            $table->string('s3_folder', 120)->nullable()->default('uploads')->after('s3_url');
            $table->string('do_folder', 120)->nullable()->default('uploads')->after('do_cdn_url');
            $table->string('r2_folder', 120)->nullable()->default('uploads')->after('r2_public_url');
            $table->string('local_folder', 120)->nullable()->default('uploads')->after('r2_folder');
        });
    }

    public function down(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            $table->dropColumn(['s3_folder', 'do_folder', 'r2_folder', 'local_folder']);
        });
    }
};

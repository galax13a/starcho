<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes backing the /admin/storage reports.
 *
 *  - users.storage_used_bytes : the "Usuarios" table and the "Top usuarios"
 *    chart both ORDER BY this column; without an index it triggers a filesort.
 *  - media (user_id, created_at) : the weekly top-12 report filters by
 *    created_at and groups by user_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('storage_used_bytes', 'users_storage_used_bytes_index');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'media_user_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_storage_used_bytes_index');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_user_id_created_at_index');
        });
    }
};

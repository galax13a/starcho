<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            // URL base del sitio para construir URLs públicas cuando el driver es local.
            // Ej: http://starcho.test/  → genera http://starcho.test/storage/uploads/media/...
            $table->string('local_url', 255)->nullable()->after('local_folder');
        });
    }

    public function down(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            $table->dropColumn('local_url');
        });
    }
};

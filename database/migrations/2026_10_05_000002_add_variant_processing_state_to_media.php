<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            // Null means variants were not requested; these states describe queued image work.
            $table->string('variants_status', 20)->nullable()->index()->after('variants_size');
            $table->text('variants_error')->nullable()->after('variants_status');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropIndex(['variants_status']);
            $table->dropColumn(['variants_status', 'variants_error']);
        });
    }
};

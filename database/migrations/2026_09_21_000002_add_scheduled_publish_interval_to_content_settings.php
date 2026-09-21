<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_settings', function (Blueprint $table): void {
            // Five minutes is the default cadence for auto-publishing scheduled blog content.
            $table->unsignedSmallInteger('scheduled_publish_interval_minutes')->default(5);
        });
    }

    public function down(): void
    {
        Schema::table('content_settings', function (Blueprint $table): void {
            $table->dropColumn('scheduled_publish_interval_minutes');
        });
    }
};

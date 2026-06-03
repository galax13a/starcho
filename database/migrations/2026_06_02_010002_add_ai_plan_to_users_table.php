<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('ai_plan_id')
                ->nullable()
                ->after('storage_used_bytes')
                ->constrained('ai_plans')
                ->nullOnDelete();

            // Per-period usage counters
            $table->unsignedBigInteger('ai_text_tokens_used')->default(0)->after('ai_plan_id');
            $table->unsignedInteger('ai_images_used')->default(0)->after('ai_text_tokens_used');
            $table->unsignedInteger('ai_videos_used')->default(0)->after('ai_images_used');
            // Accumulated real provider cost this period (cents)
            $table->unsignedBigInteger('ai_spend_cents')->default(0)->after('ai_videos_used');
            // Start of the current usage period (for monthly resets)
            $table->date('ai_usage_period_start')->nullable()->after('ai_spend_cents');

            $table->index('ai_text_tokens_used');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['ai_text_tokens_used']);
            $table->dropForeign(['ai_plan_id']);
            $table->dropColumn([
                'ai_plan_id', 'ai_text_tokens_used', 'ai_images_used',
                'ai_videos_used', 'ai_spend_cents', 'ai_usage_period_start',
            ]);
        });
    }
};

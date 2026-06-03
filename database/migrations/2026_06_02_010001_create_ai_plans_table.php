<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI subscription plans. Quotas are per billing period (monthly).
 * A NULL quota means "unlimited"; 0 means "not allowed".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_plans', function (Blueprint $table) {
            $table->id();
            $table->text('name');               // Spatie translatable JSON
            $table->text('description')->nullable();
            $table->string('slug', 80)->unique();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            // Per-period quotas (null = unlimited, 0 = disabled)
            $table->unsignedBigInteger('text_token_quota')->nullable();
            $table->unsignedInteger('image_quota')->nullable();
            $table->unsignedInteger('video_quota')->nullable();
            // Hard spend cap on real provider cost (cents). null = no cap.
            $table->unsignedBigInteger('monthly_budget_cents')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_plans');
    }
};

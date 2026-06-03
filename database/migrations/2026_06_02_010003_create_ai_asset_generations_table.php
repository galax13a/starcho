<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records each AI image/video generation: provider, model, prompt, status,
 * the real provider cost and the price billed to the user (cost x markup),
 * and a link to the resulting Media record when it lands in the gallery.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_asset_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 12);                 // image | video
            $table->string('provider', 40);
            $table->string('model', 180);
            $table->string('status', 20)->default('pending'); // pending|processing|completed|failed
            $table->string('external_id')->nullable();  // e.g. fal.ai request id
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->longText('prompt');
            $table->json('params')->nullable();
            $table->text('error')->nullable();
            $table->unsignedBigInteger('cost_cents')->default(0);   // real provider cost
            $table->unsignedBigInteger('price_cents')->default(0);  // billed to user
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_asset_generations');
    }
};

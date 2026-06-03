<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_ai_memories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_ai_generation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('source')->default('generation');
            $table->string('status')->default('draft');
            $table->boolean('active')->default(true);
            $table->text('prompt_text')->nullable();
            $table->longText('body');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'active']);
            $table->index(['post_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_ai_memories');
    }
};

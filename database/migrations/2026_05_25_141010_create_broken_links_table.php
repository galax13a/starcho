<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broken_links', function (Blueprint $table) {
            $table->id();
            $table->string('url', 2000);
            $table->string('referrer', 2000)->nullable();
            $table->string('locale', 10)->nullable();
            $table->string('method', 10)->default('GET');
            $table->text('user_agent')->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('hit_count')->default(1);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('ignored')->default(false);
            $table->string('redirect_to', 2000)->nullable();
            $table->timestamps();

            $table->index('ignored');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broken_links');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->text('content')->nullable();
            $table->string('color', 20)->default('#6366f1');
            $table->date('important_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'color']);
            $table->index('important_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

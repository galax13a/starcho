<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 20)->index();
            $table->string('name', 255);
            $table->string('run_id', 64)->nullable()->index();
            $table->string('status', 20)->index();
            $table->unsignedSmallInteger('attempt')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['type', 'started_at']);
            $table->index(['type', 'status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_runs');
    }
};

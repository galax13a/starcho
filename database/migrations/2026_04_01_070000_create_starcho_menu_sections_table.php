<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starcho_menu_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('panel', 20);
            $table->string('label', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['panel', 'label']);
            $table->index(['panel', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starcho_menu_sections');
    }
};

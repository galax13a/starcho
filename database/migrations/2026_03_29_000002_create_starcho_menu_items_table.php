<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starcho_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('module_key')->nullable();       // null = core item
            $table->foreignId('parent_id')->nullable()->constrained('starcho_menu_items')->nullOnDelete();
            $table->string('section')->nullable();          // visual group label (no link)
            $table->string('label');
            $table->string('icon')->nullable();             // heroicon or SVG name
            $table->string('route')->nullable();            // named route e.g. app.tasks.index
            $table->string('url')->nullable();              // fallback absolute URL
            $table->string('target')->default('_self');
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starcho_menu_items');
    }
};

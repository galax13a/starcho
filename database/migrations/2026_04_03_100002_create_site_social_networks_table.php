<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_social_networks', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 60)->unique();        // facebook, x, instagram, ...
            $table->string('label', 80);                // Facebook, X (Twitter), ...
            $table->string('icon', 80);                 // fab fa-facebook-f
            $table->string('color', 20)->default('#6b7280'); // brand hex color
            $table->string('url')->nullable();          // URL configurada por el admin
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_social_networks');
    }
};

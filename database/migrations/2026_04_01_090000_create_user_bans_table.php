<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Columnas de estado rápido en users ──────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('locale');
            $table->timestamp('banned_until')->nullable()->after('is_banned');   // null = ban permanente
            $table->text('ban_reason')->nullable()->after('banned_until');
        });

        // ── Historial completo de bans ──────────────────────────────────────────
        Schema::create('user_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('banned_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->timestamp('banned_at');
            $table->timestamp('expires_at')->nullable();   // null = permanente
            $table->timestamp('lifted_at')->nullable();
            $table->foreignId('lifted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bans');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_banned', 'banned_until', 'ban_reason']);
        });
    }
};

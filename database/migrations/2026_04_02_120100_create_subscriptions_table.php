<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('level', ['free', 'monthly', 'semiannual', 'yearly'])->default('free');
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['level', 'ends_at']);
        });

        DB::table('users')
            ->select('id', 'subscription_level', 'created_at', 'updated_at')
            ->orderBy('id')
            ->lazy()
            ->each(function (object $user): void {
                DB::table('subscriptions')->insert([
                    'user_id' => $user->id,
                    'level' => $user->subscription_level ?: 'free',
                    'is_active' => true,
                    'starts_at' => $user->created_at ?? now(),
                    'ends_at' => null,
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
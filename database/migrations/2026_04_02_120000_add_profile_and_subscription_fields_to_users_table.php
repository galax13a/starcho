<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'whatsapp')) {
                $table->string('whatsapp', 30)->nullable()->after('avatar');
            }

            if (! Schema::hasColumn('users', 'whatsapp_verified_at')) {
                $table->timestamp('whatsapp_verified_at')->nullable()->after('email_verified_at');
            }

            if (! Schema::hasColumn('users', 'subscription_level')) {
                $table->enum('subscription_level', ['free', 'monthly', 'semiannual', 'yearly'])
                    ->default('free')
                    ->after('locale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $columns = [];

            foreach (['avatar', 'whatsapp', 'whatsapp_verified_at', 'subscription_level'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
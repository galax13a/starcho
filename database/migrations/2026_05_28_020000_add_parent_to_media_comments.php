<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('media_comments')) {
            return;
        }

        Schema::table('media_comments', function (Blueprint $table): void {
            if (! Schema::hasColumn('media_comments', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('media_comments')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('media_comments', 'depth')) {
                $table->unsignedTinyInteger('depth')->default(0)->after('parent_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('media_comments')) {
            return;
        }

        Schema::table('media_comments', function (Blueprint $table): void {
            if (Schema::hasColumn('media_comments', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }

            if (Schema::hasColumn('media_comments', 'depth')) {
                $table->dropColumn('depth');
            }
        });
    }
};

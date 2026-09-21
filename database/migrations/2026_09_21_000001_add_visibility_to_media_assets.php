<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('visibility', 20)->default('public');
        });

        Schema::table('media_albums', function (Blueprint $table): void {
            $table->string('visibility', 20)->default('public');
        });

        // Existing password-protected albums retain their old behavior under
        // the explicit visibility model. Their files are proxied immediately;
        // the secure-media command migrates old public objects off public disks.
        DB::table('media_albums')
            ->where('password_enabled', true)
            ->update(['visibility' => 'protected']);

        DB::table('media')
            ->whereIn('id', DB::table('media_album_media')
                ->join('media_albums', 'media_albums.id', '=', 'media_album_media.media_album_id')
                ->where('media_albums.password_enabled', true)
                ->select('media_album_media.media_id'))
            ->update(['visibility' => 'protected']);

        DB::table('media')->where('visibility', '<>', 'public')->update(['url' => null]);
    }

    public function down(): void
    {
        Schema::table('media_albums', function (Blueprint $table): void {
            $table->dropColumn('visibility');
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->dropColumn('visibility');
        });
    }
};

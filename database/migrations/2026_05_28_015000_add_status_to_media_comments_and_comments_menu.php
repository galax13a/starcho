<?php

use App\Models\StarchoMenuItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media_comments') && ! Schema::hasColumn('media_comments', 'status')) {
            Schema::table('media_comments', function (Blueprint $table): void {
                $table->string('status', 30)->default('approved')->after('body')->index();
            });
        }

        if (Schema::hasTable('starcho_menu_items') && class_exists(StarchoMenuItem::class)) {
            $item = StarchoMenuItem::firstOrNew([
                'panel' => 'admin',
                'route' => 'admin.comments.index',
            ]);

            $item->fill([
                'module_key' => 'posts',
                'section' => 'Contenido',
                'icon' => 'fas fa-comments',
                'url' => null,
                'sort_order' => 65,
                'active' => true,
                'target' => '_self',
            ]);

            $item->setTranslation('name', 'es', 'Comentarios');
            $item->setTranslation('name', 'en', 'Comments');
            $item->setTranslation('name', 'pt_BR', 'Comentários');
            $item->save();

            StarchoMenuItem::where('panel', 'admin')
                ->where('route', 'admin.content.settings')
                ->update(['sort_order' => 66]);

            StarchoMenuItem::where('panel', 'admin')
                ->where('route', 'admin.content.broken-links')
                ->update(['sort_order' => 67]);

            StarchoMenuItem::clearMenuCache();
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('starcho_menu_items') && class_exists(StarchoMenuItem::class)) {
            StarchoMenuItem::where('panel', 'admin')
                ->where('route', 'admin.comments.index')
                ->delete();

            StarchoMenuItem::clearMenuCache();
        }

        if (Schema::hasTable('media_comments') && Schema::hasColumn('media_comments', 'status')) {
            Schema::table('media_comments', function (Blueprint $table): void {
                $table->dropColumn('status');
            });
        }
    }
};

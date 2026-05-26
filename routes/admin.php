<?php

use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\ContentSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeoLocationsController;
use App\Http\Controllers\Admin\MediaAlbumController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\StorageSettingsController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserBanController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:root|admin', 'permission:view-admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ── Roles: rutas custom ANTES del resource para evitar conflicto con {role} ──
        Route::get('roles/import',       [RoleController::class, 'importForm'])->name('roles.import');
        Route::post('roles/import',      [RoleController::class, 'import'])->name('roles.import.store');
        Route::get('roles/export',       [\App\Http\Controllers\Admin\AdminDataTransferController::class, 'exportRoles'])->name('roles.export');
        Route::get('roles/export-json',  [RoleController::class, 'exportJson'])->name('roles.export-json');
        Route::resource('roles', RoleController::class)->except(['show']);

        // ── Permissions: rutas custom ANTES del resource ───────────────────────────
        Route::get('permissions/import',      [PermissionController::class, 'importForm'])->name('permissions.import');
        Route::post('permissions/import',     [PermissionController::class, 'import'])->name('permissions.import.store');
        Route::get('permissions/export',      [\App\Http\Controllers\Admin\AdminDataTransferController::class, 'exportPermissions'])->name('permissions.export');
        Route::get('permissions/export-json', [PermissionController::class, 'exportJson'])->name('permissions.export-json');
        Route::resource('permissions', PermissionController::class)->except(['show']);

        // ── Users (CRUD) ─────────────────────────────────────────────────────────
        Route::get('users',                    [UserController::class, 'index'])->name('users.index');
        Route::get('users/export',             [\App\Http\Controllers\Admin\AdminDataTransferController::class, 'exportUsers'])->name('users.export');
        Route::get('users/create',             [UserController::class, 'create'])->name('users.create');
        Route::post('users',                   [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit',        [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}',             [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}',          [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('users/{user}/plan',      [UserController::class, 'updatePlan'])->name('users.plan');

        // ── Tasks (Index + Export + Import) ───────────────────────────────────────
        Route::get('tasks',             [TaskController::class, 'index'])->name('tasks.index');
        Route::get('tasks/export',      [\App\Http\Controllers\Admin\AdminDataTransferController::class, 'exportTasks'])->name('tasks.export');

        // ── Contacts (Index + Export + Import) ────────────────────────────────────
        Route::view('contacts', 'admin.contacts.index')->name('contacts.index');
        Route::get('contacts/export',   [\App\Http\Controllers\Admin\AdminDataTransferController::class, 'exportContacts'])->name('contacts.export');

        // ── Notes (Index + Export + Import) ───────────────────────────────────────
        Route::view('notes', 'admin.notes.index')->name('notes.index');
        Route::get('notes/export',      [\App\Http\Controllers\Admin\AdminDataTransferController::class, 'exportNotes'])->name('notes.export');

        // ── Site module (SEO / favicon / metadata) ─────────────────────────────
        Route::get('site', [SiteController::class, 'index'])->name('site.index');
        Route::put('site', [SiteController::class, 'update'])->name('site.update');
        Route::get('site/page-editor', [SiteController::class, 'editPage'])->name('site.pages.edit');
        Route::put('site/page-editor', [SiteController::class, 'updatePage'])->name('site.pages.update');

        // ── Modules ──────────────────────────────────────────────────────────────
        Route::get('modules',                    [ModuleController::class, 'index'])->name('modules.index');
        Route::get('modules/{module}/config',    [ModuleController::class, 'config'])->name('modules.config');
        Route::post('modules/{module}/install',  [ModuleController::class, 'install'])->name('modules.install');
        Route::post('modules/{module}/uninstall',[ModuleController::class, 'uninstall'])->name('modules.uninstall');
        Route::post('modules/{module}/activate', [ModuleController::class, 'activate'])->name('modules.activate');
        Route::post('modules/{module}/deactivate',[ModuleController::class, 'deactivate'])->name('modules.deactivate');

        // ── Menu Builder ─────────────────────────────────────────────────────────
        Route::get('menu', [MenuController::class, 'index'])->name('menu.index');

        // ── Cache ─────────────────────────────────────────────────────────────────
        Route::get('cache',                    [CacheController::class, 'index'])->name('cache.index');
        Route::post('cache/clear-all',         [CacheController::class, 'clearAll'])->name('cache.clear-all');
        Route::post('cache/clear-app',         [CacheController::class, 'clearApp'])->name('cache.clear-app');
        Route::post('cache/clear-routes',      [CacheController::class, 'clearRoutes'])->name('cache.clear-routes');
        Route::post('cache/clear-config',      [CacheController::class, 'clearConfig'])->name('cache.clear-config');
        Route::post('cache/clear-views',       [CacheController::class, 'clearViews'])->name('cache.clear-views');
        Route::post('cache/clear-permissions', [CacheController::class, 'clearPermissions'])->name('cache.clear-permissions');
        Route::post('cache/clear-menu',        [CacheController::class, 'clearMenu'])->name('cache.clear-menu');
        Route::post('cache/optimize',          [CacheController::class, 'optimize'])->name('cache.optimize');

        // ── Geolocation (Starcho IP) ─────────────────────────────────────────────
        Route::get('geolocations',                [GeoLocationsController::class, 'index'])->name('geolocations.index');
        Route::get('geolocations/{geolocation}', [GeoLocationsController::class, 'show'])->name('geolocations.show');

        // ── Ban Users ─────────────────────────────────────────────────────────────
        Route::get('users-ban',               [UserBanController::class, 'index'])->name('users-ban.index');
        Route::post('users-ban/{user}/ban',   [UserBanController::class, 'ban'])->name('users-ban.ban');
        Route::post('users-ban/{user}/unban', [UserBanController::class, 'unban'])->name('users-ban.unban');

        // ── Storage settings ──────────────────────────────────────────────────────
        Route::put('storage',                          [StorageSettingsController::class, 'update'])->name('storage.update');
        Route::post('storage/link',                    [StorageSettingsController::class, 'link'])->name('storage.link');
        Route::post('storage/test',                    [StorageSettingsController::class, 'test'])->name('storage.test');
        Route::post('storage/test-delete',             [StorageSettingsController::class, 'deleteTestFile'])->name('storage.test-delete');
        Route::post('storage/plans',                   [StorageSettingsController::class, 'storePlan'])->name('storage.plans.store');
        Route::put('storage/plans/{plan}',             [StorageSettingsController::class, 'updatePlan'])->name('storage.plans.update');
        Route::delete('storage/plans/{plan}',          [StorageSettingsController::class, 'destroyPlan'])->name('storage.plans.destroy');

        // ── Multimedia gallery ────────────────────────────────────────────────────
        Route::get('media',              [MediaController::class, 'index'])->name('media.index');
        Route::post('media/upload',      [MediaController::class, 'upload'])->name('media.upload');
        Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete'])->name('media.bulk-delete');
        Route::post('media/bulk-attach', [MediaController::class, 'bulkAttach'])->name('media.bulk-attach');
        Route::get('media/albums',       [MediaAlbumController::class, 'index'])->name('media.albums.index');
        Route::post('media/albums',      [MediaAlbumController::class, 'store'])->name('media.albums.store');
        Route::put('media/albums/{album}', [MediaAlbumController::class, 'update'])->name('media.albums.update');
        Route::delete('media/albums/{album}', [MediaAlbumController::class, 'destroy'])->name('media.albums.destroy');
        Route::post('media/albums/{album}/upload', [MediaAlbumController::class, 'upload'])->name('media.albums.upload');
        Route::post('media/albums/{album}/attach', [MediaAlbumController::class, 'attach'])->name('media.albums.attach');
        Route::delete('media/albums/{album}/files/{media}', [MediaAlbumController::class, 'detach'])->name('media.albums.files.detach');
        Route::post('media/albums/{album}/files/bulk-detach', [MediaAlbumController::class, 'bulkDetach'])->name('media.albums.files.bulk-detach');
        Route::patch('media/albums/{album}/files/{media}/move', [MediaAlbumController::class, 'move'])->name('media.albums.files.move');
        Route::put('media/albums/files/{media}', [MediaAlbumController::class, 'updateMedia'])->name('media.albums.files.update');
        Route::delete('media/albums/files/{media}', [MediaAlbumController::class, 'destroyMedia'])->name('media.albums.files.destroy');
        Route::post('media/albums/files/bulk-destroy', [MediaAlbumController::class, 'bulkDestroyMedia'])->name('media.albums.files.bulk-destroy');
        Route::post('media/albums/{type}/{id}/comments', [MediaAlbumController::class, 'comment'])->name('media.albums.comments.store');
        Route::delete('media/albums/comments/{comment}', [MediaAlbumController::class, 'destroyComment'])->name('media.albums.comments.destroy');
        Route::post('media/albums/{type}/{id}/rating', [MediaAlbumController::class, 'rate'])->name('media.albums.rating.store');
        Route::post('media/{media}/rating', [MediaController::class, 'rate'])->name('media.rating');
        Route::post('media/{media}/comments', [MediaController::class, 'comment'])->name('media.comments');
        Route::post('media/{media}/favorite', [MediaController::class, 'favorite'])->name('media.favorite');
        Route::get('media/{media}/download', [MediaController::class, 'download'])->name('media.download');
        Route::put('media/{media}',      [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}',   [MediaController::class, 'destroy'])->name('media.destroy');

        // ── Posts (blog) ──────────────────────────────────────────────────────────
        Route::post('posts/upload-image',                  [PostController::class, 'uploadEditorImage'])->name('posts.upload-image');
        Route::post('posts/generate-slug',                 [PostController::class, 'generateSlug'])->name('posts.generate-slug');
        Route::post('posts/{post}/gallery',                [PostController::class, 'uploadGalleryImage'])->name('posts.gallery.upload');
        Route::delete('posts/{post}/gallery/{media}',      [PostController::class, 'destroyGalleryImage'])->name('posts.gallery.destroy');
        Route::get('posts',                                [PostController::class, 'index'])->name('posts.index');
        Route::get('posts/create',                         [PostController::class, 'create'])->name('posts.create');
        Route::post('posts',                               [PostController::class, 'store'])->name('posts.store');
        Route::get('posts/{post}/edit',                    [PostController::class, 'edit'])->name('posts.edit');
        Route::put('posts/{post}',                         [PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post}',                      [PostController::class, 'destroy'])->name('posts.destroy');

        // ── Pages (sitio) ─────────────────────────────────────────────────────────
        Route::get('pages',                  [PostController::class, 'pagesIndex'])->name('pages.index');
        Route::get('pages/create',           [PostController::class, 'pagesCreate'])->name('pages.create');
        Route::post('pages',                 [PostController::class, 'pagesStore'])->name('pages.store');
        Route::get('pages/{post}/edit',      [PostController::class, 'pagesEdit'])->name('pages.edit');
        Route::put('pages/{post}',           [PostController::class, 'pagesUpdate'])->name('pages.update');
        Route::delete('pages/{post}',        [PostController::class, 'pagesDestroy'])->name('pages.destroy');

        // ── Post Categories ───────────────────────────────────────────────────────
        Route::view('post-categories', 'admin.post-categories.index')->name('post-categories.index');

        // ── Post Tags ─────────────────────────────────────────────────────────────
        Route::view('post-tags', 'admin.post-tags.index')->name('post-tags.index');

        // ── Content Settings & Broken Links ───────────────────────────────────────
        Route::get('content/settings',                        [ContentSettingsController::class, 'index'])->name('content.settings');
        Route::put('content/settings',                        [ContentSettingsController::class, 'update'])->name('content.settings.update');
        Route::post('content/sitemap/generate',               [ContentSettingsController::class, 'generateSitemap'])->name('content.sitemap.generate');
        Route::get('content/broken-links',                    [ContentSettingsController::class, 'brokenLinks'])->name('content.broken-links');
        Route::patch('content/broken-links/{link}/ignore',    [ContentSettingsController::class, 'ignoreLink'])->name('content.broken-links.ignore');
        Route::patch('content/broken-links/{link}/restore',   [ContentSettingsController::class, 'restoreLink'])->name('content.broken-links.restore');
        Route::patch('content/broken-links/{link}/redirect',  [ContentSettingsController::class, 'redirectLink'])->name('content.broken-links.redirect');
        Route::delete('content/broken-links/{link}',          [ContentSettingsController::class, 'destroyLink'])->name('content.broken-links.destroy');
        Route::delete('content/broken-links',                 [ContentSettingsController::class, 'clearIgnored'])->name('content.broken-links.clear-ignored');
    });

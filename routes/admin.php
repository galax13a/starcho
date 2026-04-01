<?php

use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeoLocationsController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
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
        Route::get('users',               [UserController::class, 'index'])->name('users.index');
        Route::get('users/export',        [\App\Http\Controllers\Admin\AdminDataTransferController::class, 'exportUsers'])->name('users.export');
        Route::get('users/create',        [UserController::class, 'create'])->name('users.create');
        Route::post('users',              [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit',   [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}',        [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}',     [UserController::class, 'destroy'])->name('users.destroy');

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
    });

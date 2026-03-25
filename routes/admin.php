<?php

use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:admin'])
    ->group(function () {

        Route::get('/', fn () => redirect()->route('admin.roles.index'))->name('index');

        // ── Roles: rutas custom ANTES del resource para evitar conflicto con {role} ──
        Route::get('roles/import',       [RoleController::class, 'importForm'])->name('roles.import');
        Route::post('roles/import',      [RoleController::class, 'import'])->name('roles.import.store');
        Route::get('roles/export-json',  [RoleController::class, 'exportJson'])->name('roles.export-json');
        Route::resource('roles', RoleController::class)->except(['show']);

        // ── Permissions: rutas custom ANTES del resource ───────────────────────────
        Route::get('permissions/import',      [PermissionController::class, 'importForm'])->name('permissions.import');
        Route::post('permissions/import',     [PermissionController::class, 'import'])->name('permissions.import.store');
        Route::get('permissions/export-json', [PermissionController::class, 'exportJson'])->name('permissions.export-json');
        Route::resource('permissions', PermissionController::class)->except(['show']);

        // ── Users (CRUD) ─────────────────────────────────────────────────────────
        Route::get('users',               [UserController::class, 'index'])->name('users.index');
        Route::get('users/create',        [UserController::class, 'create'])->name('users.create');
        Route::post('users',              [UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit',   [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}',        [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}',     [UserController::class, 'destroy'])->name('users.destroy');

        // ── Cache ─────────────────────────────────────────────────────────────────
        Route::get('cache',                    [CacheController::class, 'index'])->name('cache.index');
        Route::post('cache/clear-all',         [CacheController::class, 'clearAll'])->name('cache.clear-all');
        Route::post('cache/clear-app',         [CacheController::class, 'clearApp'])->name('cache.clear-app');
        Route::post('cache/clear-routes',      [CacheController::class, 'clearRoutes'])->name('cache.clear-routes');
        Route::post('cache/clear-config',      [CacheController::class, 'clearConfig'])->name('cache.clear-config');
        Route::post('cache/clear-views',       [CacheController::class, 'clearViews'])->name('cache.clear-views');
        Route::post('cache/clear-permissions', [CacheController::class, 'clearPermissions'])->name('cache.clear-permissions');
        Route::post('cache/optimize',          [CacheController::class, 'optimize'])->name('cache.optimize');
    });

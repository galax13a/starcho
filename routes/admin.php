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

        // Roles
        Route::resource('roles', RoleController::class);

        // Permissions
        Route::resource('permissions', PermissionController::class);

        // Users
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');

        // Cache
        Route::get('cache', [CacheController::class, 'index'])->name('cache.index');
        Route::post('cache/clear-all', [CacheController::class, 'clearAll'])->name('cache.clear-all');
        Route::post('cache/clear-app', [CacheController::class, 'clearApp'])->name('cache.clear-app');
        Route::post('cache/clear-routes', [CacheController::class, 'clearRoutes'])->name('cache.clear-routes');
        Route::post('cache/clear-config', [CacheController::class, 'clearConfig'])->name('cache.clear-config');
        Route::post('cache/clear-views', [CacheController::class, 'clearViews'])->name('cache.clear-views');
        Route::post('cache/optimize', [CacheController::class, 'optimize'])->name('cache.optimize');
    });

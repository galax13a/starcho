<?php

use App\Http\Controllers\App\DataTransferController;
use Illuminate\Support\Facades\Route;

// Alias global esperado por Fortify y por algunos componentes del starter kit.
// Se declara fuera del prefijo de nombres para que `route('dashboard')` y
// `route('app.dashboard')` sigan siendo compatibles sin exponer el dashboard.
Route::view('app/dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'banned'])
    ->name('dashboard');

Route::prefix('app')
    ->name('app.')
    ->middleware(['auth', 'verified', 'banned'])
    ->group(function () {

        Route::view('/', 'dashboard')->name('dashboard');

        Route::view('tasks', 'tasks.index')->name('tasks.index');
        Route::get('tasks/export', [DataTransferController::class, 'exportTasks'])->name('tasks.export');

        Route::view('contacts', 'contacts.index')->name('contacts.index');
        Route::get('contacts/export', [DataTransferController::class, 'exportContacts'])->name('contacts.export');

        Route::view('notes', 'notes.index')->name('notes.index');
        Route::get('notes/export', [DataTransferController::class, 'exportNotes'])->name('notes.export');
    });

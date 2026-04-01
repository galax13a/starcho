<?php

use App\Http\Controllers\App\DataTransferController;
use Illuminate\Support\Facades\Route;

Route::prefix('app')
    ->name('app.')
    ->middleware(['auth', 'verified', 'banned'])
    ->group(function () {

        Route::view('/', 'dashboard')->name('dashboard');
        Route::view('dashboard', 'dashboard');

        Route::view('tasks', 'tasks.index')->name('tasks.index');
        Route::get('tasks/export', [DataTransferController::class, 'exportTasks'])->name('tasks.export');

        Route::view('contacts', 'contacts.index')->name('contacts.index');
        Route::get('contacts/export', [DataTransferController::class, 'exportContacts'])->name('contacts.export');

        Route::view('notes', 'notes.index')->name('notes.index');
        Route::get('notes/export', [DataTransferController::class, 'exportNotes'])->name('notes.export');
    });

<?php

use Illuminate\Support\Facades\Route;

Route::prefix('app')
    ->name('app.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        Route::view('/', 'dashboard')->name('dashboard');

        Route::view('tasks', 'tasks.index')->name('tasks.index');

        Route::view('contacts', 'contacts.index')->name('contacts.index');
    });

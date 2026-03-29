<?php

use Illuminate\Support\Facades\Route;

Route::get('language/{locale}', [App\Http\Controllers\LanguageController::class, 'switch'])->name('language.switch');

require __DIR__.'/settings.php';

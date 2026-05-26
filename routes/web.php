<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Public blog and pages with locale prefix
Route::prefix('{locale}')
    ->middleware(\App\Http\Middleware\SetLocaleFromUrl::class)
    ->where(['locale' => '[a-z]{2}(_[A-Z]{2})?'])
    ->group(function () {
        Route::get('blog',        [BlogController::class, 'index'])->name('blog.index');
        Route::get('blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
        Route::get('{slug}',      [PageController::class, 'show'])->name('page.show');
    });

require __DIR__.'/settings.php';

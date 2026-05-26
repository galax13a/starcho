<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MediaAlbumController;
use App\Http\Controllers\MediaFileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('media/files/{media}', [MediaFileController::class, 'show'])->name('media.files.show');
Route::get('media/albums/{album:slug}', [MediaAlbumController::class, 'show'])->name('media.albums.show');
Route::post('media/albums/{album:slug}/unlock', [MediaAlbumController::class, 'unlock'])->name('media.albums.unlock');

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

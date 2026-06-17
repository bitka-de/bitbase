<?php

use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\PageShowController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('pages.admin.dashboard');
    })->name('dashboard');

    Route::post('pages/{page}/revisions/restore', [PageController::class, 'restoreRevision'])
        ->name('pages.revisions.restore');

    Route::post('pages/{page}/revisions/prune', [PageController::class, 'pruneRevisions'])
        ->name('pages.revisions.prune');

    Route::resource('pages', PageController::class)->except(['show']);
});

Route::get('/stylebook', function () {
    return view('pages.stylebook');
})->name('stylebook');

Route::get('/robots.txt', RobotsController::class)->name('robots.txt');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'index'])->name('sitemap.pages');

Route::get('/{slugPath}', PageShowController::class)
    ->where('slugPath', '^(?!admin$|admin/|stylebook$|stylebook/|login$|login/|register$|register/|logout$|logout/|sitemap\\.xml$|sitemap-pages\\.xml$|robots\\.txt$|up$).+')
    ->name('pages.show');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

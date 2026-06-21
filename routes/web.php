<?php

use App\Http\Controllers\Admin\ContentComponentController;
use App\Http\Controllers\Admin\ExternalMediaSearchController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\MediaFileController;
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

    Route::get('components-export', [ContentComponentController::class, 'export'])
        ->name('components.export');
    Route::post('components-export-zip', [ContentComponentController::class, 'exportZip'])
        ->name('components.export-zip');
    Route::post('components-import', [ContentComponentController::class, 'import'])
        ->name('components.import');

    Route::get('media', [MediaController::class, 'index'])
        ->name('media.index');
    Route::post('media', [MediaController::class, 'store'])
        ->name('media.store');
    Route::delete('media/bulk', [MediaController::class, 'bulkDestroy'])
        ->name('media.bulk-destroy');
    Route::post('media/{media}/tags', [MediaController::class, 'attachTag'])
        ->name('media.attach-tag');
    Route::get('media/external/search', ExternalMediaSearchController::class)
        ->name('media.external.search');
    Route::post('media/external/import', [MediaController::class, 'importExternal'])
        ->name('media.external.import');
    Route::get('media/{media}/edit', [MediaController::class, 'edit'])
        ->name('media.edit');
    Route::patch('media/{media}', [MediaController::class, 'update'])
        ->name('media.update');
    Route::delete('media/{media}', [MediaController::class, 'destroy'])
        ->name('media.destroy');

    Route::resource('components', ContentComponentController::class)->except(['show']);
    Route::resource('pages', PageController::class)->except(['show']);
});

Route::get('/stylebook', function () {
    return view('pages.stylebook');
})->name('stylebook');

Route::get('/robots.txt', RobotsController::class)->name('robots.txt');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'index'])->name('sitemap.pages');
Route::get('/media/{filename}', MediaFileController::class)
    ->where('filename', '.*')
    ->name('media.show');

Route::get('/{slugPath}', PageShowController::class)
    ->where('slugPath', '^(?!admin$|admin/|stylebook$|stylebook/|login$|login/|register$|register/|logout$|logout/|sitemap\\.xml$|sitemap-pages\\.xml$|robots\\.txt$|up$).+')
    ->name('pages.show');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

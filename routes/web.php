<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('pages.admin.dashboard');
    })->name('admin.dashboard');
});

Route::get('/stylebook', function () {
    return view('pages.stylebook');
})->name('stylebook');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

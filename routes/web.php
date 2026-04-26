<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/oauth/yandex/callback', [\App\Http\Controllers\Cabinet\SiteMetricController::class, 'handleProviderCallback'])
    ->name('oauth.callback');
//Route::get('/oauth/yandex/callback', [App\Http\Controllers\OAuth\YandexController::class, 'callback'])
//    ->name('oauth.yandex.callback');

/*
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
//*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/', function () {
    return view('welcome');
});


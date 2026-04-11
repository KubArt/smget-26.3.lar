<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


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

Route::get('/v1/get-widgets', [\App\Http\Controllers\Widgets\WidgetDeliveryController::class, 'getPayload']);
Route::post('/v1/track', [\App\Http\Controllers\Widgets\WidgetDeliveryController::class, 'track']);

Route::get('/', function () {
    return view('welcome');
});

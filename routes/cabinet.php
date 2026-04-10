<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Cabinet\IndexCabinetController::class, 'index'])->name('index');

// Дашборд
Route::get('/dashboard', [\App\Http\Controllers\Cabinet\DashboardCabinetController::class, 'index'])->name('dashboard');

// Системные сообщения
Route::prefix('messages')->name('messages.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Cabinet\MessageController::class, 'index'])->name('index');
    Route::get('/{id}', [\App\Http\Controllers\Cabinet\MessageController::class, 'show'])->name('show');
    Route::post('/{id}/read', [\App\Http\Controllers\Cabinet\MessageController::class, 'markAsRead'])->name('read');
});


Route::get('/sites/create', [App\Http\Controllers\Cabinet\SiteController::class, 'create'])->name('sites.create');

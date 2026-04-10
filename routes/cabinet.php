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


// Профиль
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Cabinet\ProfileController::class, 'index'])->name('index');
    Route::post('/update', [\App\Http\Controllers\Cabinet\ProfileController::class, 'update'])->name('update');
    Route::post('/password', [\App\Http\Controllers\Cabinet\ProfileController::class, 'updatePassword'])->name('password');
});



// Работа с сайтами
    Route::resource('sites', \App\Http\Controllers\Cabinet\SiteController::class);
    Route::post('sites/{site}/verify', [\App\Http\Controllers\Cabinet\SiteController::class, 'verify'])->name('sites.verify');
// Верификация сайта
    Route::post('sites/{site}/verify-ajax', [\App\Http\Controllers\Cabinet\SiteController::class, 'verifyAjax'])->name('sites.verify.ajax');

    Route::get('sites/{site}/notifications', [\App\Http\Controllers\Cabinet\SiteController::class, 'notifications'])->name('sites.notifications');
    Route::get('notifications/{id}/read', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'readAndRedirect'])->name('notifications.read');

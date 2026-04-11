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
    Route::post('/{id}/delete', [\App\Http\Controllers\Cabinet\MessageController::class, 'destroy'])->name('destroy');
});


// Профиль
Route::prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Cabinet\ProfileController::class, 'index'])->name('index');
    Route::post('/update', [\App\Http\Controllers\Cabinet\ProfileController::class, 'update'])->name('update');
    Route::post('/password', [\App\Http\Controllers\Cabinet\ProfileController::class, 'updatePassword'])->name('password');
});



// Работа с сайтами
    Route::resource('sites', \App\Http\Controllers\Cabinet\SiteController::class);

// Группа для управления виджетами конкретного сайта
Route::prefix('sites/{site}')->name('sites.')->group(function () {
    // Страница со списком установленных виджетов на сайте
    Route::get('widgets', [\App\Http\Controllers\Cabinet\SiteWidgetController::class, 'index'])->name('widgets.index');
    Route::resource('widgets', \App\Http\Controllers\Cabinet\SiteWidgetController::class);
    Route::post('widgets/{widget}/toggle', [\App\Http\Controllers\Cabinet\SiteWidgetController::class, 'toggle'])->name('widgets.toggle');


// Верификация сайта
    Route::post('verify', [\App\Http\Controllers\Cabinet\SiteController::class, 'verify'])->name('verify');
    Route::post('verify-ajax', [\App\Http\Controllers\Cabinet\SiteController::class, 'verifyAjax'])->name('verify.ajax');
    Route::get('notifications', [\App\Http\Controllers\Cabinet\SiteController::class, 'notifications'])->name('notifications');
});

    Route::get('notifications/{id}/read', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'readAndRedirect'])->name('notifications.read');

// Общий маркетплейс (все доступные типы виджетов)
    Route::get('marketplace', [\App\Http\Controllers\Cabinet\SiteWidgetController::class, 'market'])->name('marketplace.index');


// Для биллинга выделим отдельный префикс и группу. Это позволит удобно управлять доступами.

Route::prefix('billing')->name('billing.')->group(function () {
    // Главная страница биллинга (баланс, история транзакций)
    Route::get('/', [\App\Http\Controllers\Billing\BillingController::class, 'index'])->name('index');
    // Активация ваучера
    Route::post('/voucher/activate', [\App\Http\Controllers\Billing\BillingController::class, 'activateVoucher'])->name('voucher.activate');
    // Тарифы и подписки
    Route::get('/plans', [\App\Http\Controllers\Billing\PlanController::class, 'index'])->name('plans.index');
    // Процесс покупки/активации
    Route::post('/check-balance', [\App\Http\Controllers\Billing\SubscriptionController::class, 'checkBalance'])->name('check-balance');
    Route::post('/subscribe', [\App\Http\Controllers\Billing\SubscriptionController::class, 'subscribe'])->name('subscribe');
});







/* TODO:
        Бонус: Логика "Бесплатного периода"
        Если ты захочешь сделать все сайты бесплатными на первые 7 дней, тебе достаточно будет изменить одну строчку в Middleware:
        PHP
        if (!$site->activeSubscription && $site->created_at->diffInDays(now()) > 7) {
            // ... редирект
        }
//*/

// Группа роутов, требующая активной подписки
// например проверка количества виджетов или лидов и т.д.
Route::middleware(['subscription'])->group(function () {
    // Сюда переносим всё, что должно быть платным
    // Например, редактирование сайта
    // Route::get('sites/{site}/edit', [\App\Http\Controllers\Cabinet\SiteController::class, 'edit'])->name('sites.edit');

    // Будущие роуты виджетов
    // Route::resource('sites.widgets', \App\Http\Controllers\Cabinet\WidgetController::class);

});

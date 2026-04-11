<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
        Route::middleware('web')->group(base_path('routes/web.php'));
        // Роуты кабинета клиента
        Route::middleware(['web', 'auth', 'cabinet'])
            ->prefix('cabinet')
            ->name('cabinet.')
            ->group(base_path('routes/cabinet.php'));

        // Роуты супер-админа
        Route::middleware('web')
            ->prefix('cpanel')
            ->name('cpanel.')
            ->group(base_path('routes/cpanel.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'cabinet' => \App\Http\Middleware\CabinetMiddleware::class,
        ]);
    })
    // проверка активногго тарифа в мидлваре
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'subscription' => \App\Http\Middleware\CheckActiveSubscription::class,
        ]);
    })

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'v1/track', // Исключаем эндпоинт трекинга
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

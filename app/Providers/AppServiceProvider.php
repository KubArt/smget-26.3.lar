<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        view()->composer('cabinet.layouts.partials.header', function ($view) {
            $user = auth()->user();
            if ($user) {
                // ID всех сайтов пользователя
                $siteIds = $user->sites()->pluck('sites.id')->toArray();

                // Собираем непрочитанные уведомления пользователя И его сайтов
                $allNotifications = \Illuminate\Notifications\DatabaseNotification::with('notifiable') // Жадная загрузка
                ->where(function($query) use ($user, $siteIds) {
                    $query->where(function($q) use ($user) {
                        $q->where('notifiable_type', get_class($user))
                            ->where('notifiable_id', $user->id);
                    })->orWhere(function($q) use ($siteIds) {
                        $q->where('notifiable_type', \App\Models\Site::class)
                            ->whereIn('notifiable_id', $siteIds);
                    });
                })
                    ->unread()
                    ->latest()
                    ->take(10) // Ограничим количество в хедере
                    ->get();
                $view->with('headerNotifications', $allNotifications);
            }
        });
    }
}

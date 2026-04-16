<?php

namespace App\Providers;

use App\Models\NotificationReadState;
use Illuminate\Notifications\DatabaseNotification;
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

        // Добавляем связь динамически
        DatabaseNotification::resolveRelationUsing('readStates', function ($notificationModel) {
            return $notificationModel->hasMany(NotificationReadState::class, 'notification_id', 'id');
        });

        // Автоматическое создание кабинета при регистрации пользователя.
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        view()->composer('cabinet.layouts.partials.header', function ($view) {
            $user = auth()->user();
            if ($user) {
                $siteIds = $user->sites()->pluck('sites.id')->toArray();

                $allNotifications = \Illuminate\Notifications\DatabaseNotification::with('notifiable')
                    ->where(function($query) use ($user, $siteIds) {
                        // Личные непрочитанные
                        $query->where(function($q) use ($user) {
                            $q->where('notifiable_type', get_class($user))
                                ->where('notifiable_id', $user->id)
                                ->whereNull('read_at');
                        })
                            // ИЛИ сообщения сайтов, которые ЭТОТ юзер еще не читал
                            ->orWhere(function($q) use ($siteIds, $user) {
                                $q->where('notifiable_type', \App\Models\Site::class)
                                    ->whereIn('notifiable_id', $siteIds)
                                    ->whereDoesntHave('readStates', function($subQuery) use ($user) {
                                        $subQuery->where('user_id', $user->id);
                                    });
                            });
                    })
                    ->latest()
                    ->get();

                $view->with('headerNotifications', $allNotifications);
            }
        });

        // Выводим баланс пользователя в шапке каинета
        view()->composer('cabinet.layouts.partials.header', function ($view) {
            if (auth()->check()) {
                // Мы просто обращаемся к атрибуту, который создали в модели User
                $view->with('userBalance', auth()->user()->balance);
            }
        });
    }
}

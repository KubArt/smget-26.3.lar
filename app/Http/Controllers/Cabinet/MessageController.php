<?php


namespace App\Http\Controllers\Cabinet;


class MessageController extends BaseCabinetController
{
    /**
     * Список всех уведомлений пользователя
     */
    public function index()
    {
        $siteIds = auth()->user()->sites()->pluck('sites.id')->toArray();

        $notifications = \Illuminate\Notifications\DatabaseNotification::with('notifiable')
            ->where(function($query) use ($siteIds) {
                $query->where(function($q) {
                    $q->where('notifiable_type', \App\Models\User::class)
                        ->where('notifiable_id', auth()->id());
                })->orWhere(function($q) use ($siteIds) {
                    $q->where('notifiable_type', \App\Models\Site::class)
                        ->whereIn('notifiable_id', $siteIds);
                });
            })
            ->latest()
            ->paginate(15);

        return view('cabinet.messages.index', compact('notifications'));
    }

    public function show($id)
    {
        $siteIds = auth()->user()->sites()->pluck('sites.id')->toArray();

        // Основное уведомление
        $notification = \Illuminate\Notifications\DatabaseNotification::where(function($query) use ($siteIds) {
            $query->where(function($q) {
                $q->where('notifiable_type', \App\Models\User::class)
                    ->where('notifiable_id', auth()->id());
            })->orWhere(function($q) use ($siteIds) {
                $q->where('notifiable_type', \App\Models\Site::class)
                    ->whereIn('notifiable_id', $siteIds);
            });
        })->findOrFail($id);

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        // Список непрочитанных для боковой колонки (за исключением текущего)
        $unreadList = \Illuminate\Notifications\DatabaseNotification::where('id', '!=', $id)
            ->where(function($query) use ($siteIds) {
                $query->where(function($q) {
                    $q->where('notifiable_type', \App\Models\User::class)
                        ->where('notifiable_id', auth()->id());
                })->orWhere(function($q) use ($siteIds) {
                    $q->where('notifiable_type', \App\Models\Site::class)
                        ->whereIn('notifiable_id', $siteIds);
                });
            })
            //->unread()
            ->latest()
            ->take(10)
            ->get();

        return view('cabinet.messages.show', compact('notification', 'unreadList'));
    }

    /**
     * Пометить сообщение как прочитанное
     */
    public function markAsRead($id)
    {
        // Логика пометки прочитанным
    }
}

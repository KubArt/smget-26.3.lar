<?php


namespace App\Http\Controllers\Cabinet;


use App\Models\NotificationReadState;
use App\Models\Site;
use App\Models\User;

class MessageController extends BaseCabinetController
{
    /**
     * Список всех уведомлений пользователя
     */
    public function index()
    {
        $user = auth()->user();
        $siteIds = $user->sites()->pluck('sites.id')->toArray();

        $notifications = \Illuminate\Notifications\DatabaseNotification::with('notifiable')
            ->leftJoin('notification_read_states', function($join) use ($user) {
                $join->on('notifications.id', '=', 'notification_read_states.notification_id')
                    ->where('notification_read_states.user_id', '=', $user->id);
            })
            ->where(function($query) use ($user, $siteIds) {
                $query->where(function($q) use ($user) {
                    $q->where('notifiable_type', \App\Models\User::class)
                        ->where('notifiable_id', $user->id);
                })->orWhere(function($q) use ($siteIds) {
                    $q->where('notifiable_type', \App\Models\Site::class)
                        ->whereIn('notifiable_id', $siteIds);
                });
            })
            // Выбираем все поля уведомлений и флаг прочтения из нашей таблицы
            ->select('notifications.*', 'notification_read_states.id as is_custom_read')
            ->latest('notifications.created_at')
            ->paginate(15);

        return view('cabinet.messages.index', compact('notifications'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $siteIds = $user->sites()->pluck('sites.id')->toArray();

        // 1. Поиск основного уведомления с загрузкой объекта, к которому оно относится
        $notification = \Illuminate\Notifications\DatabaseNotification::with('notifiable')
            ->where(function($query) use ($user, $siteIds) {
                $query->where(function($q) use ($user) {
                    $q->where('notifiable_type', get_class($user))
                        ->where('notifiable_id', $user->id);
                })->orWhere(function($q) use ($siteIds) {
                    $q->where('notifiable_type', \App\Models\Site::class)
                        ->whereIn('notifiable_id', $siteIds);
                });
            })->findOrFail($id);

        // 2. Логика пометки прочтения
        if ($notification->notifiable_type === \App\Models\Site::class) {
            // Фиксируем прочтение конкретным пользователем для уведомления сайта
            \App\Models\NotificationReadState::firstOrCreate([
                'user_id' => $user->id,
                'notification_id' => $notification->id
            ]);
        } else {
            // Стандартное прочтение для личных уведомлений
            if ($notification->unread()) {
                $notification->markAsRead();
            }
        }

        // 3. Список непрочитанных для боковой колонки (сложная логика)
        $unreadList = \Illuminate\Notifications\DatabaseNotification::with('notifiable')
            ->where('id', '!=', $id) // Исключаем текущее
            ->where(function($query) use ($user, $siteIds) {
                // Личные непрочитанные (read_at IS NULL)
                $query->where(function($q) use ($user) {
                    $q->where('notifiable_type', get_class($user))
                        ->where('notifiable_id', $user->id)
                        ->whereNull('read_at');
                })
                    // ИЛИ непрочитанные сообщения сайтов (нет записи в notification_read_states)
                    ->orWhere(function($q) use ($siteIds) {
                        $q->where('notifiable_type', \App\Models\Site::class)
                            ->whereIn('notifiable_id', $siteIds)
                            ->whereDoesntHave('readStates', function($sub) {
                                $sub->where('user_id', auth()->id());
                            });
                    });
            })
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

    public function destroy($id)
    {
        $siteIds = auth()->user()->sites()->pluck('sites.id')->toArray();
        $notification = \Illuminate\Notifications\DatabaseNotification::where(...)
            ->findOrFail($id);

        // Если это личное сообщение — удаляем совсем.
        // Если это сообщение сайта — тут вопрос политики:
        // можно удалить для всех, либо просто скрыть для этого юзера.
        // Удалим для всех, так как это действие владельца/менеджера:
        $notification->delete();

        return redirect()->route('cabinet.messages.index')
            ->with('success', 'Сообщение удалено.');
    }

}

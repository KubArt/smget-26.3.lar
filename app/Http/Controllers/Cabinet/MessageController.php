<?php


namespace App\Http\Controllers\Cabinet;


class MessageController extends BaseCabinetController
{
    /**
     * Список всех уведомлений пользователя
     */
    public function index()
    {
        // Получаем все уведомления текущего пользователя с пагинацией
        // По 10 сообщений на страницу
        $notifications = auth()->user()->notifications()->latest()->paginate(10);

        return view('cabinet.messages.index', compact('notifications'));
    }

    public function show($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Помечаем как прочитанное, если оно еще не прочитано
        if ($notification->unread()) {
            $notification->markAsRead();
        }

        return view('cabinet.messages.show', compact('notification'));
    }
    /**
     * Пометить сообщение как прочитанное
     */
    public function markAsRead($id)
    {
        // Логика пометки прочитанным
    }
}

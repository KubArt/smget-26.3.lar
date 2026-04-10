<?php


namespace App\Http\Controllers\Cabinet;


class MessageController extends BaseCabinetController
{
    /**
     * Список всех уведомлений пользователя
     */
    public function index()
    {
        return view('cabinet.messages.index');
    }

    /**
     * Просмотр конкретного системного сообщения
     */
    public function show($id)
    {
        return view('cabinet.messages.show');
    }

    /**
     * Пометить сообщение как прочитанное
     */
    public function markAsRead($id)
    {
        // Логика пометки прочитанным
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(1);

        if (!$user) {
            $this->command->error("Пользователь с ID 1 не найден!");
            return;
        }

        $notifications = [
            [
                'title' => 'Добро пожаловать в SMGET!',
                'message' => 'Ваш аккаунт успешно активирован. Теперь вы можете добавить свой первый сайт и начать собирать лиды.',
                'type' => 'success',
                'icon' => 'fa-rocket',
                'importance' => 'normal',
            ],
            [
                'title' => 'Критическое обновление системы',
                'message' => 'Мы обновили протоколы безопасности. Пожалуйста, проверьте настройки доступа в разделе API.',
                'type' => 'danger',
                'icon' => 'fa-exclamation-triangle',
                'importance' => 'high',
            ],
            [
                'title' => 'Новая функция: Виджет "Обратный звонок"',
                'message' => 'Теперь вам доступен новый виджет для увеличения конверсии. Попробуйте настроить его прямо сейчас.',
                'type' => 'info',
                'icon' => 'fa-magic',
                'importance' => 'low',
            ],
            [
                'title' => 'Баланс пополнен',
                'message' => 'Ваш баланс успешно пополнен на 5000 руб. Спасибо, что выбираете SMGET!',
                'type' => 'primary',
                'icon' => 'fa-wallet',
                'importance' => 'normal',
            ],
        ];

        foreach ($notifications as $data) {
            DB::table('notifications')->insert([
                'id' => Str::uuid(),
                'type' => 'App\Notifications\SystemMessage', // Класс-заглушка
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => json_encode($data),
                'created_at' => now()->subMinutes(rand(1, 1000)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Уведомления для пользователя ID 1 успешно созданы.");
    }
}

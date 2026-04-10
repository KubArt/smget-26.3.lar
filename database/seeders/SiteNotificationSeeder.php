<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiteNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Берем первый попавшийся сайт
        $site = Site::first();

        if ($site) {
            // Создаем несколько тестовых уведомлений напрямую в таблицу
            $notifications = [
                [
                    'title' => 'Проверка кода пройдена',
                    'icon' => 'fa fa-check-circle',
                    'type' => 'success',
                    'url' => route('cabinet.sites.show', $site->id),
                ],
                [
                    'title' => 'Обнаружена ошибка в виджете',
                    'icon' => 'fa fa-exclamation-triangle',
                    'type' => 'warning',
                    'url' => route('cabinet.sites.notifications', $site->id),
                ],
                [
                    'title' => 'Новый лид с формы',
                    'icon' => 'fa fa-user-plus',
                    'type' => 'info',
                    'url' => '#',
                ]
            ];

            foreach ($notifications as $data) {
                DB::table('notifications')->insert([
                    'id' => Str::uuid(),
                    'type' => 'App\Notifications\SystemNotification', // Или ваш класс
                    'notifiable_type' => 'App\Models\Site',
                    'notifiable_id' => $site->id,
                    'data' => json_encode($data),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->command->info('Уведомления для сайта ' . $site->domain . ' успешно созданы.');
        } else {
            $this->command->error('Сайты не найдены. Сначала запустите SiteSeeder.');
        }
    }
}

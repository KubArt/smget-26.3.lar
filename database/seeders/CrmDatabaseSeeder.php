<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Crm\Client;
use App\Models\Crm\Lead;
use App\Models\Crm\FunnelStage;
use App\Models\Crm\LeadNote;
use App\Models\Crm\ClientNote;
use App\Models\Crm\LeadTask;
use App\Models\User;
use Carbon\Carbon;

class CrmDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $siteId = 2;
        $widgetId = 4;

        // 1. Получаем или создаем тестового юзера (менеджера)
        $user = User::first() ?? User::factory()->create(['name' => 'Admin Manager']);

        // 2. Создаем базовые стадии воронки для этого сайта
        $stages = [
            ['name' => 'Новый', 'code' => 'new', 'sort' => 1, 'color' => '#3498db'],
            ['name' => 'В работе', 'code' => 'process', 'sort' => 2, 'color' => '#f1c40f'],
            ['name' => 'Записан', 'code' => 'appointed', 'sort' => 3, 'color' => '#2ecc71'],
            ['name' => 'Отказ', 'code' => 'lost', 'sort' => 4, 'color' => '#e74c3c'],
        ];

        foreach ($stages as $st) {
            FunnelStage::updateOrCreate(
                ['site_id' => $siteId, 'code' => $st['code']],
                ['name' => $st['name'], 'sort_order' => $st['sort'], 'color' => $st['color'], 'is_system' => true]
            );
        }

        // 3. Создаем 3 клиентов
        $clients = [];
        $names = ['Иванов Иван', 'Петрова Мария', 'Сидоров Алексей'];
        foreach ($names as $index => $name) {
            $clients[] = Client::create([
                'site_id' => $siteId,
                'phone' => '+7900111223' . $index,
                'name' => explode(' ', $name)[1],
                'last_name' => explode(' ', $name)[0],
                'email' => "client{$index}@example.com",
            ]);
        }

        // 4. Создаем 10 лидов с разным распределением
        for ($i = 1; $i <= 10; $i++) {
            $client = $clients[array_rand($clients)];

            $lead = Lead::create([
                'site_id' => $siteId,
                'client_id' => $client->id,
                'widget_id' => $widgetId,
                'phone' => $client->phone,
                'email' => $client->email,
                'status' => $i < 4 ? 'new' : ($i < 8 ? 'process' : 'appointed'),
                'assigned_to' => $user->id,
                'form_data' => [
                    'question' => "Тестовый вопрос №{$i}",
                    'service' => 'Консультация стоматолога'
                ],
                'utm_source' => $i % 2 == 0 ? 'yandex' : 'google',
                'utm_medium' => 'cpc',
                'utm_campaign' => 'dental_implants_2026',
                'page_url' => 'https://smile-center.ru/services/implants',
                'vaucher_name' => $i > 8 ? 'Скидка 10%' : '',
                'vaucher_code' => $i > 8 ? 'SALE10' : null,
                'created_at' => Carbon::now()->subDays(10 - $i),
            ]);

            // Добавляем заметку к каждому лиду
            LeadNote::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'note' => "Автоматическая заметка по лиду №{$i}. Клиент интересовался стоимостью.",
            ]);

            // Для каждого 3-го лида создаем задачу-напоминание
            if ($i % 3 == 0) {
                LeadTask::create([
                    'lead_id' => $lead->id,
                    'assigned_to' => $user->id,
                    'created_by' => $user->id,
                    'title' => "Перезвонить по лиду #{$lead->id}",
                    'description' => "Уточнить время записи на прием",
                    'due_date' => Carbon::now()->addDays(2),
                    'reminder_at' => Carbon::now()->addDays(2)->subHours(1),
                    'status' => 'pending',
                    'priority' => 'high',
                ]);
            }
        }

        // 5. Добавляем общую заметку клиенту
        foreach ($clients as $client) {
            ClientNote::create([
                'client_id' => $client->id,
                'user_id' => $user->id,
                'note' => "Постоянный пациент. Предпочитает запись на утренние часы.",
            ]);
        }
    }
}

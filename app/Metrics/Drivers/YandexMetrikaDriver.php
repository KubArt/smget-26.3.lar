<?php
// app/Metrics/Drivers/YandexMetrikaDriver.php

namespace App\Metrics\Drivers;

use App\Metrics\Contracts\MetricDriverInterface;
use Illuminate\Support\Facades\Http;

class YandexMetrikaDriver implements MetricDriverInterface
{
    protected $counterId;
    protected $token;

    public function __construct(array $settings = [])  // ✅ Добавили значение по умолчанию
    {
        $this->counterId = $settings['counter_id'] ?? null;
        $this->token = $settings['token'] ?? null;
    }

    public function sendEvent(string $event, array $params): void
    {
        // ✅ Добавили проверку наличия необходимых данных
        if (!$this->counterId || !$this->token) {
            \Log::warning('YandexMetrika: missing credentials', [
                'has_counter_id' => !empty($this->counterId),
                'has_token' => !empty($this->token)
            ]);
            return;
        }

        if (!isset($params['client_id'])) {
            \Log::warning('YandexMetrika: missing client_id');
            return;
        }

        try {
            // Отправка через Offline Conversions API
            $response = Http::withToken($this->token)
                ->post("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/offline_conversions/upload", [
                    'conversions' => [
                        [
                            'client_id' => $params['client_id'],
                            'target' => $event,
                            'date_time' => date('Y-m-d H:i:s'),
                        ]
                    ]
                ]);

            if (!$response->successful()) {
                \Log::error('YandexMetrika API error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('YandexMetrika send error: ' . $e->getMessage());
        }
    }

    public function syncGoals(array $goals): void
    {
        if (!$this->counterId || !$this->token) {
            \Log::warning('YandexMetrika: missing credentials for goals sync');
            return;
        }

        try {
            // Получаем существующие цели
            $response = Http::withToken($this->token)
                ->get("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/goals");

            if ($response->successful()) {
                $existingGoals = collect($response->json()['goals'] ?? []);

                // Создаем новые цели
                foreach ($goals as $goal) {
                    $goalName = $goal['name'] ?? $goal['event'];

                    // Проверяем, существует ли уже такая цель
                    if (!$existingGoals->contains('name', $goalName)) {
                        Http::withToken($this->token)
                            ->post("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/goals", [
                                'name' => $goalName,
                                'type' => 'event',
                                'conditions' => [
                                    ['type' => 'event', 'value' => $goal['event']]
                                ]
                            ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('YandexMetrika sync goals error: ' . $e->getMessage());
        }
    }

    /**
     * Проверка соединения
     */
    public function test(): array
    {
        if (!$this->counterId || !$this->token) {
            return [
                'success' => false,
                'message' => 'Не заполнены ID счетчика или Token'
            ];
        }

        try {
            $response = Http::withToken($this->token)
                ->get("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Подключение успешно! Счетчик найден.'
                ];
            }

            return [
                'success' => false,
                'message' => 'Ошибка: ' . ($response->json()['message'] ?? 'Неверные данные')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка подключения: ' . $e->getMessage()
            ];
        }
    }
}


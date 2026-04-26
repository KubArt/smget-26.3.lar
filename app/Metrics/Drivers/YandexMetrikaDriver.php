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

    /*
        public function syncGoals(array $goals): void
        {
            if (!$this->counterId || !$this->token) {
                \Log::warning('YandexMetrika: missing credentials for goals sync');
                return;
            }

            try {
                // 1. Получаем существующие цели, чтобы не плодить дубликаты
                $response = Http::withToken($this->token)
                    ->get("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/goals");

                if (!$response->successful()) {
                    \Log::error('YandexMetrika: Failed to fetch existing goals', ['body' => $response->body()]);
                    return;
                }

                $existingGoals = collect($response->json()['goals'] ?? []);

                foreach ($goals as $goal) {
                    $goalName = $goal['name'] ?? $goal['event'];

                    // Яндекс не дает создать цель с тем же именем или тем же идентификатором условия
                    $exists = $existingGoals->contains(function ($existing) use ($goalName, $goal) {
                        return $existing['name'] === $goalName ||
                               (isset($existing['conditions'][0]['url']) && $existing['conditions'][0]['url'] === $goal['event']);
                    });

                    if (!$exists) {
                        // 2. Отправляем запрос согласно спецификации API
                        Http::withToken($this->token)
                            ->post("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/goals", [
                                'goal' => [ // ОБЯЗАТЕЛЬНО: обертка в goal
                                    'name' => $goalName,
                                    'type' => 'action', // Для JS-событий тип всегда 'action'
                                    'is_retargeting' => 0,
                                    'conditions' => [
                                        [
                                            'type' => 'exact', // 'exact' для типа 'action' означает идентификатор цели
                                            'url' => $goal['event'] // Сам идентификатор передается в поле 'url'
                                        ]
                                    ]
                                ]
                            ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('YandexMetrika sync goals exception: ' . $e->getMessage());
            }
        }
    //*/
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


    /**
     *
     */
    /**
     * Проверка и обновление токена
     */
    protected function refreshTokenIfNeeded(array $settings): array
    {
        $expiresAt = $settings['token_expires_at'] ?? null;

        if ($expiresAt && now()->diffInMinutes($expiresAt) < 5) {
            // Токен скоро истечет - обновляем
            $response = Http::asForm()->post('https://oauth.yandex.ru/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $settings['refresh_token'],
                'client_id' => config('metrics.available.yandex-metrika.oauth.client_id'),
                'client_secret' => config('metrics.available.yandex-metrika.oauth.client_secret'),
            ]);

            if ($response->successful()) {
                $newToken = $response->json();
                $settings['access_token'] = $newToken['access_token'];
                $settings['token_expires_at'] = now()->addSeconds($newToken['expires_in']);

                // Сохраняем обновленный токен в БД
                $siteMetric = \App\Models\Metrics\SiteMetric::where('type', 'yandex-metrika')
                    ->whereJsonContains('settings->access_token', $this->accessToken)
                    ->first();

                if ($siteMetric) {
                    $siteMetric->update(['settings' => $settings]);
                    $this->accessToken = $settings['access_token'];
                }
            }
        }

        return $settings;
    }

    public function createGoal(array $goalConfig): ?string
    {
        if (!$this->counterId || !$this->accessToken) {
            return null;
        }

        $response = Http::withToken($this->accessToken)
            ->post("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/goals", [
                'name' => $goalConfig['name'],
                'type' => $goalConfig['event'] ?? 'reachGoal',
                'conditions' => $goalConfig['conditions'] ?? [],
                'is_retargeting' => false
            ]);

        if ($response->successful()) {
            return (string) $response->json()['goal']['id'];
        }

        \Log::error('YandexMetrika create goal error', [
            'response' => $response->body()
        ]);

        return null;
    }

    public function sendGoalEvent($goalId, array $eventData): void
    {
        if (!$this->counterId || !$this->accessToken) {
            return;
        }

        Http::withToken($this->accessToken)
            ->post("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/offline_conversions/upload", [
                'conversions' => [[
                    'goal_id' => (int) $goalId,
                    'client_id' => $eventData['client_id'] ?? null,
                    'date_time' => now()->toDateTimeString(),
                    'price' => $eventData['price'] ?? null,
                    'currency' => 'RUB'
                ]]
            ]);
    }

    public function deleteGoal($goalId): void
    {
        if (!$this->counterId || !$this->accessToken) {
            return;
        }

        Http::withToken($this->accessToken)
            ->delete("https://api-metrika.yandex.net/management/v1/counter/{$this->counterId}/goal/{$goalId}");
    }

}


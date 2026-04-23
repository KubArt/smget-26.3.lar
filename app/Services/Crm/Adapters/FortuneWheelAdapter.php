<?php

namespace App\Services\Crm\Adapters;

use App\Models\Crm\PrizeAttempt;
use App\Models\Crm\Prize;
use App\Models\Widget;
use Illuminate\Support\Str;

class FortuneWheelAdapter implements LeadAdapterInterface
{
    public function parse(array $data): array
    {
        $widgetId = $data['widget_id'] ?? null;
        $userContact = $data['contact'] ?? $data['phone'] ?? $data['email'] ?? null;

        // 1. Проверяем обязательные поля
        if (!$widgetId) {
            return $this->error('Widget ID is required', 'WIDGET_REQUIRED');
        }

        if (!$userContact) {
            return $this->error('Contact is required', 'CONTACT_REQUIRED');
        }

        // 2. Получаем виджет
        $widget = Widget::with('widgetType')->find($widgetId);
        if (!$widget || $widget->widgetType->slug !== 'fortune-wheel') {
            return $this->error('Invalid widget', 'INVALID_WIDGET');
        }

        $siteId = $widget->site_id;
        $settings = $widget->settings;
        $segments = $settings['wheel']['segments'] ?? [];

        // Фильтруем только активные сегменты
        $activeSegments = array_filter($segments, function($segment) {
            return ($segment['enabled'] ?? true) === true;
        });

        if (empty($activeSegments)) {
            return $this->error('No active prizes available', 'NO_ACTIVE_PRIZES');
        }

        // 3. Проверяем лимит попыток
        /*
        $maxAttempts = $settings['limits']['spins_per_user'] ?? 3;
        $attemptsCount = PrizeAttempt::getAttemptsCount($siteId, $userContact, $widgetId, 24);

        if ($maxAttempts > 0 && $attemptsCount >= $maxAttempts) {
            PrizeAttempt::log($siteId, $userContact, null, $widgetId, null, false, 'MAX_ATTEMPTS');
            return $this->error('Maximum attempts reached', 'MAX_ATTEMPTS_REACHED', 429, [
                'attempts_used' => $attemptsCount,
                'attempts_limit' => $maxAttempts
            ]);
        }
        //*/

        // 4. ВЫБИРАЕМ СЛУЧАЙНЫЙ ПРИЗ (с равной вероятностью)
        $prizeIndex = array_rand($activeSegments);
        $selectedSegment = $activeSegments[$prizeIndex];
        $originalIndex = array_search($selectedSegment, $segments, true);

        // 5. Генерируем уникальный промокод
        $uniqueCode = $this->generateUniqueCode($selectedSegment['value'], $widgetId);
        $expiryDays = (int) $selectedSegment['expiry_days'] ?? 7;
        $expiresAt = $expiryDays > 0 ? now()->addDays($expiryDays) : null;

        // 6. СОЗДАЕМ ПРИЗ В БД
        $prize = Prize::create([
            'site_id' => $siteId,
            'widget_id' => $widgetId,
            'code' => $uniqueCode,
            'name' => $selectedSegment['label'],
            'description' => $selectedSegment['description'] ?? null,
            'type' => 'discount',
            'meta' => [
                'original_code' => $selectedSegment['value'],
                'segment_index' => $originalIndex,
                'segment_data' => $selectedSegment
            ],
            'expires_at' => $expiresAt,
            'is_active' => true,
            'is_limited' => $expiryDays > 0,
        ]);

        // 7. Логируем успешную попытку
        PrizeAttempt::log($siteId, $userContact, $selectedSegment['value'], $widgetId, $prize->id, true);

        // 8. РАССЧИТЫВАЕМ УГОЛ ОСТАНОВКИ ДЛЯ ВИДЖЕТА
        $segmentCount = count($segments);
        $segmentDeg = 360 / $segmentCount;
        // Угол остановки: указатель вверху (12 часов)
        // Нужно, чтобы указатель попал в середину выигрышного сегмента
        $targetRotation = (360 - ($originalIndex * $segmentDeg)) - ($segmentDeg / 2);

        // 9. Формируем сообщение для пользователя
        $messageTemplate = $settings['form']['success_message'] ?? 'Ваш купон: {CODE}';
        $successMessage = str_replace('{CODE}', $uniqueCode, $messageTemplate);
        $successMessage = str_replace('{NAME}', $selectedSegment['label'], $successMessage);

        // 10. Обработка контакта
        $phone = null;
        $email = null;

        if (filter_var($userContact, FILTER_VALIDATE_EMAIL)) {
            $email = $userContact;
        } else {
            $phone = preg_replace('/[^\d+]/', '', $userContact);
        }

        return [
            // Данные для лида
            'site_id' => $siteId,
            'widget_id' => $widgetId,
            'contact' => $userContact,
            'phone' => $phone,
            'email' => $email,
            'name' => $data['name'] ?? 'Участник колеса фортуны',

            // UTM метки
            'utm_source'   => $data['utm_source'] ?? null,
            'utm_medium'   => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term'     => $data['utm_term'] ?? null,
            'utm_content'  => $data['utm_content'] ?? null,
            'utm_referrer' => $data['utm_referrer'] ?? null,

            'page_url'  => $data['page_url'] ?? null,
            'form_data' => $data,

            // Данные для виджета (вращение)
            'extra' => [
                'target_index' => $originalIndex
            ],
            'widget_data' => [
                'target_rotation' => $targetRotation,
                'prize_index' => $originalIndex,
                'prize' => [
                    'code' => $uniqueCode,
                    'name' => $selectedSegment['label'],
                    'description' => $selectedSegment['description'] ?? null,
                    'expires_at' => $expiresAt?->toISOString(),
                ],
                'message' => $successMessage,
            ],

            'prize_data' => [
                'code' => $selectedSegment['value'],
                'name' => $selectedSegment['label'],
                'description' => $selectedSegment['description'] ?? null,
                'expiry_days' => $expiryDays,
            ],

            'message' => $successMessage,
        ];
    }

    protected function generateUniqueCode(string $baseCode, int $widgetId): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $baseCode));
        if (strlen($prefix) < 3) {
            $prefix = 'PROMO';
        }

        do {
            $suffix = strtoupper(Str::random(6));
            $code = $prefix . '_' . $suffix;
        } while (Prize::where('code', $code)->exists());

        return $code;
    }

    protected function error(string $message, string $code, int $httpCode = 422, array $extra = []): array
    {
        return array_merge([
            'error' => $message,
            'code' => $code,
            'http_code' => $httpCode,
        ], $extra);
    }
}

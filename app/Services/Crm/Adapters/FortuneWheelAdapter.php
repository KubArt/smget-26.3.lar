<?php

namespace App\Services\Crm\Adapters;

use App\Models\Crm\PrizeAttempt;
use App\Models\Widget;
use Illuminate\Support\Str;

class FortuneWheelAdapter implements LeadAdapterInterface
{
    public function parse(array $data): array
    {
        $widgetId = $data['widget_id'] ?? null;
        $prizeCode = $data['prize_code'] ?? null;
        $prizeLabel = $data['prize_label'] ?? null;
        $userContact = $data['contact'] ?? $data['phone'] ?? $data['email'] ?? null;

        // 1. Проверяем обязательные поля
        if (!$widgetId) {
            return $this->error('Widget ID is required', 'WIDGET_REQUIRED');
        }

        if (!$userContact) {
            return $this->error('Contact is required', 'CONTACT_REQUIRED');
        }

        if (!$prizeCode) {
            return $this->error('Prize code is required', 'PRIZE_CODE_REQUIRED');
        }

        // 2. Получаем виджет
        $widget = Widget::with('widgetType')->find($widgetId);
        if (!$widget || $widget->widgetType->slug !== 'fortune-wheel') {
            return $this->error('Invalid widget', 'INVALID_WIDGET');
        }

        $siteId = $widget->site_id;
        $settings = $widget->settings;
        $segments = $settings['wheel']['segments'] ?? [];

        // 3. Проверяем сегмент
        $validSegment = null;
        foreach ($segments as $segment) {
            if (($segment['enabled'] ?? true) && $segment['value'] === $prizeCode) {
                $validSegment = $segment;
                break;
            }
        }

        if (!$validSegment) {
            PrizeAttempt::log($siteId, $userContact, $prizeCode, $widgetId, null, false, 'INVALID_SEGMENT');
            return $this->error('Invalid prize segment', 'INVALID_SEGMENT');
        }

        // 4. Проверяем лимит попыток
        $maxAttempts = $settings['limits']['spins_per_user'] ?? 3;
        $attemptsCount = PrizeAttempt::getAttemptsCount($siteId, $userContact, $widgetId, 24);

        if ($maxAttempts > 0 && $attemptsCount >= $maxAttempts) {
            PrizeAttempt::log($siteId, $userContact, $prizeCode, $widgetId, null, false, 'MAX_ATTEMPTS');

            return $this->error('Maximum attempts reached', 'MAX_ATTEMPTS_REACHED', 429, [
                'attempts_used' => $attemptsCount,
                'attempts_limit' => $maxAttempts
            ]);
        }

        // 5. Возвращаем данные (лид + приз)
        return [
            // Для лида
            'site_id' => $siteId,
            'widget_id' => $widgetId,
            'contact' => $userContact,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'name' => $data['name'] ?? 'Участник колеса фортуны',

            // UTM-метки
            'utm_source'   => $data['utm_source'] ?? null,
            'utm_medium'   => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term'     => $data['utm_term'] ?? null,
            'utm_content'  => $data['utm_content'] ?? null,
            'utm_referrer' => $data['utm_referrer'] ?? null,

            'page_url'     => $data['page_url'] ?? null,
            'form_data'    => $data,

            // Данные для приза
            'prize_data' => [
                'code' => $prizeCode,
                'name' => $validSegment['label'],
                'description' => $validSegment['description'] ?? null,
                'expiry_days' => $validSegment['expiry_days'] ?? 30,
                'segment_data' => $validSegment
            ],

            // Шаблон сообщения из настроек виджета
            'success_message_template' => $settings['form']['success_message'] ?? 'Ваш купон: {CODE}',
        ];
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

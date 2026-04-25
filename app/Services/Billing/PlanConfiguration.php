<?php

namespace App\Services\Billing;

class PlanConfiguration
{
    // Константы для названий ключей, чтобы не ошибиться в будущем
    public const LEADS_LIMIT = 'leads_limit';
    public const WIDGETS_LIMIT = 'widgets_limit';
    public const SHOWS_LIMIT = 'shows_limit';

    public const INTEGRATIONS = 'integrations'; // Массив разрешенных сервисов
    public const FEATURES = 'features';         // Массив доп. функций (tg_bot, export и т.д.)

    /**
     * Базовые лимиты (Бесплатный уровень)
     */
    public static function getDefaultLimit(): array
    {
        return [
            self::LEADS_LIMIT => 5,
            self::WIDGETS_LIMIT => 1,
            self::SHOWS_LIMIT => 300,
            self::INTEGRATIONS => ['internal'], // Только внутренняя база
            self::FEATURES => [
            //    'telegram_notifications' => false,
            //    'email_notifications' => true,
            //    'export_leads' => false,
            ],
        ];
    }

    /**
     * Описание доступных интеграций (для удобства расширения)
     */
    public static function getAvailableIntegrations(): array
    {
        return [
            'internal' => 'Внутренняя CRM',
            'tilda' => 'Интеграция с Tilda',
            //'bitrix24' => 'Битрикс24',
            //'amocrm' => 'amoCRM',
            'webhook' => 'Webhooks',
        ];
    }
}

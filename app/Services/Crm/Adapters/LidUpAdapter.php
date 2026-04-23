<?php

namespace App\Services\Crm\Adapters;

use App\Models\Crm\PrizeAttempt;
use App\Models\Widget;
use Illuminate\Support\Str;

/**
 * Адаптер для виджета "LidUp Popup"
 *
 * ============================================================================
 * НАЗНАЧЕНИЕ
 * ============================================================================
 *
 * Преобразует входящие данные от виджета в структурированный массив для контроллера.
 * Выполняет валидацию, проверку обязательных полей.
 * Поддерживает динамические поля формы и выдачу бонуса.
 * НЕ создает записи в БД - только готовит данные.
 *
 * ============================================================================
 * ВХОДНЫЕ ДАННЫЕ (ожидает от виджета)
 * ============================================================================
 *
 * Обязательные:
 *   - widget_id    - ID виджета в системе
 *   - phone или email - контакт пользователя (хотя бы одно)
 *
 * Опциональные:
 *   - name         - имя пользователя
 *   - [любые другие поля] - дополнительные поля формы
 *   - page_url     - URL страницы
 *   - utm_*        - UTM метки
 *
 * ============================================================================
 * ВЫХОДНЫЕ ДАННЫЕ (возвращает контроллеру)
 * ============================================================================
 *
 * УСПЕХ:
 *   [
 *     'site_id' => int,
 *     'widget_id' => int,
 *     'contact' => string,
 *     'phone' => string|null,
 *     'email' => string|null,
 *     'name' => string,
 *     'form_fields' => array,
 *     'utm_*' => string|null,
 *     'page_url' => string|null,
 *     'form_data' => array,
 *     'prize_data' => [             // данные для приза (если включен бонус)
 *       'code' => string,
 *       'code_type' => string,      // fixed, unique, random
 *       'name' => string,
 *       'description' => string|null,
 *       'expiry_days' => int,
 *     ],
 *     'message' => string           // сообщение об успехе
 *   ]
 *
 * ============================================================================
 * КОДЫ ОШИБОК
 * ============================================================================
 *
 * WIDGET_REQUIRED     - не передан widget_id
 * CONTACT_REQUIRED    - не передан контакт (телефон/email)
 * INVALID_WIDGET      - виджет не найден или не является LidUp Popup
 *
 * ============================================================================
 */
class LidUpAdapter implements LeadAdapterInterface
{
    /**
     * Парсинг и валидация входных данных
     */
    public function parse(array $data): array
    {
        // ====================================================================
        // ШАГ 1: ИЗВЛЕЧЕНИЕ И ПРОВЕРКА ОБЯЗАТЕЛЬНЫХ ПОЛЕЙ
        // ====================================================================
        $widgetId = $data['widget_id'] ?? null;
        $rawContact = $data['phone'] ?? $data['email'] ?? null;

        if (!$widgetId) {
            return $this->error('Widget ID is required', 'WIDGET_REQUIRED');
        }

        if (!$rawContact) {
            return $this->error('Contact is required (phone or email)', 'CONTACT_REQUIRED');
        }

        // ====================================================================
        // ШАГ 2: ОПРЕДЕЛЕНИЕ ТИПА КОНТАКТА И ОЧИСТКА
        // ====================================================================
        $phone = null;
        $email = null;

        if (filter_var($rawContact, FILTER_VALIDATE_EMAIL)) {
            $email = $rawContact;
        } else {
            $phone = preg_replace('/[^\d+]/', '', $rawContact);
        }

        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^\d+]/', '', $data['phone']);
        }
        if (!empty($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $email = $data['email'];
        }

        // ====================================================================
        // ШАГ 3: ПРОВЕРКА ВИДЖЕТА
        // ====================================================================
        $widget = Widget::with('widgetType')->find($widgetId);

        if (!$widget) {
            return $this->error('Widget not found', 'INVALID_WIDGET');
        }

        if ($widget->widgetType->slug !== 'lidup') {
            return $this->error('Widget is not a LidUp popup', 'INVALID_WIDGET');
        }

        $siteId = $widget->site_id;
        $settings = $widget->settings;

        // ====================================================================
        // ШАГ 4: ИЗВЛЕЧЕНИЕ ВСЕХ ПОЛЕЙ ФОРМЫ
        // ====================================================================
        $excludeFields = ['widget_id', 'page_url', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_referrer'];
        $formFields = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $excludeFields) && !empty($value)) {
                $formFields[$key] = $value;
            }
        }

        // ====================================================================
        // ШАГ 5: ПОДГОТОВКА ДАННЫХ ДЛЯ ЛИДА
        // ====================================================================
        $name = $data['name'] ?? $formFields['name'] ?? 'Аноним';
        $successMessage = $settings['success_message'] ?? 'Спасибо! Мы свяжемся с вами в ближайшее время.';

        // ====================================================================
        // ШАГ 6: ПРОВЕРКА ВЫДАЧИ БОНУСА (НОВАЯ СТРУКТУРА)
        // ====================================================================
        $prizeData = null;
        $bonus = $settings['bonus'] ?? [];

        if (($bonus['enabled'] ?? false) && !empty($bonus['code'])) {
            $prizeData = [
                'code' => $bonus['code'],
                'code_type' => $bonus['code_type'] ?? 'fixed',
                'name' => $bonus['name'] ?? 'Бонус',
                'description' => $bonus['description'] ?? null,
                'expiry_days' => (int)($bonus['expiry_days'] ?? 30),
                'user_message' => $bonus['message'] ?? null
            ];

            // Для unique и random кодов генерируем уникальный код
            if ($prizeData['code_type'] !== 'fixed') {
                $prizeData['code'] = $this->generateCode($prizeData['code_type']);
            }

            // Сообщение для пользователя (может быть из настроек или стандартное)
            $userMessage = $settings['success_message'] ?? null;
            if ($userMessage) {
                $successMessage = $userMessage;
            }
        }

        // ====================================================================
        // ШАГ 7: ВОЗВРАТ ДАННЫХ
        // ====================================================================
        $result = [
            'site_id' => $siteId,
            'widget_id' => $widgetId,
            'contact' => $rawContact,
            'phone' => $phone,
            'email' => $email,
            'name' => $name,
            'form_fields' => $formFields,
            'utm_source'   => $data['utm_source'] ?? null,
            'utm_medium'   => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'utm_term'     => $data['utm_term'] ?? null,
            'utm_content'  => $data['utm_content'] ?? null,
            'utm_referrer' => $data['utm_referrer'] ?? null,
            'page_url'  => $data['page_url'] ?? null,
            'form_data' => $data,
            'message' => $successMessage,
        ];

        if ($prizeData) {
            $result['prize_data'] = $prizeData;
        }

        return $result;
    }

    /**
     * Генерация уникального или случайного кода
     */
    protected function generateCode(string $type): string
    {
        if ($type === 'random') {
            // 6-значный числовой код
            return (string) rand(100000, 999999);
        }

        // unique: PROMO_XXXXXX
        $prefix = 'PROMO';
        $suffix = strtoupper(Str::random(6));
        return $prefix . '_' . $suffix;
    }

    /**
     * Формирование ошибки
     */
    protected function error(string $message, string $code, int $httpCode = 422, array $extra = []): array
    {
        return array_merge([
            'error' => $message,
            'code' => $code,
            'http_code' => $httpCode,
        ], $extra);
    }
}

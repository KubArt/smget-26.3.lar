<?php

namespace App\Http\Controllers\Widgets\Services;

class BehaviorManager
{
    /**
     * Доступные типы триггеров
     */
    public const TRIGGERS = ['immediate', 'delay', 'scroll', 'exit', 'click'];

    /**
     * Доступные типы частоты показа
     */
    public const FREQUENCIES = ['always', 'once_session', 'once_day', 'once_week', 'once_month', 'once_forever'];

    /**
     * Нормализация поведения (очистка и установка дефолтов)
     */
    public function normalize(array $behavior): array
    {
        $defaults = $this->getDefaults();

        $result = [];

        // Базовые поля с дефолтами
        $result['trigger_type'] = $this->normalizeTriggerType($behavior['trigger_type'] ?? $defaults['trigger_type']);
        $result['frequency'] = $this->normalizeFrequency($behavior['frequency'] ?? $defaults['frequency']);
        $result['auto_close'] = $this->normalizeInt($behavior['auto_close'] ?? $defaults['auto_close'], 0, 60);

        // Добавляем поля в зависимости от триггера
        switch ($result['trigger_type']) {
            case 'delay':
                $result['delay'] = $this->normalizeInt($behavior['delay'] ?? $defaults['delay'], 0, 30);
                break;
            case 'scroll':
                $result['scroll_percent'] = $this->normalizeInt($behavior['scroll_percent'] ?? $defaults['scroll_percent'], 0, 100);
                break;
            case 'click':
                $result['click_selector'] = $this->normalizeString($behavior['click_selector'] ?? $defaults['click_selector']);
                break;
        }

        return $result;
    }

    /**
     * Подготовка данных из запроса
     */
    public function fromRequest(array $data): array
    {
        return $this->normalize([
            'trigger_type' => $data['trigger_type'] ?? null,
            'delay' => $data['delay'] ?? null,
            'scroll_percent' => $data['scroll_percent'] ?? null,
            'click_selector' => $data['click_selector'] ?? null,
            'frequency' => $data['frequency'] ?? null,
            'auto_close' => $data['auto_close'] ?? null,
        ]);
    }

    /**
     * Получение правил валидации
     */
    public function getValidationRules(): array
    {
        return [
            'behavior.trigger_type' => 'required|in:' . implode(',', self::TRIGGERS),
            'behavior.delay' => 'required_if:behavior.trigger_type,delay|nullable|integer|min:0|max:30',
            'behavior.scroll_percent' => 'required_if:behavior.trigger_type,scroll|nullable|integer|min:0|max:100',
            'behavior.click_selector' => 'required_if:behavior.trigger_type,click|nullable|string|max:255',
            'behavior.frequency' => 'required|in:' . implode(',', self::FREQUENCIES),
            'behavior.auto_close' => 'nullable|integer|min:0|max:60',
        ];
    }

    /**
     * Получение сообщений валидации
     */
    public function getValidationMessages(): array
    {
        return [
            'behavior.trigger_type.required' => 'Выберите триггер показа.',
            'behavior.trigger_type.in' => 'Некорректное значение триггера.',
            'behavior.delay.required_if' => 'Укажите задержку появления.',
            'behavior.delay.integer' => 'Задержка должна быть числом.',
            'behavior.scroll_percent.required_if' => 'Укажите процент прокрутки.',
            'behavior.scroll_percent.integer' => 'Процент прокрутки должен быть числом.',
            'behavior.click_selector.required_if' => 'Укажите CSS селектор.',
            'behavior.frequency.required' => 'Выберите частоту показа.',
            'behavior.frequency.in' => 'Некорректное значение частоты.',
            'behavior.auto_close.integer' => 'Авто-закрытие должно быть числом.',
        ];
    }

    /**
     * Значения по умолчанию
     */
    protected function getDefaults(): array
    {
        return [
            'trigger_type' => 'immediate',
            'delay' => 3,
            'scroll_percent' => 50,
            'click_selector' => '',
            'frequency' => 'always',
            'auto_close' => 0,
        ];
    }

    protected function normalizeTriggerType(?string $value): string
    {
        return in_array($value, self::TRIGGERS) ? $value : 'immediate';
    }

    protected function normalizeFrequency(?string $value): string
    {
        return in_array($value, self::FREQUENCIES) ? $value : 'always';
    }

    protected function normalizeInt($value, int $min, int $max): int
    {
        $value = (int) $value;
        if ($value < $min) return $min;
        if ($value > $max) return $max;
        return $value;
    }

    protected function normalizeString(?string $value): string
    {
        return trim(strip_tags($value ?? ''));
    }
}

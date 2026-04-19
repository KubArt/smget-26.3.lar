<?php


namespace App\Http\Controllers\Widgets\Services;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TargetTimeManager
{
    /**
     * Нормализация правил временного таргетинга
     */
    public function normalize(array $rules): array
    {
        $normalized = [];

        foreach ($rules as $rule) {
            // Пропускаем пустые правила
            if (empty($rule['days']) && empty($rule['start_date']) && empty($rule['end_date'])) {
                continue;
            }

            $normalizedRule = [
                'type' => $this->normalizeType($rule['type'] ?? 'days'),
            ];

            // Для типа 'days' - сохраняем только дни
            if ($normalizedRule['type'] === 'days') {
                $normalizedRule['days'] = $this->normalizeDays($rule['days'] ?? []);
                // Добавляем время, только если оно указано
                if (!empty($rule['start_time']) && !empty($rule['end_time'])) {
                    $normalizedRule['start_time'] = $this->normalizeTime($rule['start_time']);
                    $normalizedRule['end_time'] = $this->normalizeTime($rule['end_time']);
                }
            }

            // Для типа 'date_range' - сохраняем даты и время
            if ($normalizedRule['type'] === 'date_range') {
                $normalizedRule['start_date'] = $this->normalizeDate($rule['start_date'] ?? null);
                $normalizedRule['end_date'] = $this->normalizeDate($rule['end_date'] ?? null);
                // Добавляем время, только если оно указано
                if (!empty($rule['start_time']) && !empty($rule['end_time'])) {
                    $normalizedRule['start_time'] = $this->normalizeTime($rule['start_time']);
                    $normalizedRule['end_time'] = $this->normalizeTime($rule['end_time']);
                }
            }

            $normalized[] = $normalizedRule;
        }

        return $normalized;
    }
    /**
     * Проверка, должен ли виджет показываться сейчас
     * @param array $rules - правила временного таргетинга
     * @param string|null $timezone - часовой пояс (если null, берем из настроек или UTC)
     */
    public function shouldShow(array $rules, ?string $timezone = null): bool
    {
        if (empty($rules)) {
            return true;
        }

        // Если часовой пояс не указан, берем из настроек приложения или UTC
        if (!$timezone) {
            $timezone = config('app.timezone', 'UTC');
        }

        $now = Carbon::now($timezone);

        foreach ($rules as $rule) {
            if ($this->matchesRule($now, $rule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка соответствия одному правилу
     */
    protected function matchesRule(Carbon $now, array $rule): bool
    {
        // Проверка по датам (диапазон)
        if ($rule['type'] === 'date_range') {
            // Проверка дат
            $startDate = $rule['start_date'] ?? null;
            $endDate = $rule['end_date'] ?? null;

            if ($startDate && $now->lt(Carbon::parse($startDate))) return false;
            if ($endDate && $now->gt(Carbon::parse($endDate))) return false;

            // Проверка времени (если указано)
            if (!empty($rule['start_time']) && !empty($rule['end_time'])) {
                return $this->matchesTime($now, $rule);
            }
            return true;
        }

        // Проверка по дням недели
        if ($rule['type'] === 'days') {
            $currentDay = strtolower($now->format('D'));

            // Проверка дня недели
            if (!empty($rule['days']) && !in_array($currentDay, $rule['days'])) {
                return false;
            }

            // Проверка времени (если указано)
            if (!empty($rule['start_time']) && !empty($rule['end_time'])) {
                return $this->matchesTime($now, $rule);
            }
            return true;
        }

        return false;
    }


    /**
     * Проверка диапазона дат
     */
    protected function matchesDateRange(Carbon $now, array $rule): bool
    {
        $startDate = $rule['start_date'] ? Carbon::parse($rule['start_date']) : null;
        $endDate = $rule['end_date'] ? Carbon::parse($rule['end_date']) : null;

        if ($startDate && $now->lt($startDate)) return false;
        if ($endDate && $now->gt($endDate)) return false;

        // Если есть время, проверяем его
        if ($rule['start_time'] && $rule['end_time']) {
            return $this->matchesTime($now, $rule);
        }

        return true;
    }

    /**
     * Проверка дней недели и времени
     */
    protected function matchesDaysAndTime(Carbon $now, array $rule): bool
    {
        $currentDay = strtolower($now->format('D')); // mon, tue, wed...
        $currentTime = $now->format('H:i');

        // Проверка дня недели
        if (!empty($rule['days']) && !in_array($currentDay, $rule['days'])) {
            return false;
        }

        // Проверка времени
        if ($rule['start_time'] && $rule['end_time']) {
            return $this->matchesTime($now, $rule);
        }

        return true;
    }

    /**
     * Проверка временного интервала
     */
    protected function matchesTime(Carbon $now, array $rule): bool
    {
        $currentTime = $now->format('H:i');
        $startTime = $rule['start_time'];
        $endTime = $rule['end_time'];

        // Если время не указано - считаем что подходит (круглосуточно)
        if (empty($startTime) || empty($endTime)) {
            return true;
        }

        // Интервал через полночь (например 22:00 - 06:00)
        if ($startTime > $endTime) {
            return $currentTime >= $startTime || $currentTime <= $endTime;
        }

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    /**
     * Получение правил валидации
     */
    public function getValidationRules(): array
    {
        return [
            'target_time' => 'nullable|array',
            'target_time.*.type' => 'required|in:date_range,days',
            'target_time.*.days' => 'nullable|array',
            'target_time.*.days.*' => 'in:mon,tue,wed,thu,fri,sat,sun',
            'target_time.*.start_date' => 'nullable|date',
            'target_time.*.end_date' => 'nullable|date|after_or_equal:target_time.*.start_date',
            'target_time.*.start_time' => 'nullable|date_format:H:i',
            'target_time.*.end_time' => 'nullable|date_format:H:i',
        ];
    }

    /**
     * Нормализация типа правила
     */
    protected function normalizeType(string $type): string
    {
        return in_array($type, ['date_range', 'days']) ? $type : 'days';
    }

    /**
     * Нормализация дней недели
     */
    protected function normalizeDays(array $days): array
    {
        $validDays = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        return array_values(array_intersect($days, $validDays));
    }

    /**
     * Нормализация даты
     */
    protected function normalizeDate($date): ?string
    {
        if (empty($date)) return null;
        $timestamp = strtotime($date);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    /**
     * Нормализация времени
     */
    protected function normalizeTime($time): ?string
    {
        if (empty($time)) return null;
        $timestamp = strtotime($time);
        return $timestamp ? date('H:i', $timestamp) : null;
    }
}

<?php

namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Widgets\Services\TargetTimeManager;
use App\Models\Site;
use App\Models\Widget;
use App\Http\Controllers\Widgets\Services\BehaviorManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WidgetConfigurationController extends BaseCabinetController
{
    /**
     * Доступные типы триггеров
     */
    protected const TRIGGERS = ['immediate', 'delay', 'scroll', 'exit', 'click'];

    /**
     * Доступные типы частоты показа
     */
    protected const FREQUENCIES = ['always', 'once_session', 'once_day', 'once_week', 'once_month', 'once_forever'];

    public function edit(Site $site, Widget $widget)
    {
        // Нормализуем поведение при чтении
        $behaviorManager = new BehaviorManager();
        $behavior = $behaviorManager->normalize($widget->behavior ?? []);

        $targetTimeManager = new TargetTimeManager();
        $targetTime = $targetTimeManager->normalize($widget->target_time ?? []);


        return view('cabinet.widgets.config', [
            'site' => $site,
            'widget' => $widget,
            'config' => $widget->settings,
            'target' => $widget->target_paths ?? ['allow' => [], 'disallow' => []],
            'behavior' => $behavior,
            'target_time' => $targetTime, // добавляем в шаблон
        ]);
    }

    public function update(Request $request, Site $site, Widget $widget)
    {
        // 1. Очистка UTM данных
        $utmData = $this->cleanUtmData($request->input('target_utm', []));

        // 2. Валидация
        $validated = $this->validateRequest($request);

        // 3. Нормализация поведения (только релевантные поля)
        $behaviorManager = new BehaviorManager();
        $behavior = $behaviorManager->fromRequest($request->input('behavior', []));

        $targetTime = $request->input('target_time', []);
        $targetTime = app(TargetTimeManager::class)->normalize($targetTime);


        try {
            DB::beginTransaction();

            $widget->update([
                'custom_name' => $validated['custom_name'] ?? null,
                'is_active' => $request->has('is_active'),
                'privacy_config' => [
                    'type' => $request->input('privacy_policy_type'),
                    'url' => $request->input('privacy_policy_url'),
                ],
                'settings' => $this->mergeSettings($widget->settings ?? [], $request->all()),
                'behavior' => $behavior,
                'target_time' => $targetTime,
                'target_paths' => $this->processPaths(
                    $request->input('target_paths.allow', ''),
                    $request->input('target_paths.disallow', '')
                ),
                'target_utm' => $utmData,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Настройки успешно сохранены');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Widget Save Error: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ошибка при сохранении данных: ' . $e->getMessage());
        }
    }

    /**
     * Нормализация поведения (очистка и установка дефолтов)
     */
    protected function normalizeBehavior(array $behavior): array
    {
        $defaults = [
            'trigger_type' => 'immediate',
            'delay' => 3,
            'scroll_percent' => 50,
            'click_selector' => '',
            'frequency' => 'always',
            'auto_close' => 0,
        ];

        $result = [];

        // Базовые поля с дефолтами
        $result['trigger_type'] = in_array($behavior['trigger_type'] ?? 'immediate', self::TRIGGERS)
            ? $behavior['trigger_type']
            : $defaults['trigger_type'];

        $result['frequency'] = in_array($behavior['frequency'] ?? 'always', self::FREQUENCIES)
            ? $behavior['frequency']
            : $defaults['frequency'];

        $result['auto_close'] = $this->normalizeInt($behavior['auto_close'] ?? 0, 0, 60);

        // Добавляем поля в зависимости от триггера
        switch ($result['trigger_type']) {
            case 'delay':
                $result['delay'] = $this->normalizeInt($behavior['delay'] ?? 3, 0, 30);
                break;
            case 'scroll':
                $result['scroll_percent'] = $this->normalizeInt($behavior['scroll_percent'] ?? 50, 0, 100);
                break;
            case 'click':
                $result['click_selector'] = trim(strip_tags($behavior['click_selector'] ?? ''));
                break;
        }

        return $result;
    }

    /**
     * Нормализация поведения из запроса
     */
    protected function normalizeBehaviorFromRequest(Request $request): array
    {
        $triggerType = $request->input('behavior.trigger_type', 'immediate');
        $frequency = $request->input('behavior.frequency', 'always');

        $behavior = [
            'trigger_type' => in_array($triggerType, self::TRIGGERS) ? $triggerType : 'immediate',
            'frequency' => in_array($frequency, self::FREQUENCIES) ? $frequency : 'always',
            'auto_close' => $this->normalizeInt($request->input('behavior.auto_close', 0), 0, 60),
        ];

        // Добавляем поля в зависимости от триггера
        switch ($triggerType) {
            case 'delay':
                $behavior['delay'] = $this->normalizeInt($request->input('behavior.delay', 3), 0, 30);
                break;
            case 'scroll':
                $behavior['scroll_percent'] = $this->normalizeInt($request->input('behavior.scroll_percent', 50), 0, 100);
                break;
            case 'click':
                $behavior['click_selector'] = trim(strip_tags($request->input('behavior.click_selector', '')));
                break;
        }

        return $behavior;
    }

    /**
     * Нормализация целочисленного значения с ограничениями
     */
    protected function normalizeInt($value, int $min, int $max): int
    {
        $value = (int) $value;
        if ($value < $min) return $min;
        if ($value > $max) return $max;
        return $value;
    }

    /**
     * Валидация запроса
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            // Базовые настройки
            'custom_name' => 'nullable|string|max:255',
            'is_active' => 'nullable',
            'privacy_policy_type' => 'required|in:system,custom,none',
            'privacy_policy_url' => 'required_if:privacy_policy_type,custom|nullable|url',

            // Настройки поведения
            'behavior.trigger_type' => 'required|in:' . implode(',', self::TRIGGERS),
            'behavior.delay' => 'required_if:behavior.trigger_type,delay|nullable|integer|min:0|max:30',
            'behavior.scroll_percent' => 'required_if:behavior.trigger_type,scroll|nullable|integer|min:0|max:100',
            'behavior.click_selector' => 'required_if:behavior.trigger_type,click|nullable|string|max:255',
            'behavior.frequency' => 'required|in:' . implode(',', self::FREQUENCIES),
            'behavior.auto_close' => 'nullable|integer|min:0|max:60',

            // Таргетинг
            'target_paths.allow' => 'nullable|string',
            'target_paths.disallow' => 'nullable|string',
            'target_utm' => 'nullable|array',
            'target_utm.*.*.key' => 'required_with:target_utm.*.*.val|string',
            'target_utm.*.*.val' => 'required_with:target_utm.*.*.key|string',
        ], [
            'behavior.trigger_type.required' => 'Выберите триггер показа.',
            'behavior.delay.required_if' => 'Укажите задержку появления.',
            'behavior.scroll_percent.required_if' => 'Укажите процент прокрутки.',
            'behavior.click_selector.required_if' => 'Укажите CSS селектор.',
            'behavior.frequency.required' => 'Выберите частоту показа.',
        ]);
    }

    /**
     * Очистка UTM данных от пустых значений
     */
    protected function cleanUtmData(array $utmData): array
    {
        foreach ($utmData as $gIdx => $group) {
            foreach ($group as $rIdx => $rule) {
                if (empty($rule['val']) && (empty($rule['key']) || $rule['key'] === 'utm_source')) {
                    unset($utmData[$gIdx][$rIdx]);
                }
            }
            if (empty($utmData[$gIdx])) {
                unset($utmData[$gIdx]);
            }
        }
        return array_values($utmData);
    }

    /**
     * Слияние существующих настроек с новыми
     */
    protected function mergeSettings(array $currentSettings, array $input): array
    {
        // Здесь можно добавить логику для специфичных настроек виджетов
        // Например, для колеса фортуны сохраняем segments и т.д.
        return $currentSettings;
    }

    /**
     * Обработка путей (allow/disallow)
     */
    protected function processPaths($allowRaw, $disallowRaw): array
    {
        $allowRaw = $allowRaw ?? '';
        $disallowRaw = $disallowRaw ?? '';

        $process = function($str) {
            if (empty($str)) return [];
            return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $str)))));
        };

        return [
            'allow' => $process($allowRaw),
            'disallow' => $process($disallowRaw),
        ];
    }
}

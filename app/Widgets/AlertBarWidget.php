<?php

namespace App\Widgets;

use App\Models\Widget;

class AlertBarWidget implements WidgetContract
{
    public function getDesignForm(): string
    {
        return 'widgets.alert-bar.configuration';
    }

    public function getEditorConfig(Widget $widget): array
    {
        return [
            'slug' => 'alert-bar',
            'settings' => $widget->settings,
            'skins' => (new CookiePopsWidget())->getSkins('alert-bar')
        ];
    }

    public function updateDesign(Widget $widget, array $data): bool
    {
        // Получаем текущие настройки или создаем массив
        $settings = $widget->settings ?? [];

        if (isset($data['settings'])) {
            $data = $data['settings'];
        }
        // Сохраняем все поля из формы
        $settings['text'] = $data['text'] ?? $settings['text'] ?? 'Скидка -20% на первичный прием до конца недели!';
        $settings['link'] = $data['link'] ?? $settings['link'] ?? '';
        $settings['btn_text'] = $data['btn_text'] ?? $settings['btn_text'] ?? 'Узнать больше';
        $settings['has_button'] = filter_var($data['has_button'] ?? $settings['has_button'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $settings['position'] = $data['position'] ?? $settings['position'] ?? 'top';
        $settings['fixed_on_scroll'] = filter_var($data['fixed_on_scroll'] ?? $settings['fixed_on_scroll'] ?? true, FILTER_VALIDATE_BOOLEAN);
        // Настройки дизайна
        $settings['design'] = [
            'bg_color' => $data['design']['bg_color'] ?? $settings['design']['bg_color'] ?? '#E63946',
            'text_color' => $data['design']['text_color'] ?? $settings['design']['text_color'] ?? '#FFFFFF',
            'btn_color' => $data['design']['btn_color'] ?? $settings['design']['btn_color'] ?? '#1D3557'
        ];

        // Поведение (маркетинговые инструменты)
        $settings['delay'] = (int)($data['delay'] ?? $settings['delay'] ?? 0);
        $settings['auto_hide'] = (int)($data['auto_hide'] ?? $settings['auto_hide'] ?? 0);
        $settings['scroll_trigger'] = (int)($data['scroll_trigger'] ?? $settings['scroll_trigger'] ?? 0);
        $settings['frequency'] = $data['frequency'] ?? $settings['frequency'] ?? 'once_session';
        $settings['close_behavior'] = $data['close_behavior'] ?? $settings['close_behavior'] ?? 'hide_session';

        // Шаблон
        $settings['template'] = $data['template'] ?? $settings['template'] ?? 'default';

        // Для отладки - можно добавить лог
        \Log::info('AlertBarWidget saved settings:', $settings);



        return $widget->update(['settings' => $settings]);
    }
}

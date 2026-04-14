<?php

namespace App\Widgets;

use App\Models\Widget;
use Illuminate\Support\Facades\File;

class LidUpWidget implements WidgetContract
{
    public function getDesignForm(): string
    {
        return 'widgets.lidup.configuration';
    }

    public function getEditorConfig(Widget $widget): array
    {
        return [
            'slug' => 'lidup',
            'settings' => $widget->settings,
            'skins' => $this->getSkins('lidup')
        ];
    }

    function getSkins(string $slug): array
    {
        $skinsPath = public_path("widgets/{$slug}/skins");
        $skins = [];

        if (File::exists($skinsPath)) {
            $directories = File::directories($skinsPath);
            foreach ($directories as $dir) {
                $skinSlug = basename($dir);
                $skins[$skinSlug] = [
                    'name' => ucwords(str_replace('-', ' ', $skinSlug)),
                    'slug' => $skinSlug
                ];
            }
        }

        // Если нет скинов, создаем дефолтный
        if (empty($skins)) {
            $skins['default'] = [
                'name' => 'Default',
                'slug' => 'default'
            ];
        }

        return $skins;
    }

    public function updateDesign(Widget $widget, array $data): bool
    {
        $settings = $widget->settings ?? [];

        if (isset($data['settings'])) {
            $data = $data['settings'];
        }

        // Контент
        $settings['title'] = $data['title'] ?? $settings['title'] ?? 'Получите скидку 20%';
        $settings['description'] = $data['description'] ?? $settings['description'] ?? '';
        $settings['image'] = $data['image'] ?? $settings['image'] ?? '';
        $settings['image_position'] = $data['image_position'] ?? $settings['image_position'] ?? 'left';
        $settings['has_image'] = filter_var($data['has_image'] ?? $settings['has_image'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Форма
        $settings['form_fields'] = $data['form_fields'] ?? $settings['form_fields'] ?? [];
        $settings['btn_text'] = $data['btn_text'] ?? $settings['btn_text'] ?? 'Отправить заявку';
        $settings['success_message'] = $data['success_message'] ?? $settings['success_message'] ?? 'Спасибо! Мы свяжемся с вами.';
        $settings['error_message'] = $data['error_message'] ?? $settings['error_message'] ?? 'Ошибка. Попробуйте позже.';
        $settings['webhook_url'] = $data['webhook_url'] ?? $settings['webhook_url'] ?? '';

        // Таймер
        $settings['has_timer'] = filter_var($data['has_timer'] ?? $settings['has_timer'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $settings['timer_target_date'] = $data['timer_target_date'] ?? $settings['timer_target_date'] ?? '';

        // Поведение
        $settings['trigger_type'] = $data['trigger_type'] ?? $settings['trigger_type'] ?? 'time';
        $settings['delay'] = (int)($data['delay'] ?? $settings['delay'] ?? 3);
        $settings['scroll_percent'] = (int)($data['scroll_percent'] ?? $settings['scroll_percent'] ?? 50);
        $settings['exit_intent'] = filter_var($data['exit_intent'] ?? $settings['exit_intent'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $settings['frequency'] = $data['frequency'] ?? $settings['frequency'] ?? 'once_session';
        $settings['close_behavior'] = $data['close_behavior'] ?? $settings['close_behavior'] ?? 'hide_session';
        $settings['auto_close'] = (int)($data['auto_close'] ?? $settings['auto_close'] ?? 0);

        // Дизайн
        $settings['position'] = $data['position'] ?? $settings['position'] ?? 'center';
        $settings['size'] = $data['size'] ?? $settings['size'] ?? 'medium';
        $settings['animation_in'] = $data['animation_in'] ?? $settings['animation_in'] ?? 'fadeIn';
        $settings['overlay_color'] = $data['overlay_color'] ?? $settings['overlay_color'] ?? 'rgba(0,0,0,0.7)';
        $settings['design'] = [
            'bg_color' => $data['design']['bg_color'] ?? $settings['design']['bg_color'] ?? '#FFFFFF',
            'text_color' => $data['design']['text_color'] ?? $settings['design']['text_color'] ?? '#1F2937',
            'accent_color' => $data['design']['accent_color'] ?? $settings['design']['accent_color'] ?? '#3B82F6',
            'btn_color' => $data['design']['btn_color'] ?? $settings['design']['btn_color'] ?? '#22C55E',
            'btn_text_color' => $data['design']['btn_text_color'] ?? $settings['design']['btn_text_color'] ?? '#FFFFFF',
            'border_radius' => $data['design']['border_radius'] ?? $settings['design']['border_radius'] ?? '16',
        ];

        // Шаблон
        $settings['template'] = $data['template'] ?? $settings['template'] ?? 'default';

        return $widget->update(['settings' => $settings]);
    }
}

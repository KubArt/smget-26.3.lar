<?php

namespace App\Widgets;

use App\Models\Widget;
use Illuminate\Support\Facades\File;

class ContactButtonWidget implements WidgetContract
{
    public function getDesignForm(): string
    {
        return 'widgets.contact-button.configuration';
    }

    public function getEditorConfig(Widget $widget): array
    {
        // Гарантируем структуру данных
        $settings = $widget->settings;

        if (!isset($settings['channels']) || !is_array($settings['channels'])) {
            $settings['channels'] = [];
        }

        if (!isset($settings['design'])) {
            $settings['design'] = [
                'main_color' => '#3b82f6',
                'icon_color' => '#ffffff',
                'size' => 'medium',
                'opacity' => 1,
                'hover_effect' => 'lift'
            ];
        }

        if (!isset($settings['animation'])) {
            $settings['animation'] = [
                'type' => 'wave',
                'enabled' => true
            ];
        }

        if (!isset($settings['template'])) {
            $settings['template'] = 'default';
        }

        if (!isset($settings['position'])) {
            $settings['position'] = 'bottom-right';
        }

        if (!isset($settings['delay'])) {
            $settings['delay'] = 1;
        }

        if (!isset($settings['main_tooltip'])) {
            $settings['main_tooltip'] = 'Свяжитесь с нами';
        }

        return [
            'slug' => 'contact-button',
            'settings' => $settings,
            'skins' => $this->getSkins($widget->widgetType->slug)
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
        // Получаем текущие настройки
        $currentSettings = $widget->settings;

        // Извлекаем новые настройки
        $newSettings = $data['settings'] ?? $data;

        // Сохраняем только нужные поля
        $settings = [
            'template' => $newSettings['template'] ?? $currentSettings['template'] ?? 'default',
            'position' => $newSettings['position'] ?? $currentSettings['position'] ?? 'bottom-right',
            'delay' => $newSettings['delay'] ?? $currentSettings['delay'] ?? 1,
            'main_tooltip' => $newSettings['main_tooltip'] ?? $currentSettings['main_tooltip'] ?? '',
            'channels' => $newSettings['channels'] ?? $currentSettings['channels'] ?? [],
            'design' => $newSettings['design'] ?? $currentSettings['design'] ?? [
                    'main_color' => '#3b82f6',
                    'icon_color' => '#ffffff',
                    'size' => 'medium',
                    'opacity' => 1,
                    'hover_effect' => 'lift'
                ],
            'animation' => $newSettings['animation'] ?? $currentSettings['animation'] ?? [
                    'type' => 'wave',
                    'enabled' => true
                ]
        ];

        return $widget->update(['settings' => $settings]);
    }
}

<?php

namespace App\Widgets;

use App\Models\Widget;
use Illuminate\Support\Facades\File;

class ContactButtonWidget implements WidgetContract
{
    public function getDesignForm(): string { return 'widgets.contact-button.configuration'; }

    public function getEditorConfig(Widget $widget): array
    {
        return [
            'slug' => 'contact-button',
            'settings' => $widget->settings,
            'skins'    => $this->getSkins($widget->widgetType->slug)
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

        return $skins;
    }

    public function updateDesign(Widget $widget, array $data): bool
    {
        $settings = $widget->settings;

        if (isset($data['settings'])) {
            $data = $data['settings'];
        }


        // Массив каналов сохраняем как есть из формы
        $settings['channels'] = $data['channels'] ?? [];
        $settings['position'] = $data['position'] ?? 'bottom-right';
        $settings['pulse'] = isset($data['pulse']);
        $settings['delay'] = $data['delay'] ?? 2;
        $settings['design'] = $data['design'] ?? ['main_color' => '#007bff', 'icon_color' => '#ffffff'];

        return $widget->update(['settings' => $settings]);
    }
}

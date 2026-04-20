<?php

namespace App\Widgets;

use App\Models\Widget;
use Illuminate\Support\Facades\File;

class AlertBarWidget implements WidgetContract
{
    public function getDesignForm(): string
    {
        return 'widgets.alert-bar.configuration';
    }

    public function getEditorConfig(Widget $widget): array
    {
        // Берем дефолты из нашего нового конфига
        $baseConfig = config("widgets.alert-bar.default_values.settings", []);

        // Сливаем с базой (база в приоритете)
        $mergedSettings = array_replace_recursive($baseConfig, $widget->settings ?? []);

        return [
            'slug'     => 'alert-bar',
            'settings' => $mergedSettings,
            'skins'    => $this->getSkins('alert-bar')
        ];
    }

    public function updateDesign(Widget $widget, array $data): bool
    {
        $baseConfig = config("widgets.alert-bar.default_values.settings", []);

        // Берем входящие настройки из формы
        $inputSettings = $data['settings'] ?? [];

        // Финальный объект для БД
        $finalSettings = array_replace_recursive($baseConfig, $inputSettings);

         \Log::info('AlertBarWidget saved settings:', $settings);

        return $widget->update([
            'settings' => $finalSettings
        ]);
    }

    public function getSkins(string $slug): array
    {
        // Используем твою логику сканирования директории skins
        $skinsPath = public_path("widgets/{$slug}/skins");
        $skins = [];

        if (File::exists($skinsPath)) {
            foreach (File::directories($skinsPath) as $dir) {
                $skinSlug = basename($dir);
                $skins[$skinSlug] = [
                    'name' => ucwords(str_replace('-', ' ', $skinSlug)),
                    'slug' => $skinSlug
                ];
            }
        }
        return $skins;
    }

}

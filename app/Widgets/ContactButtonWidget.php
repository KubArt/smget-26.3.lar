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
        // Берем структуру из файла
        $baseConfig = config("widgets.contact-button.default_values.settings", []);
        // Сливаем с тем, что в БД
        $mergedSettings = array_replace_recursive($baseConfig, $widget->settings ?? []);

        return [
            'slug'     => 'contact-button',
            'settings' => $mergedSettings,
            'skins'    => $this->getSkins('contact-button')
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
        $baseConfig = config("widgets.contact-button.default_values.settings", []);
        $inputSettings = $data['settings'] ?? [];

        // При сохранении мы тоже делаем merge, чтобы гарантировать
        // наличие всех ключей, даже если фронт их не прислал.
        $finalSettings = array_replace_recursive($baseConfig, $inputSettings);

        return $widget->update([
            'settings' => $finalSettings
        ]);
    }
}

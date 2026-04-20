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
        $baseConfig = config("widgets.lidup.default_values.settings", []);

        // При открытии редактора склеиваем эталон и базу
        $mergedSettings = array_replace_recursive($baseConfig, $widget->settings ?? []);

        return [
            'slug'     => 'lidup',
            'settings' => $mergedSettings,
            'skins'    => $this->getSkins('lidup')
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
        // Берем эталонную структуру из config/widgets/lidup.php
        $baseConfig = config("widgets.lidup.default_values.settings", []);

        // Данные из запроса (уже структурированные формой)
        $inputSettings = $data['settings'] ?? [];

        // Сливаем: эталон + ввод пользователя.
        // Это гарантирует, что если пользователь удалил все поля формы,
        // они не превратятся в null, а останутся массивом.
        $finalSettings = array_replace_recursive($baseConfig, $inputSettings);

        return $widget->update([
            'settings' => $finalSettings
        ]);
    }
}

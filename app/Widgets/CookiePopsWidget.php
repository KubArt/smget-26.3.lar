<?php


namespace App\Widgets;

use App\Models\Widget;
use Illuminate\Support\Facades\File;

class CookiePopsWidget implements WidgetContract
{
    public function getDesignForm(): string
    {
        // Указываем путь к специфичной форме
        return 'widgets.cookie-pops.configuration';
    }
    public function getEditorConfig(Widget $widget): array
    {
        // 1. Берем эталонную структуру из Laravel config
        // Напоминаю: мы договорились хранить это в config/widgets/cookie-pops.php
        $baseConfig = config("widgets.{$widget->type}.default_values.settings", []);
        // 2. Рекурсивно сливаем с тем, что реально есть в базе
        $mergedSettings = array_replace_recursive($baseConfig, $widget->settings ?? []);
        return [
            'slug'     => $widget->type,
            'settings' => $mergedSettings, // Теперь здесь всегда полная структура
            'skins'    => $this->getSkins($widget->type)
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
        // 1. Берем текущий эталон структуры из файлов конфигурации
        $baseConfig = config("widgets.{$widget->type}.default_values.settings", []);
        // 2. Берем то, что реально пришло из формы (дизайн и контент)
        $inputSettings = $data['settings'] ?? [];
        // 3. Слияние:
        // Мы НЕ используем array_merge_recursive, чтобы не дублировать массивы (например, если в дизайне есть списки).
        // array_replace_recursive идеально заменит значения, сохранив структуру.
        $finalSettings = array_replace_recursive($baseConfig, $inputSettings);
        // 4. Сохраняем чистый объект в БД
        return $widget->update([
            'settings' => $finalSettings
        ]);
    }
    /*
    public function updateDesign(Widget $widget, array $data): bool
    {
        // Извлекаем текущие настройки, чтобы не затереть другие ключи (если есть)
        $settings = $widget->settings;

        // Обновляем структуру настроек
        // Мы ожидаем, что с фронта придет объект settings, содержащий дизайн и контент
        if (isset($data['settings'])) {
            $settings = $data['settings'];
        }

        return $widget->update([
            'settings' => $settings
        ]);
    }
    //*/
}

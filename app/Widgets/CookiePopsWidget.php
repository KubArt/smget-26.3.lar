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
        return [
            'slug' => 'cookie-pops',
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
}

<?php


namespace App\Services;

use App\Models\Widgets\WidgetType;
use Illuminate\Support\Facades\File;

class WidgetManager
{
    public function discoverAndSync()
    {
        $widgetsPath = app_path('Widgets');
        if (!File::exists($widgetsPath)) return;

        $directories = File::directories($widgetsPath);

        foreach ($directories as $directory) {
            $widgetName = basename($directory);
            $manifestClass = "App\\Widgets\\{$widgetName}\\Manifest";

            if (class_exists($manifestClass)) {
                $config = $manifestClass::config();
                // Синхронизируем с БД по slug
                WidgetType::updateOrCreate(
                    ['slug' => $config['slug']],
                    [
                        'name'     => $config['name'],
                        'description'     => $config['description'],
                        'category' => $config['category'],
                        'manifest' => $config, // Сохраняем весь паспорт виджета
                        'is_active' => true
                    ]
                );
            }
        }
    }
}

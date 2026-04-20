<?php

namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Models\Site;
use App\Models\Widget;
use App\Widgets\WidgetFactory;
use Illuminate\Http\Request;

class WidgetDesignController extends BaseCabinetController
{
    public function design(Site $site, Widget $widget)
    {
        $service = WidgetFactory::make($widget);
        // Все данные для JS (включая скины) теперь приходят из одного метода
        $editorConfig = $service->getEditorConfig($widget);

        return view($service->getDesignForm(), [
            'site'   => $site,
            'widget' => $widget,
            'config' => $editorConfig,
        ]);
    }

    public function designUpdate(Request $request, Site $site, Widget $widget)
    {
        $this->authorizeAccess($site); // Проверка прав

        $service = WidgetFactory::make($widget);

        // Передаем только нужную часть запроса (обычно это массив settings)
        $success = $service->updateDesign($widget, $request->only('settings'));

        if ($success) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Конфигурация успешно сохранена'
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Ошибка при сохранении данных'
        ], 500);
    }
}

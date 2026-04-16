<?php

namespace App\Http\Controllers\Cabinet;

use App\Models\Site;
use App\Models\Widgets\WidgetType;
use App\Models\Widget; // Предполагаем, что модель называется так
use Illuminate\Http\Request;

class SiteWidgetController extends BaseCabinetController
{
    /*
    public function __construct()
    {
        parent::__construct();
    }
    //*/

    /**
     * Список всех доступных виджетов в системе (Маркетплейс)
     */
    public function market(Request $request)
    {
        $widgetTypes = WidgetType::where('is_active', true)->get();
        $preSelectedSite = $request->site_id ? Site::find($request->site_id) : null;

        return view('cabinet.widgets.market', compact('widgetTypes', 'preSelectedSite'));
    }

    /**
     * Список виджетов, установленных на конкретный сайт
     */
    public function index(Site $site)
    {
        $this->authorizeAccess($site); // Проверка прав кабинета
        // $this->authorizeOwner($site);
        // $this->authorizeAccess($site); // Метод из BaseCabinetController

        $widgets = $site->widgets()->with('widgetType')->get();
        return view('cabinet.sites.widgets.index', compact('site', 'widgets'));
    }

    /**
     * Установка виджета на сайт
     */
    public function store(Request $request, Site $site)
    {
        $this->authorizeAccess($site); // Проверка прав кабинета


        $request->validate([
            'widget_type_id' => 'required|exists:widget_types,id'
        ]);

        $widgetType = WidgetType::findOrFail($request->widget_type_id);

        // В вашей модели Widget есть поле 'type', его нужно заполнить
        $site->widgets()->create([
            'widget_type_id' => $widgetType->id,
            'type'           => $widgetType->slug, // Используем slug (например, 'cookie-pops') как тип
            'name'           => $widgetType->name,
            'settings'       => $widgetType->manifest['default_settings'] ?? [],
            'is_enabled'     => true,
        ]);

        return redirect()
            ->route('cabinet.sites.widgets.index', $site)
            ->with('success', "Виджет {$widgetType->name} успешно добавлен");
    }

    /**
     * Переключение статуса (вкл/выкл) через AJAX
     */
    public function toggle(Request $request, Site $site, Widget $widget)
    {
        // Проверка прав (убедись, что метод authorizeOwner есть в контроллере или BaseCabinetController)
        $this->authorizeAccess($site); // Проверка прав кабинета

        $newState = $request->status ? true : false;

        $widget->update([
            'is_active' => $newState
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $widget->is_enabled,
            'message' => 'Виджет "' . $widget->widgetType->name . '" ' . ($widget->is_active ? 'включен' : 'выключен')
        ]);
    }

    /**
     * Удаление виджета с сайта
     */
    public function destroy(Site $site, Widget $widget)
    {
        $this->authorizeAccess($site); // Проверка прав кабинета
        abort_if($widget->site_id !== $site->id, 403);

        $widget->delete();
        return back()->with('success', 'Виджет удален');

        return back()->with('success', 'Виджет удален');
    }

    /**
     * Приватный метод проверки прав (опционально, если нет Policy)
     */
    protected function authorizeOwner(Site $site)
    {
        if ($site->user_id !== auth()->id()) {
            abort(403, 'У вас нет доступа к этому проекту');
        }
    }
}

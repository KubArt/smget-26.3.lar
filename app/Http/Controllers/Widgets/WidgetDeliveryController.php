<?php


namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;


class WidgetDeliveryController extends Controller
{
    public function getPayload(Request $request){
        // 1. Ищем сайт по API KEY
        $site = Site::where('api_key', $request->get('key'))
            ->where('is_verified', true) // Только если сайт подтвержден
            ->first();

        if (!$site) {
            return response()->json(['error' => 'Invalid key'], 403);
        }

        // Берем только включенные виджеты
        $activeWidgets = $site->widgets()
            ->where('is_active', true)
            ->with('widgetType')
            ->get()
            ->map(function ($widget) {
                return [
                    'id'     => $widget->id,
                    'type'   => $widget->type, // 'cookie-pops'
                    'config' => $widget->settings,
                    // Пути к ассетам берем из манифеста
                    'assets' => [
                        'js'  => asset("widgets/{$widget->type}/widget.js"),
                        'css' => asset("widgets/{$widget->type}/style.css")
                    ]
                ];
            });

        return response()->json([
            'site_id' => $site->id,
            'widgets' => $activeWidgets
        ]);
    }

    public function track(Request $request)
    {
        // Используем json_decode, так как sendBeacon отправляет сырой текст
        $data = json_decode($request->getContent(), true);

        if (isset($data['widget_id'])) {
            // Здесь мы просто логируем или пишем в таблицу статистик
            // В будущем тут будет: WidgetStat::create([...])
            \Log::info("Widget Event: " . $data['event'], [
                'widget_id' => $data['widget_id'],
                'url' => $data['url'] ?? '/'
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}

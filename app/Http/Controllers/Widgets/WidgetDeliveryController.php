<?php


namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Widgets\WidgetStatistic;
use Illuminate\Http\Request;


class WidgetDeliveryController extends BaseCabinetController
{

    public function getPayload(Request $request)
    {
        $site = Site::where('api_key', $request->get('key'))->where('is_verified', true)->first();

        if (!$site) return response()->json(['error' => 'Invalid key'], 403);

        $currentPath = $request->get('path', '/');
        $currentUtm = array_filter($request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term']));

        $activeWidgets = $site->widgets()
            ->where('is_active', true)
            ->get()
            ->filter(function ($widget) use ($currentPath, $currentUtm) {
                return $this->shouldShowWidget($widget, $currentPath, $currentUtm);
            })
            ->map(function ($widget) use ($request, $currentPath, $currentUtm) {
                $this->logEvent($widget->id, 'view', $request, $currentPath, $currentUtm);

                // Получаем настройки и выбранный скин
                $settings = $widget->settings;
                $slug = $widget->widgetType->slug; // например, 'cookie-pops'
                $skin = $settings['template'] ?? 'default';

                return [
                    'id'       => $widget->id,
                    'type'     => $slug,
                    'settings' => $settings,
                    // Собираем ассеты
                    'assets'   => $this->getWidgetAssets($slug, $skin)
                ];
            });

        return response()->json(['widgets' => $activeWidgets->values()]);
    }

    /**
     * Сборка HTML/CSS/JS для конкретного скина с откатом к default
     */
    private function getWidgetAssets(string $slug, string $skin): array
    {
        $basePath = "widgets/{$slug}";

        return [
            'html' => $this->getAssetContent($basePath, $skin, 'template.html'),
            'css'  => $this->getAssetContent($basePath, $skin, 'style.css'),
            'js'   => $this->getAssetContent($basePath, $skin, 'widget.js'),
        ];
    }

    /**
     * Вспомогательный метод: ищет файл в папке скина,
     * если нет — берет из 'default', если и там нет — возвращает пустую строку.
     */
    private function getAssetContent(string $basePath, string $skin, string $fileName): string
    {
        $skinFile = public_path("{$basePath}/skins/{$skin}/{$fileName}");
        $defaultFile = public_path("{$basePath}/{$fileName}");

        if (file_exists($skinFile)) {
            return file_get_contents($skinFile);
        }

        if (file_exists($defaultFile)) {
            return file_get_contents($defaultFile);
        }

        return '';
    }

    /*** */

    public function OLD_getPayload(Request $request)
    {
        $site = Site::where('api_key', $request->get('key'))->where('is_verified', true)->first();

        if (!$site) return response()->json(['error' => 'Invalid key'], 403);

        $currentPath = $request->get('path', '/');
        $currentUtm = $request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term']);
        $currentUtm = array_filter($currentUtm); // Убираем пустые

        $activeWidgets = $site->widgets()
            ->where('is_active', true)
            ->get()
            ->filter(function ($widget) use ($currentPath, $currentUtm) {
                return $this->shouldShowWidget($widget, $currentPath, $currentUtm);
            })
            ->map(function ($widget) use ($request, $currentPath, $currentUtm) {
                // ФИКСИРУЕМ ПРОСМОТР (View) сразу при выдаче контента
                $this->logEvent($widget->id, 'view', $request, $currentPath, $currentUtm);

                return [
                    'id'     => $widget->id,
                    'type'   => $widget->widgetType->slug,
                    'config' => $widget->settings,
                    'assets' => [
                        'js'  => asset("widgets/{$widget->widgetType->slug}/widget.js"),
                        'css' => asset("widgets/{$widget->widgetType->slug}/style.css")
                    ]
                ];
            });

        return response()->json([
            'widgets' => $activeWidgets->values()
        ]);
    }

    private function shouldShowWidget($widget, $path, $utm)
    {
        // 1. Сначала проверяем URL (Маски) - это первичный фильтр
        $allowed = empty($widget->target_paths['allow']) || $this->matchMasks($path, $widget->target_paths['allow']);
        $blocked = !empty($widget->target_paths['disallow']) && $this->matchMasks($path, $widget->target_paths['disallow']);

        if (!$allowed || $blocked) return false;

        // 2. Проверка UTM (С учетом того, что loader прислал их из сессии)
        // Если в настройках виджета заданы группы UTM
        if (!empty($widget->target_utm) && count($widget->target_utm) > 0) {
            $matched = false;

            foreach ($widget->target_utm as $group) {
                $groupMatch = true;
                foreach ($group as $rule) {
                    $key = $rule['key'];
                    $val = $rule['val'];

                    // Сравниваем метку из сессии пользователя с правилом виджета
                    if (($utm[$key] ?? null) !== $val) {
                        $groupMatch = false;
                        break;
                    }
                }
                if ($groupMatch) {
                    $matched = true;
                    break;
                }
            }

            // Если таргет по UTM настроен, но совпадений нет — не показываем
            if (!$matched) return false;
        }

        return true;
    }

    private function matchMasks($path, $masks)
    {
        foreach ($masks as $mask) {
            // Превращаем маску (напр. /blog*) в регулярку
            $pattern = str_replace(['/', '*'], ['\/', '.*'], $mask);
            if (preg_match('/^' . $pattern . '$/i', $path)) return true;
        }
        return false;
    }

    public function track(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['widget_id']) && $data['event'] === 'click') {
            $this->logEvent($data['widget_id'], 'click', $request, $data['url'] ?? '/');
        }
        return response()->json(['status' => 'ok']);
    }

    private function logEvent($widgetId, $type, Request $request, $path, $utm = [])
    {
        WidgetStatistic::create([
            'widget_id'    => $widgetId,
            'event_type'   => $type,
            'url'          => $path,
            'utm_source'   => $utm['utm_source'] ?? $request->get('utm_source'),
            'utm_medium'   => $utm['utm_medium'] ?? $request->get('utm_medium'),
            'utm_campaign' => $utm['utm_campaign'] ?? $request->get('utm_campaign'),
            'utm_content'  => $utm['utm_content'] ?? $request->get('utm_content'),
            'utm_term'     => $utm['utm_term'] ?? $request->get('utm_term'),
            'ip'           => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'referer'      => $request->header('referer'),
            'query'        => $request->getQueryString(),
            'session_id'   => $request->get('session_id') ?? session()->getId(),
        ]);
    }

}

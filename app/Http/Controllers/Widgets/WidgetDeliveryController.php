<?php


namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Http\Controllers\Widgets\Services\TargetTimeManager;
use App\Models\Site;
use App\Models\Widget;
use App\Models\Widgets\WidgetStatistic;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;


class WidgetDeliveryController extends BaseCabinetController
{
    public function getPayload(Request $request)
    {
        // 1. Загружаем сайт с планом и фичами
        $site = Site::where('api_key', $request->get('key'))
            ->where('is_verified', true)
            ->with('activeSubscription.plan')
            ->first();

        if (!$site) return response()->json(['error' => 'Invalid key'], 403);

        // 2. Получаем лимиты
        $features = $site->plan_features ?? (new SubscriptionService($site))->loadFeatures();
        $limit = $features['shows_limit'];

        // Проверяем, исчерпан ли общий лимит показов
        $isLimitReached = ($limit !== -1 && $site->total_widgets_show >= $limit);

        $currentPath = $request->get('path', '/');
        $currentUtm = array_filter($request->only(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term']));

        $activeWidgets = $site->widgets()
            ->where('is_active', true)
            ->with('widgetType') // Загружаем тип, чтобы проверить категорию
            ->get()
            ->filter(function ($widget) use ($currentPath, $currentUtm, $request, $isLimitReached) {
                // ПРОВЕРКА ЛИМИТА:
                // Если лимит превышен И это НЕ lead_generation — скрываем виджет
                if ($isLimitReached && $widget->widgetType->category !== 'lead_generation') {
                    return false;
                }

                // Стандартные проверки (таргетинг по URL, UTM, времени и т.д.)
                return $this->shouldShowWidget($widget, $currentPath, $currentUtm, $request);
            })
            ->map(function ($widget) use ($request, $currentPath, $currentUtm) {
                $slug = $widget->widgetType->slug;
                $settings = $widget->settings;
                $skin = $settings['template'] ?? 'default';

                return [
                    'id'       => $widget->id,
                    'type'     => $slug,
                    'category' => $widget->widgetType->category, // Полезно для фронтенда
                    'settings' => $settings ?? [],
                    'behavior' => $widget->behavior ?? [],
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
    private function shouldShowWidget($widget, $path, $utm, $request)
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
        // 3. Проверка по времени
        $timeRules = $widget->target_time ?? [];
        if (!empty($timeRules)) {
            $timeManager = new TargetTimeManager();
            // Передаем часовой пояс пользователя из запроса
            $timezone = $request->get('timezone', config('app.timezone', 'UTC'));
            if (!$timeManager->shouldShow($timeRules, $timezone)) {
                return false;
            }
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
        try {
            $data = json_decode($request->getContent(), true);
            // $data = $request->json()->all();
            $widgetId = $data['widget_id'] ?? null;

            if (!$widgetId) {
                return response()->json(['status' => 'error', 'message' => 'Missing widget_id'], 400);
            }
            $type = $data['event'] ?? 'view';
            $path = $data['url'] ?? '/';
            $utm = $data['utm'] ?? [];
            // Вызываем логику проверки и записи
            $result = $this->logEvent($widgetId, $type, $request, $path, $utm);
            if (!$result) {
                return response()->json(['status' => 'error', 'message' => 'Widget inactive or limit reached'], 403);
            }
            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            // Логируем системную ошибку для себя, но фронтенду отдаем лаконичный ответ
            \Log::error("Widget Track Error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'System error'], 500);
        }
    }

    private function logEvent($widgetId, $type, Request $request, $path, $utm = [])
    {
        $widget = Widget::with(['widgetType', 'site'])
            ->where('is_active', true)
            ->find($widgetId);
        if (!$widget || !$widget->site || !$widget->site->is_active) {
            return null;
        }
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
        if ($type === 'view') {
            $widget->site->increment('total_widgets_show');
        }

        /*
        if ($result) {
            (new \App\Metrics\MetricsManager())->execute(
                $widget->site,
                $data['event'],
                [
                    'client_id' => $data['ym_client_id'] ?? null,
                    'widget_id' => $widget->id
                ]
            );
        }
        //*/


        return true;
    }

}

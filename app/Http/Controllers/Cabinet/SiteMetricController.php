<?php


namespace App\Http\Controllers\Cabinet;

use App\Models\Site;
use App\Models\Metrics\SiteMetric;
use App\Metrics\MetricsManager;
use Illuminate\Http\Request;

class SiteMetricController extends BaseCabinetController
{
    /**
     * Список всех доступных метрик
     */
    public function index(Site $site)
    {
        $this->authorizeAccess($site);

        // Список всех доступных метрик (хардкод или из конфига)
        $availableMetrics = config('metrics.available', [
            'yandex-metrika' => [
                'name' => 'Яндекс.Метрика',
                'icon' => 'fab fa-yandex',
                'description' => 'Отправка конверсий в Яндекс.Метрику',
                'driver' => \App\Metrics\Drivers\YandexMetrikaDriver::class
            ],
            /*
            'vk-pixel' => [
                'name' => 'VK Реклама',
                'icon' => 'fab fa-vk',
                'description' => 'VK Pixel для отслеживания конверсий',
                'driver' => \App\Metrics\Drivers\VkPixelDriver::class
            ],
            'google-analytics' => [
                'name' => 'Google Analytics 4',
                'icon' => 'fab fa-google',
                'description' => 'Отправка событий в GA4',
                'driver' => \App\Metrics\Drivers\GoogleAnalyticsDriver::class
            ]
            //*/
        ]);

        // Подключенные метрики для этого сайта
        $connectedMetrics = SiteMetric::where('site_id', $site->id)->get();

        return view('cabinet.sites.metrics.index', compact(
            'site',
            'availableMetrics',
            'connectedMetrics'
        ));
    }

    /**
     * Форма подключения/настройки метрики
     */
    public function configure(Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $metricConfig = config("metrics.available.{$metricSlug}");

        if (!$metricConfig) {
            abort(404, 'Метрика не найдена');
        }

        // Ищем или создаем запись
        $siteMetric = SiteMetric::firstOrCreate([
            'site_id' => $site->id,
            'type' => $metricSlug
        ]);

        return view('cabinet.sites.metrics.config', compact(
            'site',
            'metricSlug',
            'metricConfig',
            'siteMetric'
        ));
    }

    /**
     * Сохранение настроек метрики
     */
    // В методе update исправить сохранение настроек
    public function update(Request $request, Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $siteMetric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricSlug)
            ->firstOrFail();

        $validated = $request->validate([
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        // ✅ Важно: сохраняем settings как массив, даже если он пустой
        $currentSettings = $siteMetric->settings ?? [];
        $newSettings = $request->input('settings', []);

        // ✅ Специфичная валидация для разных метрик
        $this->validateMetricSettings($metricSlug, $newSettings);

        // ✅ Объединяем настройки
        $mergedSettings = array_merge($currentSettings, $newSettings);

        $siteMetric->update([
            'is_active' => $request->has('is_active'),
            'settings' => $mergedSettings  // ✅ Всегда сохраняем массив
        ]);

        // Если метрика активна - синхронизируем цели
        if ($siteMetric->is_active) {
            app(MetricsManager::class)->syncGoalsForSite($site, $metricSlug);
        }

        $metricConfig = config("metrics.available.{$metricSlug}");
        $metricName = $metricConfig['name'] ?? $metricSlug;

        return redirect()
            ->route('cabinet.sites.metrics.index', $site)
            ->with('success', "Настройки {$metricName} сохранены");
    }

    protected function validateMetricSettings(string $metricSlug, array $settings)
    {
        $rules = [
            'yandex-metrika' => [
                'counter_id' => 'nullable|string',  // ✅ Сделал nullable
                'token' => 'nullable|string'        // ✅ Сделал nullable
            ],
            'vk-pixel' => [
                'pixel_id' => 'nullable|string'
            ],
            'google-analytics' => [
                'measurement_id' => 'nullable|string',
                'api_secret' => 'nullable|string'
            ]
        ];

        if (isset($rules[$metricSlug])) {
            $validator = validator($settings, $rules[$metricSlug]);
            if ($validator->fails()) {
                abort(422, $validator->errors()->first());
            }
        }
    }

    /**
     * Отключение метрики
     */
    public function destroy(Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $siteMetric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricSlug)
            ->firstOrFail();

        $siteMetric->delete();

        return back()->with('success', 'Метрика отключена');
    }

    /**
     * Проверка подключения метрики
     */
    public function test(Site $site, string $metricSlug)
    {
        $this->authorizeAccess($site);

        $siteMetric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricSlug)
            ->first();

        if (!$siteMetric || !$siteMetric->is_active) {
            return response()->json(['success' => false, 'message' => 'Метрика не активна']);
        }

        $manager = new MetricsManager();
        $result = $manager->testConnection($site, $metricSlug);

        return response()->json($result);
    }

}

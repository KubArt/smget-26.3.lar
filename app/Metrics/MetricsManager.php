<?php
namespace App\Metrics;

use App\Models\Site;
use App\Models\Metrics\SiteMetric;
use Illuminate\Support\Str;

class MetricsManager
{
    /**
     * Отправка события во все активные метрики сайта
     */
    public function execute(Site $site, string $event, array $data): void
    {
        $metrics = SiteMetric::where('site_id', $site->id)
            ->where('is_active', true)
            ->get();

        foreach ($metrics as $metric) {
            $driver = $this->resolveDriver($metric);

            if ($driver) {
                try {
                    $driver->sendEvent($event, $data);
                } catch (\Exception $e) {
                    \Log::error("Metric [{$metric->type}] error: " . $e->getMessage(), [
                        'site_id' => $site->id,
                        'event' => $event
                    ]);
                }
            }
        }
    }

    /**
     * Синхронизация целей для конкретной метрики сайта
     */
    public function syncGoalsForSite(Site $site, string $metricType): void
    {
        $metric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricType)
            ->where('is_active', true)
            ->first();

        if (!$metric) {
            return;
        }

        $driver = $this->resolveDriver($metric);

        if ($driver && method_exists($driver, 'syncGoals')) {
            $goals = $this->extractGoalsFromSite($site);
            $driver->syncGoals($goals);
        }
    }

    /**
     * Проверка подключения
     */
    public function testConnection(Site $site, string $metricType): array
    {
        $metric = SiteMetric::where('site_id', $site->id)
            ->where('type', $metricType)
            ->first();

        if (!$metric || !$metric->is_active) {
            return ['success' => false, 'message' => 'Метрика не активна'];
        }

        $driver = $this->resolveDriver($metric);

        if ($driver && method_exists($driver, 'test')) {
            return $driver->test();
        }

        return ['success' => false, 'message' => 'Метод проверки не реализован'];
    }

    protected function resolveDriver(SiteMetric $metric)
    {
        $config = config("metrics.available.{$metric->type}");

        if (!$config || !isset($config['driver'])) {
            \Log::warning("Metric driver config not found for: {$metric->type}");
            return null;
        }

        $driverClass = $config['driver'];

        if (class_exists($driverClass)) {
            return new $driverClass($metric->settings);
        }

        \Log::warning("Metric driver class not found: {$driverClass}");
        return null;
    }

    /**
     * Получает список всех потенциальных целей для конкретной метрики
     */
    public function getPendingGoals(Site $site): array
    {
        $groupedGoals = [];

        foreach ($site->widgets()->where('is_active', true)->with('widgetType')->get() as $widget) {
            $widgetConfig = config("widgets.{$widget->widgetType->slug}");
            $availableGoals = $widgetConfig['available_goals'] ?? [];

            /***
             * TODO: можно сделать уникализацию целей отдельно по каждому виджет или по типу добавив id
             */

            $widgetGoals = [];
            foreach ($availableGoals as $goal) {
                $widgetGoals[] = [
                    'display_name' => $goal['name'],
                    'synonym' => $goal['synonym'] ?? '',
                    'event_key' => $goal['event'], // . "_" . $widget->id,
                    'type_label' => $widget->widgetType->name // Например: "LidUp Popup"
                ];
            }

            $groupedGoals[] = [
                'widget_name' => $widget->name,
                'widget_type' => $widget->widgetType->slug,
                'goals' => $widgetGoals
            ];
        }

        return $groupedGoals;
    }

    /**
     * Финальная синхронизация (вызывается после подтверждения пользователем)
     */
    public function syncAllWidgetsWithMetric(Site $site, SiteMetric $siteMetric): void
    {
        $driver = $this->resolveDriver($siteMetric);
        if (!$driver) return;

        $flatGoals = [];
        $grouped = $this->getPendingGoals($site);

        foreach ($grouped as $group) {
            foreach ($group['goals'] as $goal) {
                $flatGoals[] = [
                    'name' => $group['widget_name'] . ": " . $goal['display_name'],
                    'event' => $goal['event_key'],
                    'type' => 'action' // Тип для Яндекс.Метрики
                ];
            }
        }

        if (method_exists($driver, 'syncGoals')) {
            $driver->syncGoals($flatGoals);
        }
    }

}

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

    protected function extractGoalsFromSite(Site $site): array
    {
        $goals = [];

        // Извлекаем цели из активных виджетов
        foreach ($site->widgets()->where('is_active', true)->get() as $widget) {
            if (isset($widget->settings['goals']) && is_array($widget->settings['goals'])) {
                $goals = array_merge($goals, $widget->settings['goals']);
            }

            // Стандартные цели для виджета
            $goals[] = [
                'name' => $widget->name,
                'event' => "widget_{$widget->type}_submit",
                'conditions' => ['widget_id' => $widget->id]
            ];
        }

        return $goals;
    }
}

<?php


namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Widget;
use App\Models\Widgets\WidgetStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Http\Controllers\Cabinet\BaseCabinetController;

class WidgetStatisticsController extends BaseCabinetController
{
    public function getStatistic(Request $request, Site $site, Widget $widget)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day'); // day, week, month
        $utmSource = $request->get('utm_source');

        // Определяем формат даты для SQL в зависимости от группировки
        $groupFormat = match($groupBy) {
            'week' => 'YEARWEEK(created_at, 1)',
            'month' => 'DATE_FORMAT(created_at, "%Y-%m")',
            default => 'DATE(created_at)',
        };

        // Для красивых меток на графике (лейблов)
        $labelFormat = match($groupBy) {
            'week' => 'CONCAT("Неделя ", WEEK(created_at, 1))',
            'month' => 'DATE_FORMAT(created_at, "%M %Y")',
            default => 'DATE_FORMAT(created_at, "%d.%m")',
        };

        $query = WidgetStatistic::where('widget_id', $widget->id)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if ($utmSource) {
            $query->where('utm_source', $utmSource);
        }

        $stats = $query->select(
            DB::raw("$groupFormat as period"),
            DB::raw("MAX($labelFormat) as label"),
            DB::raw('SUM(CASE WHEN event_type = "view" THEN 1 ELSE 0 END) as views'),
            DB::raw('SUM(CASE WHEN event_type = "click" THEN 1 ELSE 0 END) as clicks')
        )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $responseData = [
            'labels' => $stats->pluck('label'),
            'views' => $stats->pluck('views'),
            'clicks' => $stats->pluck('clicks'),
            'totals' => [
                'views' => $stats->sum('views'),
                'clicks' => $stats->sum('clicks'),
                'ctr' => $stats->sum('views') > 0 ? number_format(($stats->sum('clicks') / $stats->sum('views')) * 100, 2) : 0
            ]
        ];

        if ($request->ajax()) {
            return response()->json($responseData);
        }
        $stats = collect();

        // Для первой загрузки страницы собираем список UTM
        $availableUtms = WidgetStatistic::where('widget_id', $widget->id)
            ->whereNotNull('utm_source')
            ->distinct()
            ->pluck('utm_source');

     //   return view('cabinet.widgets.statistics', compact('site', 'widget', 'availableUtms', 'dateFrom', 'dateTo'));
        return view('cabinet.widgets.statistics', compact('site', 'widget', 'availableUtms', 'dateFrom', 'dateTo', 'stats'));
    }
}

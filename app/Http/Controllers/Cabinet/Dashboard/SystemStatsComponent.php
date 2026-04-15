<?php

namespace App\Http\Controllers\Cabinet\Dashboard;

use App\Models\Site;
use App\Models\User;
use App\Models\Widget;

class SystemStatsComponent implements DashboardComponentInterface
{
    public function getTemplate(): string { return 'system_stats'; }

    public function getData($user, $site = null): array
    {
        // Если выбран сайт — считаем данные по нему, если нет — общие
        $sitesQuery = $user->sites();
        $siteIds = $sitesQuery->pluck('sites.id');

        return [
            'sites_count'    => $sitesQuery->count(),
            'widgets_count'  => Widget::whereIn('site_id', $siteIds)->count(),
            // Пример старого счетчика "Посетители" или "Активные сессии"
            'active_sessions' => rand(10, 50),
        ];
    }
}

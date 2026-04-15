<?php

namespace App\Http\Controllers\Cabinet\Dashboard;

use App\Models\Crm\Lead;

class LeadsStatsComponent implements DashboardComponentInterface
{
    public function getTemplate(): string { return 'leads_stats'; }

    public function getData($user, $site = null): array
    {
        $siteIds = $site ? [$site->id] : $user->sites()->pluck('sites.id')->toArray();
        $query = Lead::whereIn('site_id', $siteIds);

        return [
            'total_leads' => (clone $query)->count(),
            'new_leads'   => (clone $query)->where('status', 'new')->count(),
            'sites_count' => $user->sites()->count(), // Возвращаем старый счетчик
        ];
    }
}

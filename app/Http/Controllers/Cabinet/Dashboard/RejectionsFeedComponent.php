<?php

namespace App\Http\Controllers\Cabinet\Dashboard;

use App\Models\Crm\LeadStageHistory;

class RejectionsFeedComponent implements DashboardComponentInterface
{
    public function getTemplate(): string { return 'rejections_feed'; }

    public function getData($user, $site = null): array
    {
        $siteIds = $site ? [$site->id] : $user->sites()->pluck('sites.id')->toArray();

        $rejections = LeadStageHistory::where('to_stage', 'rejected')
            ->whereHas('lead', function($q) use ($siteIds) {
                $q->whereIn('site_id', $siteIds);
            })
            ->with('lead')
            ->latest()
            ->limit(5)
            ->get();

        return ['rejections' => $rejections];
    }
}

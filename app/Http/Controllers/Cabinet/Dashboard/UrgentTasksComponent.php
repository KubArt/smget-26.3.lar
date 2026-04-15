<?php

namespace App\Http\Controllers\Cabinet\Dashboard;

use App\Models\Crm\LeadTask;
use App\Models\Site;
use App\Models\User;

class UrgentTasksComponent implements DashboardComponentInterface
{
    public function getTemplate(): string { return 'urgent_tasks'; }

    public function getData(User $user, ?Site $site = null): array
    {
        $siteIds = $site ? [$site->id] : $user->sites()->pluck('sites.id')->toArray();

        $tasks = LeadTask::whereIn('lead_id', function($query) use ($siteIds) {
            $query->select('id')->from('leads')->whereIn('site_id', $siteIds);
        })
            ->where('assigned_to', $user->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->with('lead.client')
            ->limit(5)
            ->get();

        return ['urgent_tasks' => $tasks];
    }
}

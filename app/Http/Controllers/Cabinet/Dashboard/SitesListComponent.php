<?php


namespace App\Http\Controllers\Cabinet\Dashboard;

class SitesListComponent implements DashboardComponentInterface
{
    public function getTemplate(): string { return 'sites_list'; }

    public function getData($user, $site = null): array
    {
        // Если выбран конкретный сайт, показываем только его, иначе все
        $sites = $site ? collect([$site]) : $user->sites()->withCount('widgets')->get();
        return ['sites' => $sites];
    }
}

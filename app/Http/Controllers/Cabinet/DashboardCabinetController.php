<?php


namespace App\Http\Controllers\Cabinet;


use App\Http\Controllers\Cabinet\Dashboard\DashboardManager;
use App\Http\Controllers\Cabinet\Dashboard\LeadsStatsComponent;
use App\Http\Controllers\Cabinet\Dashboard\RejectionsFeedComponent;
use App\Http\Controllers\Cabinet\Dashboard\SitesListComponent;
use App\Http\Controllers\Cabinet\Dashboard\SystemStatsComponent;
use App\Http\Controllers\Cabinet\Dashboard\UrgentTasksComponent;
use App\Models\Site;
use Illuminate\Http\Request;

class DashboardCabinetController extends BaseCabinetController
{
    public function index(Request $request, DashboardManager $manager)
    {
        $user = auth()->user();
        $currentSite = $request->site_id ? Site::find($request->site_id) : null;

        // Регистрируем компоненты
        $dashboardContent = $manager
            ->addComponent(new LeadsStatsComponent()) // Сюда мы добавили $sites->count()
            ->addComponent(new UrgentTasksComponent())
            ->addComponent(new SitesListComponent())   // Возвращаем старый список сайтов
            ->addComponent(new SystemStatsComponent())    // Плитки-счетчики
            ->addComponent(new RejectionsFeedComponent()) // Отказы ("Золотая жила")
            ->render($user, $currentSite);

        return view('cabinet.dashboard.dashboard', [
            'sites' => $user->sites,
            'content' => $dashboardContent
        ]);
    }

    /*
    public function index(Request $request)
    {
        $user = auth()->user();
        $sites = $user->sites()->withCount('widgets')->get();

        // Если сайтов вообще нет, показываем welcome-заглушку
        if ($sites->isEmpty()) {
            return view('cabinet.welcome');
        }

        return view('cabinet.dashboard', compact('sites'));
    }
    //*/
}

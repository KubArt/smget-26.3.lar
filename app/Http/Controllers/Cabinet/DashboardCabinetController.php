<?php


namespace App\Http\Controllers\Cabinet;


use Illuminate\Support\Facades\Request;

class DashboardCabinetController extends BaseCabinetController
{
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
}

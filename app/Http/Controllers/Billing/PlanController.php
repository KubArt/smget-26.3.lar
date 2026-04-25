<?php


namespace App\Http\Controllers\Billing;


use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Models\Billing\Plan;

class PlanController extends BaseCabinetController
{
    public function index()
    {
        // Получаем активные планы
        $plans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();

        // Получаем сайты пользователя текущего воркспейса с их планами
        $workspace = auth()->user()->currentWorkspace();
        $sites = $workspace->sites()->with('activeSubscription.plan')->get();

        return view('cabinet.billing.plans', compact('plans', 'sites'));
    }
}

<?php


namespace App\Http\Controllers\Billing;


use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Models\Billing\Plan;

class PlanController extends BaseCabinetController
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->orderBy('price', 'asc')->get();
        // Подгружаем только те сайты, которые принадлежат юзеру
        $sites = auth()->user()->sites;

        return view('cabinet.billing.plans', compact('plans', 'sites'));
    }
}

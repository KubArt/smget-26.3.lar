<?php


namespace App\Http\Controllers\Cabinet;


class IndexCabinetController extends BaseCabinetController
{
    /**
     * Редирект на дашборд или на страницу создания первого сайта
     */
    public function index()
    {

        $user = auth()->user();

        if ($user->sites()->count() > 0) {
            return redirect()->route('cabinet.dashboard');
        }

        return view('cabinet.welcome'); // Экран "Создайте первый проект"
    }
}

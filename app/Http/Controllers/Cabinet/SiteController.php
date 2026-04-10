<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SiteController extends BaseCabinetController
{
    /**
     * Форма создания нового сайта
     */
    public function create()
    {
        return view('cabinet.sites.create');
    }

    /**
     * Сохранение сайта в базе
     */
    public function store(Request $request)
    {
        // Логику напишем следующим шагом
    }
}

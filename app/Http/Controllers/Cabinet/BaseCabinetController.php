<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
// Добавьте этот импорт
use Illuminate\Routing\Controllers\HasMiddleware;

class BaseCabinetController extends Controller
{
    /**
     * TODO: сделать проверки во всех модулях на принадлежность к сайту через кабинет для сотрудников
     */

    public function __construct()
    {
        // Проверяем, есть ли метод middleware, если нет - используем замыкание для инициализации
        $this->initBaseCabinet();




    }

    protected function initBaseCabinet()
    {
        // Вместо $this->middleware используем share через замыкание или напрямую,
        // если контроллер вызывается в контексте web-сессии
        view()->composer('cabinet.*', function ($view) {
            if (Auth::check()) {
                $view->with('currentWorkspace', Auth::user()->currentWorkspace());
            }
        });
    }

    protected function authorizeAccess(Site $site)
    {
        $workspace = Auth::user()->currentWorkspace();

        if (!$workspace || $site->workspace_id !== $workspace->id) {
            abort(403, 'Доступ запрещен: сайт не принадлежит вашему рабочему пространству.');
        }
    }
}

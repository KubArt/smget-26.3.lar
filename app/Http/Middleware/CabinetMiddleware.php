<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CabinetMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Если пользователь авторизован, делимся его данными со всеми шаблонами
        if (auth()->check()) {
            View::share('currentUser', auth()->user());
        }

        return $next($request);
    }
}

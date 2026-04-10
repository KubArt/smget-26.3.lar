<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Пытаемся достать сайт из параметров маршрута (например, /sites/{site}/edit)
        $site = $request->route('site');

        // Если это ID (строка), подтянем модель
        if (is_string($site)) {
            $site = \App\Models\Site::find($site);
        }

        // 2. Если сайт найден, проверяем подписку
        if ($site) {
            if (!$site->activeSubscription) {
                // Если запрос AJAX — возвращаем ошибку
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Для выполнения этого действия необходим активный тариф.'
                    ], 402);
                }

                // Если обычный переход — редирект на страницу тарифов с уведомлением
                return redirect()->route('cabinet.billing.plans.index')
                    ->with('error', "Для сайта {$site->domain} не найден активный тариф. Пожалуйста, выберите план.");
            }
        }

        return $next($request);
    }
}

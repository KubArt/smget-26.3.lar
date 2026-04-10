<?php


namespace App\Http\Controllers\Billing;


use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Models\Billing\Plan;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends BaseCabinetController
{

    // Метод для AJAX проверки возможности покупки

    public function checkBalance(Request $request)
    {
        $plan = Plan::findOrFail($request->plan_id);
        $user = auth()->user();

        return response()->json([
            'can_afford' => $user->balance >= $plan->price,
            'balance' => $user->balance,
            'price' => $plan->price,
            'diff' => $plan->price - $user->balance
        ]);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($request->plan_id);

        // Проверка доступа к сайту через Many-to-Many
        $site = $user->sites()->where('site_id', $request->site_id)->first();

        if (!$site) {
            return response()->json(['success' => false, 'message' => 'Сайт не найден или нет доступа.'], 403);
        }

        if ($user->balance < $plan->price) {
            return response()->json(['success' => false, 'message' => 'Недостаточно средств на балансе.'], 400);
        }

        try {
            DB::beginTransaction();

            // 1. Списание средств и запись в ЛОГ ТРАНЗАКЦИЙ
            $transaction = $user->transactions()->create([
                'amount' => -$plan->price,
                'type' => 'withdraw',
                'description' => "Оплата тарифа {$plan->name} для сайта {$site->domain}",
                'source_type' => Plan::class,
                'source_id' => $plan->id
            ]);

            // 2. Расчет даты (исправленная логика)
            $currentActive = $site->activeSubscription; // используем динамическое свойство

            $startFrom = ($currentActive && $currentActive->expires_at && $currentActive->expires_at->isFuture())
                ? $currentActive->expires_at
                : now();

            // 3. Создание подписки
            $site->subscriptions()->create([
                'plan_id' => $plan->id,
                'starts_at' => $startFrom,
                'expires_at' => $startFrom->copy()->addDays($plan->duration_days),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Тариф {$plan->name} успешно активирован!",
                'new_balance' => number_format($user->fresh()->balance, 0, '.', ' ') . ' ₽'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
}

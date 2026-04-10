<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;
use App\Models\Billing\Voucher;
use App\Models\Billing\Transaction;
use App\Models\Billing\Subscription;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    // Главная страница финансов
    public function index()
    {
        $transactions = auth()->user()->transactions()->latest()->paginate(15);
        return view('cabinet.billing.index', compact('transactions'));
    }

    // Страница выбора тарифов
    public function plans()
    {
        $plans = Plan::where('is_active', true)->get();
        $mySites = auth()->user()->sites; // Чтобы выбрать, какой сайт обновляем
        return view('cabinet.billing.plans', compact('plans', 'mySites'));
    }

    // Активация ваучера
    public function activateVoucher(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $voucher = Voucher::where('code', $request->code)
            ->where('uses', '>', 0)
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Ваучер недействителен, просрочен или уже использован.'
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($voucher) {
                $user = auth()->user();
                $activatedData = [];

                // 1. Если ваучер денежный
                if ($voucher->amount > 0) {
                    $user->transactions()->create([
                        'amount' => $voucher->amount,
                        'type' => 'deposit',
                        'description' => "Активация ваучера: {$voucher->code}",
                        'source_type' => Voucher::class,
                        'source_id' => $voucher->id
                    ]);
                    $activatedData[] = "Зачислено на баланс: " . number_format($voucher->amount, 0, '.', ' ') . " ₽";
                }

                // 2. Если ваучер на тариф (Plan)
                if ($voucher->plan_id) {
                    $plan = $voucher->plan;
                    $features = json_decode($plan->features, true);

                    $activatedData[] = "Активирован тариф: " . $plan->name;
                    if (isset($features['widgets_count'])) {
                        $activatedData[] = "Доступно виджетов: " . ($features['widgets_count'] == -1 ? 'Безлимитно' : $features['widgets_count']);
                    }
                }

                $voucher->decrement('uses');

                return [
                    'balance' => number_format($user->fresh()->balance, 0, '.', ' ') . ' ₽',
                    'items' => $activatedData
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Ваучер успешно активирован!',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ошибка при активации.'], 500);
        }
    }
}

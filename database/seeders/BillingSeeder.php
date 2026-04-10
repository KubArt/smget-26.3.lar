<?php

namespace Database\Seeders;

use App\Models\Billing\Plan;
use App\Models\Billing\Voucher;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем тарифные планы
        $plans = [
            [
                'slug' => 'light',
                'name' => 'LIGHT',
                'description' => 'Базовый тариф для небольших сайтов. Включает основные функции сбора лидов.',
                'price' => 7000,
                'duration_days' => 30,
                'features' => json_encode([
                    'widgets_count' => 1,
                    'sites_count' => 1,
                    'support' => 'email',
                    'branding' => true, // Наличие копирайта сервиса
                ]),
                'is_active' => true,
            ],
            [
                'slug' => 'medium',
                'name' => 'MEDIUM',
                'description' => 'Оптимальное решение для растущего бизнеса. Больше виджетов и расширенная статистика.',
                'price' => 5000, // Цена за ед. при определенных условиях или спецпредложение
                'duration_days' => 30,
                'features' => json_encode([
                    'widgets_count' => 3,
                    'sites_count' => 2,
                    'support' => 'chat',
                    'branding' => false,
                ]),
                'is_active' => true,
            ],
            [
                'slug' => 'full',
                'name' => 'FULL',
                'description' => 'Максимальный контроль и аналитика. Идеально для профессиональных маркетологов.',
                'price' => 4500,
                'duration_days' => 30,
                'features' => json_encode([
                    'widgets_count' => 10,
                    'sites_count' => 5,
                    'support' => 'priority',
                    'branding' => false,
                ]),
                'is_active' => true,
            ],
            [
                'slug' => 'extra',
                'name' => 'EXTRA',
                'description' => 'Корпоративный уровень. Безлимитные возможности для крупных сетей.',
                'price' => 4000,
                'duration_days' => 30,
                'features' => json_encode([
                    'widgets_count' => -1, // -1 как символ безлимита
                    'sites_count' => -1,
                    'support' => 'personal_manager',
                    'branding' => false,
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }

        // 2. Создаем тестовые ваучеры

        // Денежный ваучер (Пополнение баланса)
        Voucher::updateOrCreate(
            ['code' => 'WELCOME_2026'],
            [
                'name' => 'Приветственный бонус',
                'description' => 'Дает 5000 рублей на баланс пользователя для тестирования системы.',
                'amount' => 5000,
                'plan_id' => null,
                'expires_at' => now()->addMonths(6),
                'uses' => 100, // Например, для первых 100 человек
            ]
        );

        // Тарифный ваучер (Прямая активация плана LIGHT)
        $lightPlan = Plan::where('slug', 'light')->first();
        Voucher::updateOrCreate(
            ['code' => 'FREE_MONTH'],
            [
                'name' => 'Месяц в подарок',
                'description' => 'Активирует тариф LIGHT на 30 дней совершенно бесплатно.',
                'amount' => 0,
                'plan_id' => $lightPlan ? $lightPlan->id : null,
                'expires_at' => now()->addYear(),
                'uses' => 10,
            ]
        );

        // Ваучер на крупную сумму (для внутреннего тестирования)
        Voucher::updateOrCreate(
            ['code' => 'TEST_ADMIN_MONEY'],
            [
                'name' => 'Тестовое пополнение',
                'description' => 'Начисляет 50 000 рублей. Только для разработчиков.',
                'amount' => 50000,
                'plan_id' => null,
                'expires_at' => null,
                'uses' => 999,
            ]
        );
    }
}

<?php

namespace App\Widgets;

use App\Models\Widget;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FortuneWheelWidget implements WidgetContract
{
    public function getDesignForm(): string
    {
        return 'widgets.fortune-wheel.configuration';
    }

    public function getEditorConfig(Widget $widget): array
    {
        // 1. Получаем эталонную структуру из созданного нами конфига
        // (убедись, что файл config/widgets/fortune-wheel.php создан)
        $baseConfig = config("widgets.fortune-wheel.default_values.settings", []);

        // 2. Рекурсивно сливаем эталон с данными из БД.
        // БД в приоритете, но если какого-то ключа (например, новой анимации кнопки) нет,
        // он возьмется из эталона.
        $mergedSettings = array_replace_recursive($baseConfig, $widget->settings ?? []);

        return [
            'slug'     => 'fortune-wheel',
            'settings' => $mergedSettings,
            'skins'    => $this->getSkins('fortune-wheel')
        ];
    }

    function getSkins(string $slug): array
    {
        $skinsPath = public_path("widgets/{$slug}/skins");
        $skins = [];

        if (File::exists($skinsPath)) {
            $directories = File::directories($skinsPath);
            foreach ($directories as $dir) {
                $skinSlug = basename($dir);
                $skins[$skinSlug] = [
                    'name' => ucwords(str_replace('-', ' ', $skinSlug)),
                    'slug' => $skinSlug
                ];
            }
        }

        if (empty($skins)) {
            $skins['default'] = [
                'name' => 'Default',
                'slug' => 'default'
            ];
        }
        return $skins;
    }

    public function updateDesign(Widget $widget, array $data): bool
    {
        $baseConfig = config("widgets.fortune-wheel.default_values.settings", []);
        $inputSettings = $data['settings'] ?? [];

        // Глубокое слияние гарантирует, что массив призов не сбросится в пустой
        // и все новые поля анимации кнопки будут на месте.
        $finalSettings = array_replace_recursive($baseConfig, $inputSettings);

        return $widget->update([
            'settings' => $finalSettings
        ]);
    }

    /**
     * Обработка купонов перед сохранением
     */
    protected function processCoupons(array $newCoupons, array $oldCoupons): array
    {
        $processed = [];

        foreach ($newCoupons as $index => $coupon) {
            // Сохраняем старую статистику использования если есть
            $oldCoupon = collect($oldCoupons)->firstWhere('id', $coupon['id'] ?? null);

            $processed[] = [
                'id' => $coupon['id'] ?? (string) Str::uuid(),
                'name' => $coupon['name'] ?? 'Приз',
                'description' => $coupon['description'] ?? '',
                'probability' => (int)($coupon['probability'] ?? 0),
                'type' => $coupon['type'] ?? 'percentage',
                'value' => (float)($coupon['value'] ?? 0),
                //'code' => $coupon['generate_unique'] ? null : ($coupon['code'] ?? null),
                //'generate_unique' => (bool)($coupon['generate_unique'] ?? false),
                'expires_at' => $coupon['expires_at'] ?? null,
                'usage_limit' => (int)($coupon['usage_limit'] ?? 0),
                'used_count' => $oldCoupon['used_count'] ?? 0,
                'color' => $coupon['color'] ?? '#FF6B6B',
                'icon' => $coupon['icon'] ?? '🎁',
            ];
        }

        return $processed;
    }

    /**
     * Генерация сегментов колеса на основе купонов
     */
    protected function generateWheelSegments(array $coupons): array
    {
        $segments = [];
        $totalProbability = array_sum(array_column($coupons, 'probability'));

        if ($totalProbability === 0) {
            return $segments;
        }

        $currentAngle = 0;
        foreach ($coupons as $coupon) {
            $angle = ($coupon['probability'] / $totalProbability) * 360;

            $segments[] = [
                'id' => $coupon['id'],
                'name' => $coupon['name'],
                'angle' => $angle,
                'start_angle' => $currentAngle,
                'end_angle' => $currentAngle + $angle,
                'color' => $coupon['color'],
                'icon' => $coupon['icon'],
            ];

            $currentAngle += $angle;
        }

        return $segments;
    }

    /**
     * Генерация уникального кода купона
     */
    public static function generateUniqueCode(): string
    {
        return 'COUPON_' . strtoupper(Str::random(12));
    }

    /**
     * API: Получение купона после вращения
     */
    public static function spin(Widget $widget, array $userData): array
    {
        $settings = $widget->settings;
        $coupons = $settings['coupons'] ?? [];

        // Проверка лимитов
        $check = self::checkLimits($widget, $userData);
        if (!$check['allowed']) {
            return ['success' => false, 'message' => $check['message']];
        }

        // Выбор купона на основе вероятности
        $selectedCoupon = self::selectCoupon($coupons);

        if (!$selectedCoupon) {
            return ['success' => false, 'message' => 'Ошибка при выборе приза'];
        }

        // Генерация уникального кода если нужно
        if ($selectedCoupon['generate_unique']) {
            $selectedCoupon['code'] = self::generateUniqueCode();
        }

        // Проверка срока действия
        if ($selectedCoupon['expires_at'] && strtotime($selectedCoupon['expires_at']) < time()) {
            return ['success' => false, 'message' => $settings['messages']['coupon_expired'] ?? 'Купон просрочен'];
        }

        // Проверка лимита использования купона
        if ($selectedCoupon['usage_limit'] > 0 && $selectedCoupon['used_count'] >= $selectedCoupon['usage_limit']) {
            return ['success' => false, 'message' => $settings['messages']['coupon_usage_limit'] ?? 'Лимит купона исчерпан'];
        }

        // Сохраняем информацию о выигрыше
        self::saveWin($widget, $selectedCoupon, $userData);

        // Обновляем статистику
        $settings['statistics']['total_spins']++;
        if ($selectedCoupon['type'] !== 'no_prize') {
            $settings['statistics']['total_wins']++;
            $settings['statistics']['coupons_issued'][] = [
                'coupon_id' => $selectedCoupon['id'],
                'code' => $selectedCoupon['code'],
                'user_email' => $userData['email'] ?? null,
                'issued_at' => now()->toDateTimeString(),
            ];
        }

        $widget->update(['settings' => $settings]);

        return [
            'success' => true,
            'coupon' => $selectedCoupon,
            'message' => $selectedCoupon['type'] === 'no_prize'
                ? 'К сожалению, ничего не выиграли. Попробуйте еще раз!'
                : "Поздравляем! Вы выиграли: {$selectedCoupon['name']}",
        ];
    }

    /**
     * Выбор купона на основе вероятности
     */
    protected static function selectCoupon(array $coupons): ?array
    {
        $totalProbability = array_sum(array_column($coupons, 'probability'));
        if ($totalProbability === 0) return null;

        $random = mt_rand(1, $totalProbability);
        $current = 0;

        foreach ($coupons as $coupon) {
            $current += $coupon['probability'];
            if ($random <= $current) {
                return $coupon;
            }
        }

        return null;
    }

    /**
     * Проверка лимитов
     */
    protected static function checkLimits(Widget $widget, array $userData): array
    {
        $settings = $widget->settings;
        $limits = $settings['limits'] ?? [];
        $userKey = $userData['email'] ?? $userData['ip'] ?? session()->getId();

        // Тут должна быть логика проверки лимитов с использованием базы данных
        // Для примера возвращаем разрешение

        return ['allowed' => true, 'message' => null];
    }

    /**
     * Сохранение выигрыша
     */
    protected static function saveWin(Widget $widget, array $coupon, array $userData): void
    {
        // Сохранение в базу данных выигрыша
        // \App\Models\WidgetWin::create([...])
    }
}

<?php

namespace App\Widgets\FortuneWheel;

class Manifest
{
    public static function config(): array
    {
        return [
            'name' => 'Колесо фортуны',
            'slug' => 'fortune-wheel',
            'is_free' => false,
            'category' => 'lead_generation',
            'description' => 'Интерактивное колесо фортуны с купонами и ваучерами',
            'version' => '1.0.0',

            'default_settings' => [
                // Кнопка открытия
                'button' => [
                    'position' => 'bottom-right', // bottom-right, bottom-left, top-right, top-left, custom
                    'custom_position' => ['x' => 20, 'y' => 20],
                    'text' => '🎁 Крутить колесо',
                    'icon' => '🎡',
                    'bg_color' => '#FF6B6B',
                    'text_color' => '#FFFFFF',
                    'size' => 'medium', // small, medium, large
                    'border_radius' => '50px',
                    'show_on_load' => true,
                    'auto_open_delay' => 0, // секунд, 0 - не открывать автоматически
                ],

                // Колесо
                'wheel' => [
                    'size' => 400, // px
                    'rotation_speed' => 8, // секунд на оборот
                    'background_color' => '#FFFFFF',
                    'text_color' => '#FFFFFF',
                    'border_color' => '#FFD700',
                    'border_width' => 3,
                    'pointer_color' => '#FF4444',
                    'font_size' => 14,
                    'segments' => [], // Автоматически генерируются из купонов
                ],

                // Купоны (призы)
                'coupons' => [
                    [
                        'id' => '1',
                        'name' => 'Скидка 10%',
                        'description' => 'Скидка 10% на первый заказ',
                        'probability' => 30, // вес вероятности
                        'type' => 'percentage', // percentage, fixed, free_shipping, product
                        'value' => 10,
                        'code' => 'WELCOME10', // статичный код или null для генерации
                        'generate_unique' => false, // генерировать уникальный код
                        'expires_at' => '', // дата истечения
                        'usage_limit' => 0, // 0 - без лимита
                        'used_count' => 0,
                        'color' => '#FF6B6B',
                        'icon' => '🎁',
                    ],
                    [
                        'id' => '2',
                        'name' => 'Скидка 20%',
                        'description' => 'Скидка 20% на весь ассортимент',
                        'probability' => 20,
                        'type' => 'percentage',
                        'value' => 20,
                        'code' => null,
                        'generate_unique' => true,
                        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
                        'usage_limit' => 50,
                        'used_count' => 0,
                        'color' => '#4ECDC4',
                        'icon' => '🎉',
                    ],
                    [
                        'id' => '3',
                        'name' => 'Бесплатная доставка',
                        'description' => 'Бесплатная доставка любого заказа',
                        'probability' => 25,
                        'type' => 'free_shipping',
                        'value' => 0,
                        'code' => 'FREESHIP',
                        'generate_unique' => false,
                        'expires_at' => '',
                        'usage_limit' => 0,
                        'used_count' => 0,
                        'color' => '#FFE66D',
                        'icon' => '🚚',
                    ],
                    [
                        'id' => '4',
                        'name' => 'Попробуй еще раз',
                        'description' => 'К сожалению, вы ничего не выиграли',
                        'probability' => 25,
                        'type' => 'no_prize',
                        'value' => 0,
                        'code' => null,
                        'generate_unique' => false,
                        'expires_at' => '',
                        'usage_limit' => 0,
                        'used_count' => 0,
                        'color' => '#95A5A6',
                        'icon' => '😢',
                    ],
                ],

                // Форма сбора лидов
                'form' => [
                    'enabled' => true,
                    'title' => 'Поздравляем!',
                    'subtitle' => 'Введите ваши данные, чтобы получить приз',
                    'fields' => [
                        ['type' => 'text', 'name' => 'name', 'label' => 'Ваше имя', 'required' => true, 'placeholder' => 'Иван Иванов'],
                        ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true, 'placeholder' => 'ivan@example.com'],
                        ['type' => 'tel', 'name' => 'phone', 'label' => 'Телефон', 'required' => false, 'placeholder' => '+7 (999) 123-45-67'],
                    ],
                    'button_text' => 'Получить приз',
                    'success_message' => 'Ваш купон: {CODE}',
                    'webhook_url' => '',
                ],

                // Сообщения
                'messages' => [
                    'spin_limit_reached' => 'Вы уже использовали все попытки',
                    'already_spun_today' => 'Вы уже крутили колесо сегодня',
                    'coupon_expired' => 'К сожалению, срок действия купона истек',
                    'coupon_usage_limit' => 'Лимит использования купона исчерпан',
                    'email_exists' => 'Этот email уже использовался для получения купона',
                ],

                // Ограничения
                'limits' => [
                    'spins_per_user' => 1, // 0 - без лимита
                    'spins_per_day' => 1, // 0 - без лимита
                    'spins_total' => 0, // 0 - без лимита
                    'require_auth' => false, // только для авторизованных
                    'collect_email' => true, // обязательно собрать email
                ],

                // Анимации и дизайн
                'design' => [
                    'modal_bg_color' => '#FFFFFF',
                    'modal_text_color' => '#2C3E50',
                    'accent_color' => '#FF6B6B',
                    'animation_duration' => 0.5, // секунд
                    'confetti_enabled' => true,
                    'sound_enabled' => true,
                ],

                // Поведение
                'frequency' => 'once_session', // always, once_session, once_day, once_week
                'trigger_type' => 'click', // click, time, scroll, exit
                'delay' => 0,
                'scroll_percent' => 50,

                // Статистика
                'statistics' => [
                    'total_spins' => 0,
                    'total_wins' => 0,
                    'coupons_issued' => [],
                ],
            ],

            'fields' => [
                'button.position' => ['type' => 'select', 'label' => 'Позиция кнопки', 'options' => [
                    'bottom-right' => 'Снизу справа',
                    'bottom-left' => 'Снизу слева',
                    'top-right' => 'Сверху справа',
                    'top-left' => 'Сверху слева',
                    'custom' => 'Произвольная',
                ]],
                'button.text' => ['type' => 'text', 'label' => 'Текст кнопки'],
                'button.bg_color' => ['type' => 'color', 'label' => 'Цвет кнопки'],

                'wheel.size' => ['type' => 'number', 'label' => 'Размер колеса (px)'],
                'wheel.rotation_speed' => ['type' => 'range', 'label' => 'Скорость вращения', 'min' => 3, 'max' => 15],

                'coupons' => ['type' => 'repeater', 'label' => 'Купоны'],
                'form.enabled' => ['type' => 'boolean', 'label' => 'Показывать форму сбора данных'],
                'limits.spins_per_user' => ['type' => 'number', 'label' => 'Максимум попыток на пользователя'],
                'frequency' => ['type' => 'select', 'label' => 'Частота показа', 'options' => [
                    'always' => 'Всегда',
                    'once_session' => 'Раз за сессию',
                    'once_day' => 'Раз в день',
                    'once_week' => 'Раз в неделю',
                ]],
            ],
        ];
    }
}

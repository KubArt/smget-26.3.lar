<?php

return [
    'name' => 'Колесо Фортуны',
    'slug' => 'fortune-wheel',
    'is_free' => false,
    'category' => 'lead_generation',
    'description' => 'Интерактивное колесо фортуны с призами и формой сбора контактов',
    'version' => '1.0.0',

    'default_values' => [
        'settings' => [
            'template' => 'default',
            // API настройки (заполняются автоматически)
            'site_id' => null,
            'api_key' => null,
            'api_url' => null,
            // Настройки кнопки
            'button' => [
                'position' => 'bottom-right',
                'text' => 'Крутить колесо',
                'icon' => '🎡',
                'bg_color' => '#6366f1',
                'text_color' => '#ffffff',
                'size' => 'medium',
                'border_radius' => '50px',
                'auto_open_delay' => 0,
            ],

            // Настройки колеса
            'wheel' => [
                'rotation_speed' => 4,
                'text_color' => '#333333',
                'pointer_color' => '#ff4444',
                'font_size' => 13,
                'border_color' => '#ffffff',
                'border_width' => 3,
                'segments' => [
                    [
                        'label' => 'Скидка 10%',
                        'bg_color' => '#f1f5f9',
                        'value' => 'DISCOUNT10',
                        'enabled' => true,
                        'expiry_days' => 30,
                        'description' => 'Скидка 10% на первый заказ',
                    ],
                    [
                        'label' => 'Скидка 20%',
                        'bg_color' => '#e2e8f0',
                        'value' => 'DISCOUNT20',
                        'enabled' => true,
                        'expiry_days' => 30,
                        'description' => 'Скидка 20% на первый заказ',
                    ],
                    [
                        'label' => 'Скидка 30%',
                        'bg_color' => '#f1f5f9',
                        'value' => 'DISCOUNT30',
                        'enabled' => true,
                        'expiry_days' => 30,
                        'description' => 'Скидка 30% на первый заказ',
                    ],
                    [
                        'label' => 'Бесплатная доставка',
                        'bg_color' => '#e2e8f0',
                        'value' => 'FREESHIP',
                        'enabled' => true,
                        'expiry_days' => 30,
                        'description' => 'Бесплатная доставка по всему миру',
                    ],
                ],
            ],

            // Настройки формы
            'form' => [
                'contact_type' => 'tel',
                'button_text' => 'Играть',
                'title' => 'Поздравляем!',
                'success_message' => 'Ваш купон: {CODE}',
                'terms_text' => 'Я согласен с условиями розыгрыша',
            ],

            // Дизайн (все вместе)
            'design' => [
                // Модальное окно
                'modal_bg_color' => '#ffffff',
                'modal_text_color' => '#1f2937',
                'accent_color' => '#6366f1',
                'title' => 'Выиграйте приз!',
                'description' => 'Испытайте свою удачу прямо сейчас',
                // Эффекты кнопки
                'size' => 'medium',
                'hover_effect' => 'lift',
                'opacity' => 1,
            ],

            // Анимация кнопки
            'animation' => [
                'type' => 'wave',
            ],

            // Сообщения
            'messages' => [
                'reject_prize' => 'Вы отказались от приза. Жаль! Возвращайтесь еще!',
                'spin_limit_reached' => 'Вы уже использовали все попытки',
                'fill_contact' => 'Пожалуйста, укажите контактные данные',
                'accept_terms' => 'Пожалуйста, согласитесь с условиями',
            ],

            // Лимиты
            'limits' => [
                'spins_per_user' => 1,
            ],

            // Поведение при закрытии
            'close_behavior' => 'hide_session',
        ],

        // Системные настройки поведения
        'behavior' => [
            'trigger_type' => 'click',
            'delay' => 0,
            'scroll_percent' => 50,
            'click_selector' => '',
            'frequency' => 'once_session',
            'auto_close' => 0,
        ],
    ],
];

<?php

return [
    'name' => 'LidUp Popup',
    'slug' => 'lidup',
    'is_free' => false,
    'category' => 'lead_generation',
    'description' => 'Всплывающее окно для сбора лидов с таймером, формой и анимациями',
    'version' => '1.0.0',

    'default_values' => [
            'settings' => [
                // Корень settings (согласно твоим x-model)
                'template' => 'default',
                'position' => 'center',
                'size' => 'medium',
                'overlay_color' => 'rgba(0,0,0,0.7)',
                'animation_in' => 'fadeIn',
                'animation_out' => 'fadeOut',

                // Контент (напрямую в settings)
                'title' => 'Получите скидку 20%',
                'description' => 'Оставьте заявку прямо сейчас и получите персональную скидку',

                'has_image' => true,
                'image' => 'https://smget.ru/Site/images/home-2-636x480.jpg',
                'image_position' => 'left',
                'image_size' => '120',

                // Форма (напрямую в settings)
                'btn_text' => 'Отправить заявку',
                'success_message' => 'Спасибо! Мы свяжемся с вами в ближайшее время.',
                'error_message' => 'Произошла ошибка. Пожалуйста, попробуйте позже.',
                'webhook_url' => '',
                'form_fields' => [
                    ['type' => 'text', 'name' => 'name', 'label' => 'Ваше имя', 'required' => true, 'placeholder' => 'Иван Иванов'],
                    ['type' => 'tel', 'name' => 'phone', 'label' => 'Телефон', 'required' => true, 'placeholder' => '+7 (999) 123-45-67'],
                ],

                'bonus' => [
                    'enabled' => false,
                    'code' => 'PROMO2024',           // фиксированный или генерируемый
                    'code_type' => 'fixed',          // fixed, unique, random
                    'name' => 'Скидка 30%',
                    'description' => 'Скидка 30% на первый приём врача терапевта',
                    'message' => 'Покажите этот промокод при оплате',
                    'expiry_days' => 7,
                ],

                // Дизайн (единственный вложенный объект в твоем шаблоне)
                'design' => [
                    'bg_color' => '#FFFFFF',
                    'text_color' => '#1F2937',
                    'accent_color' => '#3B82F6',
                    'btn_color' => '#22C55E',
                    'btn_text_color' => '#FFFFFF',
                    'border_radius' => '12',
                ],
            ],



        'behavior' => [
            'trigger_type' => 'delay',
            'delay' => 5,
            'scroll_percent' => 0,
            'frequency' => 'once_session',
            'auto_close' => 0,
        ]
    ]
];

<?php

return [
    'name' => 'Alert Bar',
    'slug' => 'alert-bar',
    'is_free' => true,
    'category' => 'informational',
    'description' => 'Узкая информационная полоса вверху или внизу страницы',
    'version' => '1.0.0',

    'default_values' => [
        'settings' => [
            'template' => 'default',
            'position' => 'top', // top, bottom
            'fixed_on_scroll' => false,
            'has_button' => true,
            'text' => 'Скидка -20% на первый заказ до конца дня!',
            'btn_text' => 'Узнать больше',
            'link' => '',
            'design' => [
                'bg_color' => '#E63946',
                'text_color' => '#FFFFFF',
                'btn_color' => '#1D3557'
            ],
        ],
        'behavior' => [
            'trigger_type' => 'immediate', // immediate, delay, scroll, exit
            'delay' => 0,
            'scroll_percent' => 0,
            'frequency' => 'once_session',
            'auto_close' => 0, // auto_hide из старого манифеста
        ]
    ]
];

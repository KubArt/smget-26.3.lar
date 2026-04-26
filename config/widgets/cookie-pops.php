<?php

return [
    'name' => 'Cookie Pops',
    'description' => 'Уведомление об использовании Cookie на сайте',
    'is_free' => '1',
    'slug' => 'cookie-pops',
    'category' => 'informational',
    'version' => '2.1.0',

    'available_goals' => [
        [
            'name' => 'Cookie: Принятие условий',
            'event' => 'cookie_accept',
            'type' => 'action'
        ],
    ],
    // Эталонный объект для сохранения в БД (SiteWidget -> settings / behavior)
    'default_values' => [
        'settings' => [
            'template' => 'default',
            'content' => [
                'text' => 'Мы используем файлы cookie для улучшения работы сайта.',
                'btn_accept_text' => 'Принять',
                'btn_leave_text' => 'Покинуть сайт',
                'show_leave_btn' => true,
                'policy_text' => 'Политика конфиденциальности',
                'policy_url' => '/privacy',
                'position' => 'bottom-right'
            ],
            'design' => [
                'bg_color' => '#ffffff',
                'text_color' => '#2d3436',
                'btn_color' => '#0665d0',
            ],
        ],
        // Настройки поведения для SmWidget._init()
        'behavior' => [
            'trigger_type' => 'delay',
            'delay' => 1,
            'frequency' => 'once_session',
            'auto_close' => 0
        ]
    ]
];

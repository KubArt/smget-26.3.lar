<?php

return [
    'available' => [
        'yandex-metrika' => [
            'name' => 'Яндекс.Метрика',
            'icon' => 'fab fa-yandex',
            'description' => 'Отправка конверсий в Яндекс.Метрику через Offline Conversions API',
            'driver' => \App\Metrics\Drivers\YandexMetrikaDriver::class,

            // 👇 OAuth параметры
            'oauth' => [
                // ceed5283384b4546990f429e74bd6bb5
                'client_id' => env('YANDEX_CLIENT_ID'),
                // fd05793e98cc4b8d95b6c80cee39cc80
                'client_secret' => env('YANDEX_CLIENT_SECRET'),
                'redirect_uri' => env('YANDEX_REDIRECT_URI'),
                'auth_url' => 'https://oauth.yandex.ru/authorize',
                'token_url' => 'https://oauth.yandex.ru/token',
                'api_url' => 'https://api-metrika.yandex.net',
                'scopes' => 'metrika:read,metrika:write,metrika:expenses,metrika:user_params,metrika:offline_data'
            ]
        ],
        /*
        'vk-pixel' => [
            'name' => 'VK Реклама',
            'icon' => 'fab fa-vk',
            'description' => 'VK Pixel для отслеживания конверсий',
            'driver' => \App\Metrics\Drivers\VkPixelDriver::class,
        ],
        'google-analytics' => [
            'name' => 'Google Analytics 4',
            'icon' => 'fab fa-google',
            'description' => 'Отправка событий в GA4 через Measurement Protocol',
            'driver' => \App\Metrics\Drivers\GoogleAnalyticsDriver::class,
        ],
        //*/
    ],
];

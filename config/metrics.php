<?php

return [
    'available' => [
        'yandex-metrika' => [
            'name' => 'Яндекс.Метрика',
            'icon' => 'fab fa-yandex',
            'description' => 'Отправка конверсий в Яндекс.Метрику через Offline Conversions API',
            'driver' => \App\Metrics\Drivers\YandexMetrikaDriver::class,
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

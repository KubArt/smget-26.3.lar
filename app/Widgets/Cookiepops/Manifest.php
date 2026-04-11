<?php

namespace App\Widgets\CookiePops;

class Manifest
{
    public static function config(): array
    {
        return [
            'name' => 'Cookie Pops',
            'slug' => 'cookie-pops',
            'category' => 'informational',
            'description' => 'Уведомление об использовании Cookie на сайте',
            'version' => '2.0.0',

            // Дефолтные настройки (попадают в БД при установке)
            'default_settings' => [
                'text' => 'Мы используем файлы cookie для улучшения работы сайта.',
                'button_text' => 'Принять',
                'policy_link' => '/privacy-policy',
                'position' => 'bottom-right', // top, bottom, top-left, etc.
                'colors' => [
                    'bg' => '#ffffff',
                    'text' => '#000000',
                    'btn_bg' => '#007bff',
                    'btn_text' => '#ffffff'
                ],
                'delay' => 1, // Задержка появления в секундах
            ],

            // Описание полей для генерации UI в кабинете
            'fields' => [
                'text' => ['type' => 'textarea', 'label' => 'Текст сообщения'],
                'button_text' => ['type' => 'text', 'label' => 'Текст кнопки'],
                'position' => [
                    'type' => 'select',
                    'label' => 'Позиция',
                    'options' => [
                        'top' => 'Сверху (полоса)',
                        'bottom' => 'Снизу (полоса)',
                        'bottom-right' => 'В углу справа',
                        'bottom-left' => 'В углу слева'
                    ]
                ],
                'colors.bg' => ['type' => 'color', 'label' => 'Фон плашки'],
                // и так далее...
            ]
        ];
    }
}

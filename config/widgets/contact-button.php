<?php

return [
    'name' => 'Мультикнопка 2.0',
    'slug' => 'contact-button',
    'is_free' => true,
    'category' => 'conversion',
    'description' => 'Универсальный агрегатор контактов с поддержкой лид-форм и кастомных анимаций',
    'version' => '2.0.0',

    'default_values' => [
        'settings' => [
            // Корень settings (согласно x-model в шаблоне)
            'template' => 'default',
            'position' => 'right',
            'delay' => 2,
            'main_tooltip' => 'Свяжитесь с нами',

            // Динамический массив каналов

            'channels' => [
                        [
                            "id"=> "ch_1776190569417",
                            "type"=> "telegram",
                            "label"=> "Telegram",
                            "action_type"=> "link",
                            "action_value"=> "+79189678793",
                            "bg_color"=> "#0088cc",
                            "icon_color"=> "#ffffff",
                            "is_active"=> true
                        ],
                        [
                            "id"=> "ch_1776190586012",
                            "type"=> "phone",
                            "label"=> "Телефон",
                            "action_type"=> "link",
                            "action_value"=> "+7919123467",
                            "bg_color"=> "#34b7f1",
                            "icon_color"=> "#ffffff",
                            "is_active"=> true
                        ],
                        [
                            'id' => 'ch_default',
                            'type' => 'whatsapp',
                            'label' => 'WhatsApp',
                            'action_type' => 'link',
                            'action_value' => '+7919123467',
                            'bg_color' => '#25D366',
                            'icon_color' => '#ffffff',
                            'is_active' => true
                        ],
            ],

            // Вложенный объект дизайна (x-model="settings.design.*")
            'design' => [
                'main_color' => '#3b82f6',
                'icon_color' => '#ffffff',
                'size' => 'medium', // small, medium, large
                'opacity' => 1,
                'hover_effect' => 'lift' // lift, scale, glow
            ],

            // Вложенный объект анимации (x-model="settings.animation.*")
            'animation' => [
                'type' => 'wave', // wave, pulse, shake, jelly, none
                'enabled' => true,
                'speed' => 'normal'
            ],
        ],

        // Системные настройки поведения (из общего конфига)
        'behavior' => [
            'trigger_type' => 'delay',
            'delay' => 2,
            'frequency' => 'always',
        ]
    ]
];

<?php

namespace App\Widgets\ContactButton;

class Manifest
{
    public static function config(): array
    {
        return [
            'name' => 'Мультикнопка 2.0',
            'slug' => 'contact-button',
            'is_free' => true,
            'category' => 'conversion',
            'description' => 'Универсальный агрегатор контактов с поддержкой лид-форм и кастомных анимаций',
            'version' => '2.0.0',

            'default_settings' => [
                // Основные настройки
                'position' => 'bottom-right',
                'delay' => 2,
                'is_active' => true,
                'main_tooltip' => 'Свяжитесь с нами', // Новое поле

                // Массив каналов (динамический)
                'channels' => [
                    [
                        'id' => 'ch_1',
                        'type' => 'whatsapp',
                        'label' => 'WhatsApp',
                        'action_type' => 'link',
                        'action_value' => '',
                        'bg_color' => '#25D366',
                        'icon_color' => '#ffffff',
                        'is_active' => true
                    ]
                ],

                // Визуальные настройки кнопок
                'design' => [
                    'main_btn_color' => '#007bff',
                    'main_icon_color' => '#ffffff',
                    'size' => 'medium', // small, medium, large
                    'opacity' => 1.0,   // от 0.1 до 1.0
                    'hover_effect' => 'lift', // lift, scale, glow
                    'border_radius' => '50%', // круглая или скругленный квадрат
                ],

                // Настройки анимации (Pulse)
                'animation' => [
                    'type' => 'wave', // wave (волна), pulse (пульсация), shake (тряска), jelly (желе), none
                    'speed' => 'normal', // slow, normal, fast
                ],
            ],

            // Поля для генератора форм (используем как справочник для Blade)
            'fields' => [
                'position' => [
                    'type' => 'select',
                    'options' => ['bottom-right' => 'Справа', 'bottom-left' => 'Слева', 'top-right' => 'Справа сверху', 'top-left' => 'Слева сверху']
                ],
                'animation.type' => [
                    'type' => 'select',
                    'options' => [
                        'wave' => 'Волна (расходящиеся круги)',
                        'pulse' => 'Пульсация (изменение размера)',
                        'shake' => 'Тряска (внимание)',
                        'jelly' => 'Желе (мягкое сжатие)',
                        'none' => 'Без анимации'
                    ]
                ],
                'design.size' => [
                    'type' => 'select',
                    'options' => ['small' => 'Маленькая', 'medium' => 'Средняя', 'large' => 'Большая']
                ],
                'design.opacity' => ['type' => 'range', 'min' => 0.1, 'max' => 1.0, 'step' => 0.1],
                'design.hover_effect' => [
                    'type' => 'select',
                    'options' => ['lift' => 'Приподнимание', 'scale' => 'Увеличение', 'glow' => 'Свечение']
                ]
            ]
        ];
    }
}

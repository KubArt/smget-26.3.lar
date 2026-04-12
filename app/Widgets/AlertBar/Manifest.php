<?php

namespace App\Widgets\AlertBar;

class Manifest
{
    public static function config(): array
    {
        return [
            'name' => 'Alert Bar',
            'slug' => 'alert-bar',
            'is_free' => true,
            'category' => 'informational',
            'description' => 'Узкая информационная полоса вверху или внизу страницы',
            'version' => '1.0.0',

            // Дефолтные настройки (соответствуют AlertBarWidget::updateDesign)
            'default_settings' => [
                // Контент
                'text' => 'Скидка -20% на первичный прием до конца недели!',
                'link' => 'https://smile-center.ru/promo',
                'btn_text' => 'Узнать больше',
                'has_button' => true,

                // Расположение и вид
                'position' => 'top',
                'fixed_on_scroll' => true,
                'template' => 'default',
                'design' => [
                    'bg_color' => '#E63946',
                    'text_color' => '#FFFFFF',
                    'btn_color' => '#1D3557'
                ],

                // Поведение (Маркетинговые инструменты)
                'delay' => 0,              // Задержка перед появлением (сек)
                'auto_hide' => 0,          // Скрывать автоматически через X сек (0 - не скрывать)
                'frequency' => 'once_session', // Периодичность: 'always', 'once_session', 'once_day'
                'close_behavior' => 'hide_forever', // При закрытии: 'hide_session', 'hide_forever'
                'scroll_trigger' => 0,     // Показать после прокрутки % страницы (0 - сразу)
            ],

            // Описание полей для генерации UI формы в кабинете
            'fields' => [
                'text' => ['type' => 'text', 'label' => 'Текст объявления'],
                'link' => ['type' => 'text', 'label' => 'Ссылка кнопки (URL)'],
                'btn_text' => ['type' => 'text', 'label' => 'Текст кнопки'],
                'has_button' => ['type' => 'boolean', 'label' => 'Показывать кнопку'],
                'position' => [
                    'type' => 'select',
                    'label' => 'Расположение',
                    'options' => [
                        'top' => 'Сверху страницы',
                        'bottom' => 'Снизу страницы'
                    ]
                ],
                'fixed_on_scroll' => [
                    'type' => 'boolean',
                    'label' => 'Фиксировать при прокрутке',
                    'help' => 'Только для верхнего положения. Если включено - полоса всегда вверху экрана'
                ],
                'design.bg_color' => ['type' => 'color', 'label' => 'Цвет фона'],
                'design.text_color' => ['type' => 'color', 'label' => 'Цвет текста'],
                'design.btn_color' => ['type' => 'color', 'label' => 'Цвет кнопки'],
                'delay' => ['type' => 'number', 'label' => 'Задержка появления (сек)'],

                'frequency' => [
                    'type' => 'select',
                    'label' => 'Частота показа',
                    'options' => [
                        'always' => 'На каждой странице',
                        'once_session' => 'Один раз за сессию',
                        'once_day' => 'Один раз в сутки'
                    ]
                ],
                'auto_hide' => [
                    'type' => 'number',
                    'label' => 'Авто-скрытие (сек)',
                    'help' => '0 — оставить до закрытия пользователем'
                ],
                'scroll_trigger' => [
                    'type' => 'range',
                    'label' => 'Показать при прокрутке (%)',
                    'min' => 0, 'max' => 100
                ],
                'close_behavior' => [
                    'type' => 'select',
                    'label' => 'Если пользователь закрыл',
                    'options' => [
                        'hide_session' => 'Не показывать до конца сессии',
                        'hide_forever' => 'Больше никогда не показывать'
                    ]
                ]
            ]
        ];
    }
}

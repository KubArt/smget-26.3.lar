<?php

namespace App\Widgets\LidUp;

class Manifest
{
    public static function config(): array
    {
        return [
            'name' => 'LidUp Popup',
            'slug' => 'lidup',
            'is_free' => false,
            'category' => 'lead_generation',
            'description' => 'Всплывающее окно для сбора лидов с таймером, формой и анимациями',
            'version' => '1.0.0',

            'default_settings' => [
                // Контент
                'title' => 'Получите скидку 20%',
                'description' => 'Оставьте заявку прямо сейчас и получите персональную скидку',
                'image' => '',
                'image_position' => 'left',
                'image_size' => '120',
                'has_image' => false,

                // Форма
                'form_fields' => [
                    ['type' => 'text', 'name' => 'name', 'label' => 'Ваше имя', 'required' => true, 'placeholder' => 'Иван Иванов'],
                    ['type' => 'tel', 'name' => 'phone', 'label' => 'Телефон', 'required' => true, 'placeholder' => '+7 (999) 123-45-67'],
                    ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => false, 'placeholder' => 'mail@example.com'],
                ],
                'btn_text' => 'Отправить заявку',
                'btn_color' => '#22C55E',
                'success_message' => 'Спасибо! Мы свяжемся с вами в ближайшее время.',
                'error_message' => 'Произошла ошибка. Пожалуйста, попробуйте позже.',
                'webhook_url' => '',

                // Таймер
                'has_timer' => true,
                'timer_target_date' => '', // '2024-12-31 23:59:59'
                'timer_title' => 'До конца акции осталось:',
                'timer_days_text' => 'дней',
                'timer_hours_text' => 'часов',
                'timer_minutes_text' => 'минут',
                'timer_seconds_text' => 'секунд',

                // Поведение
                'trigger_type' => 'time', // time, scroll, exit, click
                'delay' => 3, // секунд до появления
                'scroll_percent' => 50, // при скролле на X%
                'exit_intent' => true, // при уходе мыши
                'frequency' => 'once_session', // always, once_session, once_day, once_week
                'close_behavior' => 'hide_session', // hide_session, hide_forever
                'auto_close' => 0, // авто-закрытие через секунд (0 - не закрывать)

                // Дизайн
                'position' => 'center',
                'size' => 'medium', // small, medium, large
                'animation_in' => 'fadeIn',
                'animation_out' => 'fadeOut',
                'overlay_color' => 'rgba(0,0,0,0.7)',
                'design' => [
                    'bg_color' => '#FFFFFF',
                    'text_color' => '#1F2937',
                    'accent_color' => '#3B82F6',
                    'btn_color' => '#22C55E',
                    'btn_text_color' => '#FFFFFF',
                    'border_radius' => '16',
                ],

                // Шаблон
                'template' => 'default',
            ],

            'fields' => [
                // Контент
                'title' => ['type' => 'text', 'label' => 'Заголовок'],
                'description' => ['type' => 'textarea', 'label' => 'Описание'],
                'has_image' => ['type' => 'boolean', 'label' => 'Показывать изображение'],
                'image' => ['type' => 'image', 'label' => 'Изображение'],
                'image_position' => ['type' => 'select', 'label' => 'Позиция изображения', 'options' => ['left' => 'Слева', 'right' => 'Справа', 'top' => 'Сверху', 'bottom' => 'Снизу']],

                // Форма
                'form_fields' => ['type' => 'repeater', 'label' => 'Поля формы'],
                'btn_text' => ['type' => 'text', 'label' => 'Текст кнопки'],
                'success_message' => ['type' => 'text', 'label' => 'Сообщение после отправки'],
                'webhook_url' => ['type' => 'text', 'label' => 'Webhook URL'],

                // Таймер
                'has_timer' => ['type' => 'boolean', 'label' => 'Показывать таймер'],
                'timer_target_date' => ['type' => 'datetime', 'label' => 'Дата окончания'],

                // Поведение
                'trigger_type' => ['type' => 'select', 'label' => 'Тиггер показа', 'options' => ['time' => 'По времени', 'scroll' => 'При скролле', 'exit' => 'При уходе мыши', 'click' => 'По клику']],
                'delay' => ['type' => 'number', 'label' => 'Задержка (сек)'],
                'scroll_percent' => ['type' => 'range', 'label' => 'Скролл %'],
                'frequency' => ['type' => 'select', 'label' => 'Частота показа', 'options' => ['always' => 'Всегда', 'once_session' => 'Раз за сессию', 'once_day' => 'Раз в день', 'once_week' => 'Раз в неделю']],
                'close_behavior' => ['type' => 'select', 'label' => 'При закрытии', 'options' => ['hide_session' => 'Скрыть до конца сессии', 'hide_forever' => 'Скрыть навсегда']],

                // Дизайн
                'size' => ['type' => 'select', 'label' => 'Размер', 'options' => ['small' => 'Маленький', 'medium' => 'Средний', 'large' => 'Большой']],
                'animation_in' => ['type' => 'select', 'label' => 'Анимация появления', 'options' => ['fadeIn' => 'Плавное появление', 'slideInUp' => 'Снизу вверх', 'slideInDown' => 'Сверху вниз', 'zoomIn' => 'Увеличение']],
                'design.bg_color' => ['type' => 'color', 'label' => 'Цвет фона'],
                'design.accent_color' => ['type' => 'color', 'label' => 'Акцентный цвет'],
            ],
        ];
    }
}

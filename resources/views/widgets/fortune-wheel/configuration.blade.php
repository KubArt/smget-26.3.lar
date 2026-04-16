@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="fortuneWheelEditor({{ json_encode($config) }})" x-init="init">
        <div class="row">
            <!-- Левая колонка настроек -->
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка Колеса Фортуны</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form id="saveForm" @submit.prevent="saveConfig">
                        @csrf

                        <!-- Вкладки -->
                            <ul class="nav nav-tabs nav-tabs-alt mb-3" role="tablist" id="widgetTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-tab="button-tab" href="#" @click.prevent="switchTab('button-tab')">Кнопка</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="wheel-tab" href="#" @click.prevent="switchTab('wheel-tab')">Колесо</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="coupons-tab" href="#" @click.prevent="switchTab('coupons-tab')">Купоны</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="form-tab" href="#" @click.prevent="switchTab('form-tab')">Форма</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="limits-tab" href="#" @click.prevent="switchTab('limits-tab')">Лимиты</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- ==================== ВКЛАДКА КНОПКА ==================== -->
                                <div class="tab-pane active" id="button-tab" data-pane="button-tab">
                                    <div class="mb-3">
                                        <label class="form-label">Позиция кнопки</label>
                                        <select class="form-select" x-model="settings.button.position">
                                            <option value="bottom-right">Снизу справа</option>
                                            <option value="bottom-left">Снизу слева</option>
                                            <option value="top-right">Сверху справа</option>
                                            <option value="top-left">Сверху слева</option>
                                            <option value="custom">Произвольная</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" x-show="settings.button.position === 'custom'">
                                        <label class="form-label">Позиция (px)</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="number" class="form-control" placeholder="X (отступ справа)" x-model="settings.button.custom_position.x">
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control" placeholder="Y (отступ снизу)" x-model="settings.button.custom_position.y">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Текст кнопки</label>
                                        <input type="text" class="form-control" x-model="settings.button.text">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Иконка</label>
                                        <input type="text" class="form-control" x-model="settings.button.icon">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Размер кнопки</label>
                                        <select class="form-select" x-model="settings.button.size">
                                            <option value="small">Маленькая</option>
                                            <option value="medium">Средняя</option>
                                            <option value="large">Большая</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Радиус скругления</label>
                                        <input type="text" class="form-control" placeholder="50px, 10px, 20px" x-model="settings.button.border_radius">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет фона кнопки</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.button.bg_color">
                                            <input type="text" class="form-control" x-model="settings.button.bg_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет текста кнопки</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.button.text_color">
                                            <input type="text" class="form-control" x-model="settings.button.text_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" x-model="settings.button.show_on_load">
                                            <label class="form-check-label">Показывать кнопку при загрузке страницы</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Авто-открытие (сек)</label>
                                        <input type="number" class="form-control" min="0" max="30" x-model="settings.button.auto_open_delay">
                                        <small class="text-muted">0 - не открывать автоматически</small>
                                    </div>


                                    <hr>

                                    <!-- Общие настройки поведения -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Триггер показа</label>
                                        <select class="form-select" x-model="settings.trigger_type">
                                            <option value="click">По клику на кнопку</option>
                                            <option value="time">По таймеру</option>
                                            <option value="scroll">При прокрутке страницы</option>
                                            <option value="exit">При уходе мыши с окна</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" x-show="settings.trigger_type === 'time'">
                                        <label class="form-label">Задержка перед показом (сек)</label>
                                        <input type="number" class="form-control" min="0" max="30" step="0.5" x-model="settings.delay">
                                    </div>

                                    <div class="mb-3" x-show="settings.trigger_type === 'scroll'">
                                        <label class="form-label">Процент прокрутки для показа</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="range" class="form-range flex-grow-1" min="0" max="100" x-model="settings.scroll_percent">
                                            <span class="badge bg-secondary" x-text="settings.scroll_percent + '%'"></span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Частота показа</label>
                                        <select class="form-select" x-model="settings.frequency">
                                            <option value="always">Всегда показывать</option>
                                            <option value="once_session">Один раз за сессию</option>
                                            <option value="once_day">Один раз в день</option>
                                            <option value="once_week">Один раз в неделю</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Поведение при закрытии</label>
                                        <select class="form-select" x-model="settings.close_behavior">
                                            <option value="hide_session">Не показывать до конца сессии</option>
                                            <option value="hide_forever">Больше никогда не показывать</option>
                                        </select>
                                    </div>

                                </div>

                                <!-- ==================== ВКЛАДКА КОЛЕСО ==================== -->
                                <div class="tab-pane" id="wheel-tab" data-pane="wheel-tab" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Размер колеса (px)</label>
                                        <input type="number" class="form-control" min="200" max="600" step="20" x-model="settings.wheel.size">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Скорость вращения (сек)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="range" class="form-range flex-grow-1" min="3" max="15" step="0.5" x-model="settings.wheel.rotation_speed">
                                            <span class="badge bg-secondary" x-text="settings.wheel.rotation_speed + ' сек'"></span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет фона колеса</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.wheel.background_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.background_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет текста на сегментах</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.wheel.text_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.text_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет границы сегментов</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.wheel.border_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.border_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Толщина границы (px)</label>
                                        <input type="number" class="form-control" min="0" max="10" x-model="settings.wheel.border_width">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет стрелки</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.wheel.pointer_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.pointer_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Размер шрифта (px)</label>
                                        <input type="number" class="form-control" min="10" max="24" x-model="settings.wheel.font_size">
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА КУПОНЫ ==================== -->
                                <div class="tab-pane" id="coupons-tab" data-pane="coupons-tab" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Призы и купоны</span>
                                            <button type="button" class="btn btn-sm btn-primary" @click="addCoupon">
                                                <i class="fa fa-plus me-1"></i> Добавить приз
                                            </button>
                                        </label>

                                        <div class="coupons-list">
                                            <template x-for="(coupon, index) in settings.coupons" :key="index">
                                                <div class="card mb-2 border">
                                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" x-model="coupon.enabled" :id="'coupon_enabled_' + index" style="cursor: pointer;">
                                                                <label class="form-check-label small fw-bold" :for="'coupon_enabled_' + index" x-text="coupon.name || 'Новый приз'" style="cursor: pointer;"></label>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeCoupon(index)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body py-2" x-show="coupon.enabled">
                                                        <div class="row g-2">
                                                            <div class="col-5">
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.name" placeholder="Название приза">
                                                            </div>
                                                            <div class="col-3">
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" class="form-control" x-model="coupon.probability" min="0" max="100" placeholder="Вес">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="input-group input-group-sm">
                                                                    <input type="color" class="form-control form-control-color" style="width: 35px; padding: 2px;" x-model="coupon.color">
                                                                    <input type="text" class="form-control" x-model="coupon.color" placeholder="#FF6B6B" maxlength="7">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row g-2 mt-2">
                                                            <div class="col-12">
                                                                <textarea class="form-control form-control-sm" rows="1" x-model="coupon.description" placeholder="Описание приза"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row g-2 mt-2">
                                                            <div class="col-5">
                                                                <div class="input-group input-group-sm">
                                                                    <span class="input-group-text">Промокод</span>
                                                                    <input type="text" class="form-control" x-model="coupon.code" :disabled="coupon.generate_unique" placeholder="Введите код">
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="form-check mt-1">
                                                                    <input class="form-check-input" type="checkbox" x-model="coupon.generate_unique" :id="'gen_unique_' + index">
                                                                    <label class="form-check-label small" :for="'gen_unique_' + index">Уникальный</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-3">
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" class="form-control" x-model="coupon.expiry_days" min="0" placeholder="7">
                                                                    <span class="input-group-text">дней</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer py-1 bg-white" x-show="!coupon.enabled">
                                                        <small class="text-muted">Приз отключен</small>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div x-show="!settings.coupons || settings.coupons.length === 0" class="text-center text-muted py-4 border rounded">
                                            <i class="fa fa-gift fa-3x mb-2 opacity-25"></i>
                                            <p class="mb-0">Нет добавленных призов</p>
                                            <small>Нажмите "Добавить приз" чтобы создать первый приз</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА ФОРМА ==================== -->
                                <div class="tab-pane" id="form-tab" data-pane="form-tab" style="display: none;">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" x-model="settings.form.enabled" id="formEnabled">
                                            <label class="form-check-label" for="formEnabled">Показывать форму сбора данных после выигрыша</label>
                                        </div>
                                    </div>

                                    <div x-show="settings.form.enabled">
                                        <div class="mb-3">
                                            <label class="form-label">Заголовок формы</label>
                                            <input type="text" class="form-control" x-model="settings.form.title" placeholder="Поздравляем!">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Подзаголовок формы</label>
                                            <input type="text" class="form-control" x-model="settings.form.subtitle" placeholder="Введите данные для получения приза">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Текст кнопки отправки</label>
                                            <input type="text" class="form-control" x-model="settings.form.button_text" placeholder="Получить приз">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Сообщение после успешной отправки</label>
                                            <input type="text" class="form-control" x-model="settings.form.success_message" placeholder="Ваш купон: {CODE}">
                                            <small class="text-muted">Используйте {CODE} для подстановки кода купона</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Webhook URL для отправки данных</label>
                                            <input type="url" class="form-control" x-model="settings.form.webhook_url" placeholder="https://your-site.com/webhook">
                                            <small class="text-muted">Данные формы будут отправлены на этот URL</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span>Поля формы</span>
                                                <button type="button" class="btn btn-sm btn-primary" @click="addFormField">
                                                    <i class="fa fa-plus me-1"></i> Добавить поле
                                                </button>
                                            </label>

                                            <div class="form-fields-list" style="max-height: 300px; overflow-y: auto;">
                                                <template x-for="(field, idx) in settings.form.fields" :key="idx">
                                                    <div class="border rounded p-2 mb-2 bg-white">
                                                        <div class="row g-2 align-items-center">
                                                            <div class="col-3">
                                                                <select class="form-select form-select-sm" x-model="field.type">
                                                                    <option value="text">Текстовое поле</option>
                                                                    <option value="email">Email</option>
                                                                    <option value="tel">Телефон</option>
                                                                    <option value="textarea">Многострочный текст</option>
                                                                    <option value="hidden">Скрытое поле</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-3">
                                                                <input type="text" class="form-control form-control-sm" placeholder="Название поля" x-model="field.label">
                                                            </div>
                                                            <div class="col-3">
                                                                <input type="text" class="form-control form-control-sm" placeholder="Placeholder" x-model="field.placeholder">
                                                            </div>
                                                            <div class="col-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" x-model="field.required" :id="'req_' + idx">
                                                                    <label class="form-check-label small" :for="'req_' + idx">Обязательное</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-1">
                                                                <button type="button" class="btn btn-sm btn-link text-danger p-0" @click="removeFormField(idx)">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2" x-show="field.type === 'hidden'">
                                                            <div class="col-12">
                                                                <label class="small text-muted">Значение по умолчанию</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="field.default_value" placeholder="Значение скрытого поля">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>

                                            <div x-show="!settings.form.fields || settings.form.fields.length === 0" class="text-center text-muted py-3 border rounded">
                                                <i class="fa fa-edit fa-2x mb-2 opacity-25"></i>
                                                <p class="mb-0 small">Нет полей формы</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА ЛИМИТЫ ==================== -->
                                <div class="tab-pane" id="limits-tab" data-pane="limits-tab" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Максимум попыток на пользователя</label>
                                        <input type="number" class="form-control" min="0" x-model="settings.limits.spins_per_user">
                                        <small class="text-muted">0 - без ограничений</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Максимум попыток в день</label>
                                        <input type="number" class="form-control" min="0" x-model="settings.limits.spins_per_day">
                                        <small class="text-muted">0 - без ограничений</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Общий лимит попыток</label>
                                        <input type="number" class="form-control" min="0" x-model="settings.limits.spins_total">
                                        <small class="text-muted">0 - без ограничений</small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" x-model="settings.limits.require_auth" id="requireAuth">
                                            <label class="form-check-label" for="requireAuth">Только для авторизованных пользователей</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" x-model="settings.limits.collect_email" id="collectEmail">
                                            <label class="form-check-label" for="collectEmail">Обязательно собирать email перед выдачей приза</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-alt-primary w-100">
                                <i class="fa fa-save me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ПРЕДПРОСМОТР -->
            <!-- ПРЕДПРОСМОТР -->
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }" x-init="$watch('previewMode', () => $dispatch('preview-mode-changed', previewMode))">
                @include("widgets.configuration.preview")
            </div>
        </div>
    </div>

    <style>
        /* Стили для вкладок */
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }
        .nav-tabs .nav-link {
            cursor: pointer;
        }


        /* Стили для форм */
        .form-fields-list {
            max-height: 300px;
            overflow-y: auto;
        }

        /* Анимация сохранения */
        .btn-alt-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>

    @include("widgets.configuration.js")


@endsection

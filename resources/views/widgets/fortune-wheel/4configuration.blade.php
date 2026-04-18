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
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="design-tab" href="#" @click.prevent="switchTab('design-tab')">Дизайн</a>
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
                                                <label class="small">X (отступ справа/слева)</label>
                                                <input type="number" class="form-control" x-model="settings.button.custom_position.x">
                                            </div>
                                            <div class="col-6">
                                                <label class="small">Y (отступ сверху/снизу)</label>
                                                <input type="number" class="form-control" x-model="settings.button.custom_position.y">
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
                                        <input type="number" class="form-control" min="0" max="30" step="0.5" x-model="settings.button.auto_open_delay">
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
                                            <input type="range" class="form-range flex-grow-1" min="2" max="10" step="0.5" x-model="settings.wheel.rotation_speed">
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
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle me-1"></i>
                                            Вероятность выпадения рассчитывается автоматически на основе веса каждого приза.
                                        </div>

                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Призы и купоны</span>
                                            <button type="button" class="btn btn-sm btn-primary" @click="addCoupon">
                                                <i class="fa fa-plus me-1"></i> Добавить приз
                                            </button>
                                        </label>

                                        <div class="coupons-list" style="max-height: 500px; overflow-y: auto;">
                                            <template x-for="(coupon, index) in settings.coupons" :key="coupon.id">
                                                <div class="card mb-2 border">
                                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" x-model="coupon.enabled" :id="'coupon_enabled_' + index" style="cursor: pointer;">
                                                                <label class="form-check-label small fw-bold" :for="'coupon_enabled_' + index" x-text="coupon.name || 'Новый приз'" style="cursor: pointer;"></label>
                                                            </div>
                                                            <span class="badge" :style="'background-color: ' + coupon.color">Вес: <span x-text="coupon.probability"></span></span>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeCoupon(index)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body py-2" x-show="coupon.enabled">
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-4">
                                                                <label class="small text-muted">Название приза</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.name" placeholder="Название приза">
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="small text-muted">Вес (вероятность)</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" class="form-control" x-model="coupon.probability" min="0" max="100" step="1">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="small text-muted">Цвет сегмента</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="color" class="form-control form-control-color" style="width: 40px; padding: 2px;" x-model="coupon.color">
                                                                    <input type="text" class="form-control" x-model="coupon.color" maxlength="7">
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-2">
                                                                <label class="small text-muted">&nbsp;</label>
                                                                <div class="form-check mt-1">
                                                                    <input class="form-check-input" type="checkbox" x-model="coupon.generate_unique" :id="'gen_unique_' + index">
                                                                    <label class="form-check-label small" :for="'gen_unique_' + index">Уник. код</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row g-2 mt-2">
                                                            <div class="col-12 col-md-6">
                                                                <label class="small text-muted">Описание</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.description" placeholder="Описание приза">
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="small text-muted">Промокод</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.code" :disabled="coupon.generate_unique" placeholder="Код">
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="small text-muted">Срок действия</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number" class="form-control" x-model="coupon.expiry_days" min="0">
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
                                            <textarea class="form-control" rows="2" x-model="settings.form.success_message" placeholder="Ваш купон: {CODE}"></textarea>
                                            <small class="text-muted">Используйте {CODE} для подстановки кода купона, {NAME} для имени пользователя</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Webhook URL для отправки данных</label>
                                            <input type="url" class="form-control" x-model="settings.form.webhook_url" placeholder="https://your-site.com/webhook">
                                            <small class="text-muted">Данные формы будут отправлены на этот URL методом POST</small>
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
                                                            <div class="col-12 col-md-3">
                                                                <select class="form-select form-select-sm" x-model="field.type">
                                                                    <option value="text">Текстовое поле</option>
                                                                    <option value="email">Email</option>
                                                                    <option value="tel">Телефон</option>
                                                                    <option value="textarea">Многострочный текст</option>
                                                                    <option value="hidden">Скрытое поле</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12 col-md-3">
                                                                <input type="text" class="form-control form-control-sm" placeholder="Название поля" x-model="field.label">
                                                            </div>
                                                            <div class="col-12 col-md-3">
                                                                <input type="text" class="form-control form-control-sm" placeholder="Placeholder" x-model="field.placeholder">
                                                            </div>
                                                            <div class="col-12 col-md-2">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" x-model="field.required" :id="'req_' + idx">
                                                                    <label class="form-check-label small" :for="'req_' + idx">Обязательное</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-1 text-end">
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
                                </div>

                                <!-- ==================== ВКЛАДКА ДИЗАЙН ==================== -->
                                <div class="tab-pane" id="design-tab" data-pane="design-tab" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Цвет фона модального окна</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.modal_bg">
                                            <input type="text" class="form-control" x-model="settings.design.modal_bg">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Акцентный цвет</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.accent_color">
                                            <input type="text" class="form-control" x-model="settings.design.accent_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Заголовок</label>
                                        <input type="text" class="form-control" x-model="settings.design.title" placeholder="Выиграйте приз!">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Описание</label>
                                        <textarea class="form-control" rows="2" x-model="settings.design.description" placeholder="Крутите колесо и получите скидку до 50%"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Шаблон (скин)</label>
                                        <select class="form-select" x-model="settings.template">
                                            <template x-for="skin in skins" :key="skin.slug">
                                                <option :value="skin.slug" x-text="skin.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-alt-primary w-100 mt-3">
                                <i class="fa fa-save me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ПРЕДПРОСМОТР -->
            <div class="col-md-7">
                @include("widgets.configuration.preview")
            </div>
        </div>
    </div>

    <style>
        .tab-pane {
            display: none;
        }
        .tab-pane.active {
            display: block;
        }
        .nav-tabs .nav-link {
            cursor: pointer;
        }
        .form-fields-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .coupons-list {
            max-height: 500px;
            overflow-y: auto;
        }
        .btn-alt-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>

    @push('js')
        <script>
            function fortuneWheelEditor(config) {
                return {
                    slug: config.slug,
                    settings: config.settings,
                    skins: config.skins,
                    activeTab: 'button-tab',
                    isUpdating: false,
                    isInitialized: false,
                    previewMode: 'desktop',
                    currentRotation: 0,
                    isSpinning: false,

                    // Для предпросмотра
                    rawTemplate: '',
                    rawCss: '',
                    shadowRoot: null,
                    widgetRoot: null,

                    async init() {
                        if (this.isInitialized) return;
                        this.isInitialized = true;

                        this.initDefaults();
                        this.initTabs();

                        // Загружаем скин
                        await this.loadSkin(this.settings.template || 'default');

                        // Наблюдение за изменениями настроек
                        this.$watch('settings', () => {
                            if (!this.isUpdating) {
                                this.updateWheelSegments();
                                this.updatePreview();
                            }
                        }, { deep: true });

                        // Отдельно следим за купонами для пересчета сегментов
                        this.$watch('settings.coupons', () => {
                            if (!this.isUpdating) {
                                this.updateWheelSegments();
                                this.updatePreview();
                            }
                        }, { deep: true });
                    },

                    initDefaults() {
                        // Кнопка
                        this.settings.button = this.settings.button || {};
                        this.settings.button.position = this.settings.button.position || 'bottom-right';
                        this.settings.button.custom_position = this.settings.button.custom_position || { x: 20, y: 20 };
                        this.settings.button.text = this.settings.button.text || 'Крутить колесо';
                        this.settings.button.icon = this.settings.button.icon || '🎡';
                        this.settings.button.size = this.settings.button.size || 'medium';
                        this.settings.button.bg_color = this.settings.button.bg_color || '#6366f1';
                        this.settings.button.text_color = this.settings.button.text_color || '#FFFFFF';
                        this.settings.button.border_radius = this.settings.button.border_radius || '50px';
                        this.settings.button.show_on_load = this.settings.button.show_on_load !== false;
                        this.settings.button.auto_open_delay = this.settings.button.auto_open_delay || 0;

                        // Колесо
                        this.settings.wheel = this.settings.wheel || {};
                        this.settings.wheel.size = this.settings.wheel.size || 300;
                        this.settings.wheel.rotation_speed = this.settings.wheel.rotation_speed || 4;
                        this.settings.wheel.background_color = this.settings.wheel.background_color || '#FFFFFF';
                        this.settings.wheel.text_color = this.settings.wheel.text_color || '#333333';
                        this.settings.wheel.border_color = this.settings.wheel.border_color || '#FFD700';
                        this.settings.wheel.border_width = this.settings.wheel.border_width || 2;
                        this.settings.wheel.pointer_color = this.settings.wheel.pointer_color || '#FF4444';
                        this.settings.wheel.font_size = this.settings.wheel.font_size || 13;
                        this.settings.wheel.segments = this.settings.wheel.segments || [];

                        // Дизайн модального окна
                        this.settings.design = this.settings.design || {};
                        this.settings.design.modal_bg = this.settings.design.modal_bg || '#FFFFFF';
                        this.settings.design.accent_color = this.settings.design.accent_color || '#6366f1';
                        this.settings.design.title = this.settings.design.title || 'Выиграйте приз!';
                        this.settings.design.description = this.settings.design.description || 'Крутите колесо и получите скидку до 50%';

                        // Купоны
                        this.settings.coupons = this.settings.coupons || [];
                        if (this.settings.coupons.length === 0) {
                            this.settings.coupons = [
                                { id: '1', enabled: true, name: 'Скидка 10%', probability: 30, code: 'WELCOME10', generate_unique: false, expiry_days: 30, color: '#FF6B6B', description: 'Скидка 10% на первый заказ' },
                                { id: '2', enabled: true, name: 'Скидка 20%', probability: 20, code: 'SAVE20', generate_unique: false, expiry_days: 14, color: '#4ECDC4', description: 'Скидка 20% на весь ассортимент' },
                                { id: '3', enabled: true, name: 'Попробуй еще раз', probability: 50, code: '', generate_unique: false, expiry_days: 0, color: '#95A5A6', description: 'К сожалению, вы ничего не выиграли' }
                            ];
                        }

                        // Форма
                        this.settings.form = this.settings.form || {};
                        this.settings.form.enabled = this.settings.form.enabled !== false;
                        this.settings.form.title = this.settings.form.title || 'Поздравляем!';
                        this.settings.form.subtitle = this.settings.form.subtitle || 'Введите ваши данные, чтобы получить приз';
                        this.settings.form.button_text = this.settings.form.button_text || 'Получить приз';
                        this.settings.form.success_message = this.settings.form.success_message || 'Ваш купон: {CODE}';
                        this.settings.form.webhook_url = this.settings.form.webhook_url || '';
                        this.settings.form.fields = this.settings.form.fields || [
                            { type: 'text', name: 'name', label: 'Ваше имя', required: true, placeholder: 'Иван Иванов' },
                            { type: 'email', name: 'email', label: 'Email', required: true, placeholder: 'ivan@example.com' }
                        ];

                        // Лимиты
                        this.settings.limits = this.settings.limits || {};
                        this.settings.limits.spins_per_user = this.settings.limits.spins_per_user || 1;
                        this.settings.limits.spins_per_day = this.settings.limits.spins_per_day || 1;
                        this.settings.limits.require_auth = this.settings.limits.require_auth || false;

                        // Поведение
                        this.settings.trigger_type = this.settings.trigger_type || 'click';
                        this.settings.delay = this.settings.delay || 0;
                        this.settings.scroll_percent = this.settings.scroll_percent || 50;
                        this.settings.frequency = this.settings.frequency || 'once_session';
                        this.settings.close_behavior = this.settings.close_behavior || 'hide_session';

                        // Шаблон
                        this.settings.template = this.settings.template || 'default';

                        // Обновляем сегменты колеса
                        this.updateWheelSegments();
                    },

                    initTabs() {
                        const tabs = document.querySelectorAll('[data-tab]');
                        tabs.forEach(tab => {
                            tab.addEventListener('click', (e) => {
                                e.preventDefault();
                                const tabId = tab.getAttribute('data-tab');
                                this.switchTab(tabId);
                            });
                        });
                    },

                    switchTab(tabId) {
                        this.activeTab = tabId;

                        document.querySelectorAll('.tab-pane').forEach(pane => {
                            pane.classList.remove('active');
                            pane.style.display = 'none';
                        });

                        const activePane = document.getElementById(tabId);
                        if (activePane) {
                            activePane.classList.add('active');
                            activePane.style.display = 'block';
                        }

                        document.querySelectorAll('[data-tab]').forEach(tab => {
                            tab.classList.remove('active');
                            if (tab.getAttribute('data-tab') === tabId) {
                                tab.classList.add('active');
                            }
                        });
                    },

                    updateWheelSegments() {
                        const coupons = (this.settings.coupons || []).filter(c => c.enabled !== false);
                        const totalProbability = coupons.reduce((sum, c) => sum + (parseInt(c.probability) || 0), 0);

                        if (totalProbability === 0 || coupons.length === 0) {
                            this.settings.wheel.segments = [];
                            return;
                        }

                        let currentAngle = 0;
                        const segments = coupons.map(coupon => {
                            const angle = (parseInt(coupon.probability) / totalProbability) * 360;
                            const segment = {
                                id: coupon.id,
                                name: coupon.name,
                                label: coupon.name,
                                probability: coupon.probability,
                                angle: angle,
                                start_angle: currentAngle,
                                end_angle: currentAngle + angle,
                                color: coupon.color || '#FF6B6B',
                                bg_color: coupon.color || '#FF6B6B',
                                code: coupon.code,
                                generate_unique: coupon.generate_unique,
                                expiry_days: coupon.expiry_days,
                                description: coupon.description
                            };
                            currentAngle += angle;
                            return segment;
                        });

                        this.settings.wheel.segments = segments;
                    },

                    async loadSkin(skinId) {
                        try {
                            const baseUrl = `/widgets/${this.slug}/skins/${skinId}`;
                            const [htmlRes, cssRes] = await Promise.all([
                                fetch(`${baseUrl}/template.html?v=${Date.now()}`),
                                fetch(`${baseUrl}/style.css?v=${Date.now()}`)
                            ]);
                            this.rawTemplate = await htmlRes.text();
                            this.rawCss = await cssRes.text();
                            this.initPreview();
                            this.updatePreview();
                        } catch (e) {
                            console.error('Error loading skin:', e);
                        }
                    },

                    initPreview() {
                        const container = document.getElementById('preview-host');
                        if (!container) return;

                        if (this.shadowRoot) {
                            this.shadowRoot.innerHTML = '';
                        }

                        this.shadowRoot = container.shadowRoot || container.attachShadow({ mode: 'open' });

                        let css = this.rawCss || '';
                        css = css.replace(/position:\s*fixed/g, 'position: absolute');
                        css = css.replace(/position:fixed/g, 'position: absolute');
                        css = css.replace(/100vh/g, '100%');
                        css = css.replace(/100vw/g, '100%');

                        // Добавляем стили для предпросмотра
                        const previewStyles = `
                        .sfw-root {
                            position: absolute;
                            bottom: 20px;
                            right: 20px;
                            z-index: 1000;
                        }
                        .sfw-overlay {
                            position: fixed;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background: rgba(0,0,0,0.5);
                            display: none;
                            justify-content: center;
                            align-items: center;
                            z-index: 1001;
                        }
                        .sfw-overlay.active, .sfw-overlay.sp-active {
                            display: flex;
                        }
                        .sfw-modal-content {
                            background: var(--sfw-modal-bg, #fff);
                            border-radius: 16px;
                            padding: 24px;
                            max-width: 800px;
                            width: 90%;
                            position: relative;
                        }
                        .sfw-close-btn {
                            position: absolute;
                            top: 12px;
                            right: 16px;
                            background: none;
                            border: none;
                            font-size: 24px;
                            cursor: pointer;
                        }
                        .sfw-grid {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 24px;
                        }
                        .sfw-wheel-container {
                            position: relative;
                            display: flex;
                            justify-content: center;
                        }
                        .sfw-pointer {
                            position: absolute;
                            top: -15px;
                            left: 50%;
                            transform: translateX(-50%);
                            width: 0;
                            height: 0;
                            border-left: 15px solid transparent;
                            border-right: 15px solid transparent;
                            border-top: 30px solid var(--sfw-pointer, #FF4444);
                            z-index: 10;
                        }
                        canvas {
                            max-width: 100%;
                            height: auto;
                            transition: transform cubic-bezier(0.15, 0, 0.15, 1);
                            border-radius: 50%;
                            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                        }
                        .sfw-spin-trigger, .sfw-submit-btn {
                            background: var(--sfw-accent, #6366f1);
                            color: white;
                            border: none;
                            padding: 12px 24px;
                            border-radius: 50px;
                            cursor: pointer;
                            font-size: 16px;
                            font-weight: 600;
                            margin-top: 16px;
                            width: 100%;
                        }
                        .sfw-input {
                            width: 100%;
                            padding: 10px 12px;
                            margin: 8px 0;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                        }
                        .sfw-trigger {
                            width: 60px;
                            height: 60px;
                            border-radius: 50%;
                            background: var(--sfw-btn-bg, #6366f1);
                            color: var(--sfw-btn-text, white);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            cursor: pointer;
                            font-size: 28px;
                            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                        }
                        @media (max-width: 768px) {
                            .sfw-grid {
                                grid-template-columns: 1fr;
                            }
                        }
                    `;

                        this.shadowRoot.innerHTML = `
                        <style>
                            :host {
                                display: block;
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                            }
                            ${previewStyles}
                            ${css}
                        </style>
                        <div id="widget-root"></div>
                    `;
                        this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                    },

                    updatePreview() {
                        if (this.isUpdating) return;
                        if (!this.widgetRoot || !this.rawTemplate) return;

                        this.isUpdating = true;

                        // Просто обновляем содержимое, Alpine сам разберется с производительностью
                        let html = this.rawTemplate
                            .replace(/\{id\}/g, 'preview')
                            .replace(/\{position\}/g, this.settings.button?.position || 'bottom-right')
                            .replace(/\{title\}/g, this.escapeHtml(this.settings.design?.title || 'Выиграйте приз!'))
                            .replace(/\{description\}/g, this.escapeHtml(this.settings.design?.description || 'Крутите колесо и получите скидку до 50%'))
                            .replace(/\{button_icon\}/g, this.settings.button?.icon || '🎡')
                            .replace(/\{btn_text\}/g, this.escapeHtml(this.settings.button?.text || 'Крутить колесо'));

                        this.widgetRoot.innerHTML = html;

                        const widget = this.widgetRoot.firstElementChild;
                        if (!widget) return;

                        // Применяем CSS переменные (быстрая операция)
                        const btn = this.settings.button || {};
                        const wheel = this.settings.wheel || {};
                        const design = this.settings.design || {};

                        widget.style.setProperty('--sfw-btn-bg', btn.bg_color || '#6366f1');
                        widget.style.setProperty('--sfw-btn-text', btn.text_color || '#ffffff');
                        widget.style.setProperty('--sfw-accent', design.accent_color || '#6366f1');
                        widget.style.setProperty('--sfw-modal-bg', design.modal_bg || '#ffffff');
                        widget.style.setProperty('--sfw-pointer', wheel.pointer_color || '#FF4444');

                        // Настройка позиции кнопки
                        const trigger = widget.querySelector('.sfw-trigger');
                        if (trigger) {
                            trigger.style.position = 'fixed';
                            const pos = btn.position || 'bottom-right';
                            if (pos === 'bottom-right') {
                                trigger.style.bottom = '20px';
                                trigger.style.right = '20px';
                            } else if (pos === 'bottom-left') {
                                trigger.style.bottom = '20px';
                                trigger.style.left = '20px';
                            } else if (pos === 'top-right') {
                                trigger.style.top = '20px';
                                trigger.style.right = '20px';
                            } else if (pos === 'top-left') {
                                trigger.style.top = '20px';
                                trigger.style.left = '20px';
                            } else if (pos === 'custom') {
                                trigger.style.bottom = `${btn.custom_position?.y || 20}px`;
                                trigger.style.right = `${btn.custom_position?.x || 20}px`;
                            }
                        }

                        // Перерисовываем canvas (это единственная "тяжелая" операция)
                        this.drawWheelOnCanvas(widget);

                        // Привязываем события (один раз!)
                        if (!widget._eventsAttached) {
                            this.attachPreviewEvents(widget);
                            widget._eventsAttached = true;
                        }

                        // Авто-открытие только при первом рендере
                        if (!this._initialAutoOpen && this.settings.coupons?.length > 0) {
                            this._initialAutoOpen = true;
                            setTimeout(() => {
                                const modal = widget.querySelector('.sfw-overlay');
                                if (modal && !widget.classList.contains('sp-active')) {
                                    modal.style.display = 'flex';
                                    widget.classList.add('sp-active');
                                }
                            }, 500);
                        }

                        this.isUpdating = false;
                    },

                    drawWheelOnCanvas(widget) {
                        const canvas = widget.querySelector('canvas');
                        if (!canvas) return;

                        // Отменяем предыдущий запрос на отрисовку
                        if (this._drawRequest) {
                            cancelAnimationFrame(this._drawRequest);
                        }

                        // Откладываем отрисовку до следующего кадра
                        this._drawRequest = requestAnimationFrame(() => {
                            const segments = this.settings.wheel?.segments || [];
                            const size = this.settings.wheel?.size || 300;
                            const textColor = this.settings.wheel?.text_color || '#333333';
                            const borderColor = this.settings.wheel?.border_color || '#FFD700';
                            const borderWidth = this.settings.wheel?.border_width || 2;
                            const fontSize = this.settings.wheel?.font_size || 13;

                            canvas.width = size;
                            canvas.height = size;

                            const ctx = canvas.getContext('2d');
                            if (!ctx) return;

                            const centerX = size / 2;
                            const centerY = size / 2;
                            const radius = size / 2 - 10;

                            ctx.clearRect(0, 0, size, size);

                            if (segments.length === 0) {
                                ctx.font = '14px Arial';
                                ctx.fillStyle = '#999';
                                ctx.textAlign = 'center';
                                ctx.fillText('Добавьте призы', centerX, centerY);
                                ctx.font = '12px Arial';
                                ctx.fillText('в разделе "Купоны"', centerX, centerY + 25);
                                return;
                            }

                            let startAngle = 0;

                            segments.forEach((segment) => {
                                const angle = (segment.angle || 0) * Math.PI / 180;
                                const endAngle = startAngle + angle;

                                ctx.beginPath();
                                ctx.moveTo(centerX, centerY);
                                ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                                ctx.closePath();

                                ctx.fillStyle = segment.color || '#FF6B6B';
                                ctx.fill();

                                ctx.strokeStyle = borderColor;
                                ctx.lineWidth = borderWidth;
                                ctx.stroke();

                                const textAngle = startAngle + angle / 2;
                                const textRadius = radius * 0.65;
                                const textX = centerX + textRadius * Math.cos(textAngle);
                                const textY = centerY + textRadius * Math.sin(textAngle);

                                let text = segment.name || '';
                                if (text.length > 12) text = text.substr(0, 10) + '..';

                                ctx.save();
                                ctx.translate(textX, textY);
                                ctx.rotate(textAngle + Math.PI / 2);
                                ctx.fillStyle = textColor;
                                ctx.font = `bold ${Math.max(10, Math.min(fontSize, (segment.angle || 20) / 12))}px Arial`;
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.fillText(text, 0, 0);
                                ctx.restore();

                                startAngle = endAngle;
                            });

                            ctx.beginPath();
                            ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
                            ctx.fillStyle = this.settings.wheel?.background_color || '#FFFFFF';
                            ctx.fill();
                            ctx.strokeStyle = borderColor;
                            ctx.stroke();

                            ctx.beginPath();
                            ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
                            ctx.fillStyle = this.settings.wheel?.pointer_color || '#FF4444';
                            ctx.fill();

                            this._drawRequest = null;
                        });
                    },

                    attachPreviewEvents(widget) {
                        const canvas = widget.querySelector('canvas');
                        const modal = widget.querySelector('.sfw-overlay');
                        const trigger = widget.querySelector('.sfw-trigger');
                        const closeBtn = widget.querySelector('.sfw-close-btn');
                        const spinBtn = widget.querySelector('.sfw-spin-trigger');

                        // Открытие модалки
                        if (trigger && !trigger._hasEvents) {
                            trigger._hasEvents = true;
                            trigger.addEventListener('click', (e) => {
                                e.preventDefault();
                                if (modal) {
                                    modal.style.display = 'flex';
                                    widget.classList.add('sp-active');
                                }
                            });
                        }

                        // Закрытие модалки
                        if (closeBtn && !closeBtn._hasEvents) {
                            closeBtn._hasEvents = true;
                            closeBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                if (modal) {
                                    modal.style.display = 'none';
                                    widget.classList.remove('sp-active');
                                }
                            });
                        }

                        // Клик по фону
                        if (modal && !modal._hasEvents) {
                            modal._hasEvents = true;
                            modal.addEventListener('click', (e) => {
                                if (e.target === modal) {
                                    modal.style.display = 'none';
                                    widget.classList.remove('sp-active');
                                }
                            });
                        }

                        // Вращение колеса
                        if (spinBtn && !spinBtn._hasEvents) {
                            spinBtn._hasEvents = true;
                            spinBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                this.previewSpin(canvas);
                            });
                        }

                        // Также вешаем на canvas если spinBtn нет
                        if (canvas && !canvas._hasEvents && !spinBtn) {
                            canvas._hasEvents = true;
                            canvas.style.cursor = 'pointer';
                            canvas.addEventListener('click', () => {
                                this.previewSpin(canvas);
                            });
                        }
                    },

                    previewSpin(canvas) {
                        if (!canvas || this.isSpinning) return;

                        const segments = this.settings.wheel?.segments || [];
                        if (segments.length === 0) {
                            alert('Добавьте призы в разделе "Купоны"');
                            return;
                        }

                        this.isSpinning = true;

                        // Выбираем случайный сегмент с учетом вероятности
                        const totalProbability = segments.reduce((sum, s) => sum + (s.probability || 0), 0);
                        let random = Math.random() * totalProbability;
                        let selectedIndex = 0;
                        let accumulated = 0;

                        for (let i = 0; i < segments.length; i++) {
                            accumulated += segments[i].probability || 0;
                            if (random <= accumulated) {
                                selectedIndex = i;
                                break;
                            }
                        }

                        const segmentAngle = 360 / segments.length;
                        const targetAngle = (360 - (selectedIndex * segmentAngle) - (segmentAngle / 2)) % 360;

                        // 5 полных оборотов + доворот до цели
                        const totalRotation = 1800 + targetAngle;
                        this.currentRotation += totalRotation;

                        canvas.style.transition = `transform ${this.settings.wheel?.rotation_speed || 4}s cubic-bezier(0.15, 0, 0.15, 1)`;
                        canvas.style.transform = `rotate(${this.currentRotation}deg)`;

                        setTimeout(() => {
                            this.isSpinning = false;
                            const wonSegment = segments[selectedIndex];

                            // Показываем результат
                            const formContainer = widget.querySelector('#sfw-form-fields-preview');
                            if (formContainer) {
                                this.showPreviewResult(formContainer, wonSegment);
                            } else {
                                alert(`Вы выиграли: ${wonSegment.name}!`);
                            }
                        }, (this.settings.wheel?.rotation_speed || 4) * 1000);
                    },

                    showPreviewResult(container, wonSegment) {
                        if (this.settings.form?.enabled) {
                            let html = `
                            <h4>${this.escapeHtml(this.settings.form.title || 'Поздравляем!')}</h4>
                            <p>Ваш приз: <strong>${this.escapeHtml(wonSegment.name)}</strong></p>
                            <p>${this.escapeHtml(wonSegment.description || '')}</p>
                        `;

                            const fields = this.settings.form?.fields || [];
                            fields.forEach(field => {
                                if (field.type === 'textarea') {
                                    html += `<textarea class="sfw-input" placeholder="${this.escapeHtml(field.placeholder || field.label || '')}" ${field.required ? 'required' : ''}></textarea>`;
                                } else if (field.type !== 'hidden') {
                                    html += `<input type="${field.type}" class="sfw-input" placeholder="${this.escapeHtml(field.placeholder || field.label || '')}" ${field.required ? 'required' : ''}>`;
                                }
                            });

                            html += `<button class="sfw-submit-btn" id="preview-submit-btn">${this.escapeHtml(this.settings.form.button_text || 'Получить приз')}</button>`;
                            container.innerHTML = html;

                            const submitBtn = container.querySelector('#preview-submit-btn');
                            if (submitBtn) {
                                submitBtn.addEventListener('click', () => {
                                    let code = wonSegment.code;
                                    if (wonSegment.generate_unique) {
                                        code = Math.random().toString(36).substring(2, 10).toUpperCase();
                                    }
                                    let message = this.settings.form.success_message || 'Ваш купон: {CODE}';
                                    message = message.replace('{CODE}', code).replace('{NAME}', 'Тестовый пользователь');
                                    container.innerHTML = `<div class="sfw-success-final">${this.escapeHtml(message)}</div>`;

                                    setTimeout(() => {
                                        const modal = document.querySelector('#preview-host shadow-root .sfw-overlay');
                                        if (modal) modal.style.display = 'none';
                                    }, 2000);
                                });
                            }
                        } else {
                            container.innerHTML = `<div class="sfw-win-msg">Вы выиграли: ${this.escapeHtml(wonSegment.name)}!</div>`;
                        }
                    },

                    addCoupon() {
                        if (!this.settings.coupons) this.settings.coupons = [];
                        this.settings.coupons.push({
                            id: 'coupon_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                            enabled: true,
                            name: 'Новый приз',
                            description: '',
                            probability: 10,
                            code: '',
                            generate_unique: false,
                            expiry_days: 7,
                            color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')
                        });
                        this.updateWheelSegments();
                        this.updatePreview();
                    },

                    removeCoupon(index) {
                        if (confirm('Удалить этот приз?')) {
                            this.settings.coupons.splice(index, 1);
                            this.updateWheelSegments();
                            this.updatePreview();
                        }
                    },

                    addFormField() {
                        if (!this.settings.form.fields) this.settings.form.fields = [];
                        this.settings.form.fields.push({
                            id: 'field_' + Date.now(),
                            type: 'text',
                            name: 'field_' + Date.now(),
                            label: 'Новое поле',
                            placeholder: 'Введите значение',
                            required: false
                        });
                        this.updatePreview();
                    },

                    removeFormField(index) {
                        this.settings.form.fields.splice(index, 1);
                        this.updatePreview();
                    },

                    escapeHtml(text) {
                        if (!text) return '';
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    },

                    async saveConfig(event) {
                        const btn = event?.target?.closest('button[type="submit"]') || event?.currentTarget;
                        if (!btn) return;

                        const originalText = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                        try {
                            const response = await axios.post(window.location.href, { settings: this.settings });
                            if (response.data.status === 'success') {
                                if (typeof showNotification === 'function') {
                                    showNotification(response.data.message, 'success');
                                } else {
                                    alert('Настройки сохранены');
                                }
                            }
                        } catch (error) {
                            const msg = error.response?.data?.message || 'Ошибка при сохранении';
                            if (typeof showNotification === 'function') {
                                showNotification(msg, 'danger');
                            } else {
                                alert(msg);
                            }
                        } finally {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection

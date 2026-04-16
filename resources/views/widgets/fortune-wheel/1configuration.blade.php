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
                        <form id="saveForm">
                        @csrf

                        <!-- Вкладки -->
                            <ul class="nav nav-tabs nav-tabs-alt mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#button-tab">Кнопка</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#wheel-tab">Колесо</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#coupons-tab">Купоны</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#form-tab">Форма</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#limits-tab">Лимиты</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Настройка кнопки -->
                                <div class="tab-pane active" id="button-tab">
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
                                        <label class="form-label">Размер</label>
                                        <select class="form-select" x-model="settings.button.size">
                                            <option value="small">Маленькая</option>
                                            <option value="medium">Средняя</option>
                                            <option value="large">Большая</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет фона</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.button.bg_color">
                                            <input type="text" class="form-control" x-model="settings.button.bg_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет текста</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.button.text_color">
                                            <input type="text" class="form-control" x-model="settings.button.text_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" x-model="settings.button.show_on_load">
                                            <label class="form-check-label">Показывать кнопку при загрузке</label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Авто-открытие (сек)</label>
                                        <input type="number" class="form-control" min="0" max="30" x-model="settings.button.auto_open_delay">
                                        <small class="text-muted">0 - не открывать автоматически</small>
                                    </div>
                                </div>

                                <!-- Настройка колеса -->
                                <div class="tab-pane" id="wheel-tab">
                                    <div class="mb-3">
                                        <label class="form-label">Размер колеса (px)</label>
                                        <input type="number" class="form-control" min="200" max="600" step="20" x-model="settings.wheel.size">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Скорость вращения (сек)</label>
                                        <input type="range" class="form-range" min="3" max="15" step="0.5" x-model="settings.wheel.rotation_speed">
                                        <span x-text="settings.wheel.rotation_speed + ' сек'"></span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет фона колеса</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" x-model="settings.wheel.background_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.background_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет текста</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" x-model="settings.wheel.text_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.text_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет стрелки</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" x-model="settings.wheel.pointer_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.pointer_color">
                                        </div>
                                    </div>
                                </div>

                                <!-- Купоны -->
                                <div class="tab-pane" id="coupons-tab">
                                    <div class="mb-3">
                                        <label class="form-label d-flex justify-content-between">
                                            Призы и купоны
                                            <button type="button" class="btn btn-sm btn-primary" @click="addCoupon">
                                                <i class="fa fa-plus"></i> Добавить приз
                                            </button>
                                        </label>

                                        <div class="coupons-list">
                                            <template x-for="(coupon, index) in settings.coupons" :key="index">
                                                <div class="card mb-2">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <strong x-text="coupon.name || 'Новый приз'"></strong>
                                                        <button type="button" class="btn btn-sm btn-danger" @click="removeCoupon(index)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-8">
                                                                <label class="small">Название</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.name">
                                                            </div>
                                                            <div class="col-4">
                                                                <label class="small">Вес (% вероятности)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="coupon.probability">
                                                            </div>
                                                        </div>

                                                        <div class="mt-2">
                                                            <label class="small">Описание</label>
                                                            <textarea class="form-control form-control-sm" rows="2" x-model="coupon.description"></textarea>
                                                        </div>

                                                        <div class="row mt-2">
                                                            <div class="col-6">
                                                                <label class="small">Тип</label>
                                                                <select class="form-select form-select-sm" x-model="coupon.type">
                                                                    <option value="percentage">Скидка %</option>
                                                                    <option value="fixed">Фиксированная скидка</option>
                                                                    <option value="free_shipping">Бесплатная доставка</option>
                                                                    <option value="product">Товар в подарок</option>
                                                                    <option value="no_prize">Нет приза</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-6" x-show="coupon.type !== 'no_prize'">
                                                                <label class="small">Значение</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="coupon.value" :placeholder="coupon.type === 'percentage' ? 'Например: 10' : 'Сумма в рублях'">
                                                            </div>
                                                        </div>

                                                        <div class="row mt-2">
                                                            <div class="col-6">
                                                                <label class="small">Статичный код</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.code" :disabled="coupon.generate_unique">
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-check mt-4">
                                                                    <input class="form-check-input" type="checkbox" x-model="coupon.generate_unique">
                                                                    <label class="form-check-label small">Генерировать уникальный код</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row mt-2">
                                                            <div class="col-6">
                                                                <label class="small">Срок годности</label>
                                                                <input type="datetime-local" class="form-control form-control-sm" x-model="coupon.expires_at">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small">Лимит использований</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="coupon.usage_limit" placeholder="0 - без лимита">
                                                            </div>
                                                        </div>

                                                        <div class="row mt-2">
                                                            <div class="col-6">
                                                                <label class="small">Цвет сегмента</label>
                                                                <input type="color" class="form-control form-control-sm" x-model="coupon.color">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="small">Иконка</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.icon" placeholder="🎁">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div x-show="settings.coupons.length === 0" class="text-center text-muted py-4">
                                            <i class="fa fa-gift fa-3x mb-2"></i>
                                            <p>Нет добавленных призов. Нажмите "Добавить приз"</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Поведение при клике на кнопку закрытия формы -->
                                <div class="mb-3">
                                    <label class="form-label">Частота показа</label>
                                    <select class="form-select" x-model="settings.frequency">
                                        <option value="always">Всегда показывать</option>
                                        <option value="once_session">Один раз за сессию</option>
                                        <option value="once_day">Один раз в день</option>
                                        <option value="once_week">Один раз в неделю</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Настройка формы -->
                            <div class="tab-pane" id="form-tab">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" x-model="settings.form.enabled">
                                        <label class="form-check-label">Показывать форму сбора данных</label>
                                    </div>
                                </div>

                                <div x-show="settings.form.enabled">
                                    <div class="mb-3">
                                        <label class="form-label">Заголовок формы</label>
                                        <input type="text" class="form-control" x-model="settings.form.title">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Подзаголовок</label>
                                        <input type="text" class="form-control" x-model="settings.form.subtitle">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Текст кнопки</label>
                                        <input type="text" class="form-control" x-model="settings.form.button_text">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label d-flex justify-content-between">
                                            Поля формы
                                            <button type="button" class="btn btn-sm btn-primary" @click="addFormField">
                                                <i class="fa fa-plus"></i> Добавить поле
                                            </button>
                                        </label>

                                        <div class="form-fields-list">
                                            <template x-for="(field, idx) in settings.form.fields" :key="idx">
                                                <div class="border rounded p-2 mb-2">
                                                    <div class="row g-2">
                                                        <div class="col-3">
                                                            <select class="form-select form-select-sm" x-model="field.type">
                                                                <option value="text">Текст</option>
                                                                <option value="email">Email</option>
                                                                <option value="tel">Телефон</option>
                                                                <option value="textarea">Текстовая область</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-4">
                                                            <input type="text" class="form-control form-control-sm" placeholder="Название поля" x-model="field.label">
                                                        </div>
                                                        <div class="col-3">
                                                            <input type="text" class="form-control form-control-sm" placeholder="Placeholder" x-model="field.placeholder">
                                                        </div>
                                                        <div class="col-1">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" x-model="field.required">
                                                                <label class="small">Req</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-1">
                                                            <button type="button" class="btn btn-sm btn-link text-danger" @click="removeFormField(idx)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Лимиты -->
                            <div class="tab-pane" id="limits-tab">
                                <div class="mb-3">
                                    <label class="form-label">Максимум попыток на пользователя</label>
                                    <input type="number" class="form-control" min="0" x-model="settings.limits.spins_per_user">
                                    <small class="text-muted">0 - без лимита</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Максимум попыток в день</label>
                                    <input type="number" class="form-control" min="0" x-model="settings.limits.spins_per_day">
                                    <small class="text-muted">0 - без лимита</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" x-model="settings.limits.require_auth">
                                        <label class="form-check-label">Только для авторизованных пользователей</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" x-model="settings.limits.collect_email">
                                        <label class="form-check-label">Обязательно собирать email</label>
                                    </div>
                                </div>
                            </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Триггер показа</label>
                        <select class="form-select" x-model="settings.trigger_type">
                            <option value="click">По клику на кнопку</option>
                            <option value="time">По таймеру</option>
                            <option value="scroll">При скролле</option>
                            <option value="exit">При уходе мыши</option>
                        </select>
                    </div>

                    <div class="mb-3" x-show="settings.trigger_type === 'time'">
                        <label class="form-label">Задержка (сек)</label>
                        <input type="number" class="form-control" x-model="settings.delay">
                    </div>

                    <div class="mb-3" x-show="settings.trigger_type === 'scroll'">
                        <label class="form-label">Процент скролла</label>
                        <input type="number" class="form-control" min="0" max="100" x-model="settings.scroll_percent">
                    </div>

                    <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig">
                        <i class="fa fa-save me-1"></i> Сохранить изменения
                    </button>
                    </form>
                </div>
            </div>
            <!-- ПРЕДПРОСМОТР -->
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }" x-init="$watch('previewMode', () => $dispatch('preview-mode-changed', previewMode))">
                <div class="block block-rounded sticky-top" style="top: 20px;">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Предпросмотр</h3>
                        <div class="block-options">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-alt-secondary"
                                        :class="previewMode === 'desktop' ? 'active' : ''"
                                        @click="previewMode = 'desktop'">
                                    <i class="fa fa-desktop me-1"></i> ПК
                                </button>
                                <button type="button" class="btn btn-alt-secondary"
                                        :class="previewMode === 'mobile' ? 'active' : ''"
                                        @click="previewMode = 'mobile'">
                                    <i class="fa fa-mobile-alt me-1"></i> Мобильный
                                </button>
                                <button type="button" class="btn btn-alt-secondary"
                                        :class="previewMode === 'tablet' ? 'active' : ''"
                                        @click="previewMode = 'tablet'">
                                    <i class="fa fa-tablet-alt me-1"></i> Планшет
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="block-content p-3 bg-body-dark">
                        <div class="browser-mockup" :class="previewMode" id="browser-mockup">
                            <div class="browser-header">
                                <div class="d-flex gap-1">
                                    <span class="dot red"></span>
                                    <span class="dot yellow"></span>
                                    <span class="dot green"></span>
                                </div>
                                <div class="address-bar">
                                    <i class="fa fa-lock me-1 text-success"></i> your-website.com
                                </div>
                                <div class="browser-controls">
                                    <span class="badge bg-secondary" x-text="previewMode === 'desktop' ? '1920px' : (previewMode === 'tablet' ? '768px' : '375px')"></span>
                                </div>
                            </div>
                            <div class="browser-viewport" id="browser-viewport">
                                <div id="preview-host"></div>
                                <div class="site-placeholder">
                                    <div class="hero-rect"></div>
                                    <div class="p-3">
                                        <div class="row g-3">
                                            <div class="col-4"><div class="line"></div></div>
                                            <div class="col-8"><div class="line w-75"></div></div>
                                            <div class="col-12"><div class="line"></div></div>
                                            <div class="col-12"><div class="line w-50"></div></div>
                                            <div class="col-6"><div class="line"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted" x-text="previewMode === 'desktop' ? '1920px × 500px' : (previewMode === 'tablet' ? '768px × 500px' : '375px × 500px')"></small>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .browser-mockup {
                    border: 1px solid #d1d1d1;
                    border-radius: 8px;
                    background: #fff;
                    overflow: hidden;
                    height: 500px;
                    display: flex;
                    flex-direction: column;
                    margin: 0 auto;
                    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                }

                /* Режимы ширины с плавным переходом */
                .browser-mockup.desktop {
                    width: 100%;
                    max-width: 100%;
                }
                .browser-mockup.tablet {
                    width: 768px;
                }
                .browser-mockup.mobile {
                    width: 375px;
                }

                .browser-header {
                    background: #f1f1f1;
                    padding: 8px 12px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    border-bottom: 1px solid #e1e1e1;
                    flex-shrink: 0;
                }

                .browser-header .dot {
                    height: 10px;
                    width: 10px;
                    border-radius: 50%;
                    margin-right: 6px;
                }
                .dot.red { background: #ff5f56; }
                .dot.yellow { background: #ffbd2e; }
                .dot.green { background: #27c93f; }

                .browser-header .address-bar {
                    background: #fff;
                    flex: 1;
                    max-width: 400px;
                    margin: 0 12px;
                    border-radius: 4px;
                    font-size: 11px;
                    padding: 3px 10px;
                    color: #666;
                    text-align: center;
                    border: 1px solid #e1e1e1;
                }

                .browser-controls {
                    min-width: 60px;
                    text-align: right;
                }

                .browser-viewport {
                    position: relative;
                    flex-grow: 1;
                    background: #fff;
                    overflow-y: auto;
                    overflow-x: hidden;
                }

                .site-placeholder {
                    padding: 0;
                    pointer-events: none;
                }
                .hero-rect {
                    height: 160px;
                    background: linear-gradient(135deg, #f0f2f5 0%, #e9ecef 100%);
                    margin-bottom: 10px;
                    width: 100%;
                }
                .line {
                    height: 12px;
                    background: #f0f2f5;
                    border-radius: 6px;
                    margin-bottom: 15px;
                    width: 100%;
                    background: linear-gradient(90deg, #f0f2f5 0%, #e9ecef 50%, #f0f2f5 100%);
                    background-size: 200% auto;
                    animation: shimmer 1.5s infinite;
                }

                @keyframes shimmer {
                    0% { background-position: -200% 0; }
                    100% { background-position: 200% 0; }
                }

                #preview-host {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 100;
                    pointer-events: none;
                }

                /* Адаптация для мобильных устройств */
                @media (max-width: 768px) {
                    .browser-mockup.tablet,
                    .browser-mockup.mobile {
                        width: calc(100% - 32px);
                    }
                }
            </style>

        </div>
    </div>

    @push('js')
        <script>
            function fortuneWheelEditor(config) {
                return {
                    slug: config.slug,
                    settings: config.settings,
                    skins: config.skins,
                    previewInstance: null,

                    init() {
                        // Инициализация настроек по умолчанию
                        // Инициализация настроек по умолчанию
                        if (!this.settings.button) {
                            this.settings.button = {};
                        }

                        // ИСПРАВЛЕНИЕ: добавить инициализацию custom_position
                        if (!this.settings.button.custom_position) {
                            this.settings.button.custom_position = { x: 20, y: 20 };
                        }


                        if (!this.settings.coupons || this.settings.coupons.length === 0) {
                            this.settings.coupons = [
                                {
                                    id: '1',
                                    name: 'Скидка 10%',
                                    probability: 30,
                                    type: 'percentage',
                                    value: 10,
                                    code: 'WELCOME10',
                                    generate_unique: false,
                                    color: '#FF6B6B',
                                    icon: '🎁'
                                },
                                {
                                    id: '2',
                                    name: 'Скидка 20%',
                                    probability: 20,
                                    type: 'percentage',
                                    value: 20,
                                    generate_unique: true,
                                    color: '#4ECDC4',
                                    icon: '🎉'
                                },
                                {
                                    id: '3',
                                    name: 'Попробуй еще раз',
                                    probability: 50,
                                    type: 'no_prize',
                                    value: 0,
                                    color: '#95A5A6',
                                    icon: '😢'
                                }
                            ];
                        }

                        if (!this.settings.button) {
                            this.settings.button = {
                                position: 'bottom-right',
                                text: '🎁 Крутить колесо',
                                bg_color: '#FF6B6B',
                                text_color: '#FFFFFF',
                                size: 'medium'
                            };
                        }

                        if (!this.settings.wheel) {
                            this.settings.wheel = {
                                size: 400,
                                rotation_speed: 8,
                                background_color: '#FFFFFF',
                                text_color: '#FFFFFF',
                                pointer_color: '#FF4444'
                            };
                        }

                        if (!this.settings.form) {
                            this.settings.form = {
                                enabled: true,
                                title: 'Поздравляем!',
                                subtitle: 'Введите ваши данные, чтобы получить приз',
                                fields: [
                                    { type: 'text', name: 'name', label: 'Ваше имя', required: true, placeholder: 'Иван Иванов' },
                                    { type: 'email', name: 'email', label: 'Email', required: true, placeholder: 'ivan@example.com' }
                                ],
                                button_text: 'Получить приз'
                            };
                        }

                        if (!this.settings.limits) {
                            this.settings.limits = {
                                spins_per_user: 1,
                                spins_per_day: 1,
                                require_auth: false,
                                collect_email: true
                            };
                        }

                        // Генерируем сегменты колеса
                        this.updateWheelSegments();

                        // Обновляем предпросмотр при изменении
                        // Разделить watch на конкретные поля
                        this.$watch('settings.coupons', () => {
                            this.updateWheelSegments();
                        }, { deep: true });

                        this.$watch('settings.wheel', () => {
                            this.updatePreview();
                        }, { deep: true });

// Убрать глубокий watch на весь settings
                    },

                    updateWheelSegments() {
                        const coupons = this.settings.coupons || [];
                        const totalProbability = coupons.reduce((sum, c) => sum + (parseInt(c.probability) || 0), 0);

                        if (totalProbability === 0) return;

                        let currentAngle = 0;
                        // НЕ изменяем напрямую settings, а возвращаем массив
                        const segments = coupons.map(coupon => {
                            const angle = (parseInt(coupon.probability) / totalProbability) * 360;
                            const segment = {
                                id: coupon.id,
                                name: coupon.name,
                                angle: angle,
                                start_angle: currentAngle,
                                end_angle: currentAngle + angle,
                                color: coupon.color,
                                icon: coupon.icon
                            };
                            currentAngle += angle;
                            return segment;
                        });

                        // Обновляем только если изменилось
                        if (JSON.stringify(this.settings.wheel.segments) !== JSON.stringify(segments)) {
                            this.settings.wheel.segments = segments;
                        }
                    },

                    addCoupon() {
                        if (!this.settings.coupons) this.settings.coupons = [];
                        this.settings.coupons.push({
                            id: 'coupon_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                            name: 'Новый приз',
                            probability: 10,
                            type: 'percentage',
                            value: 10,
                            code: '',
                            generate_unique: false,
                            expires_at: '',
                            usage_limit: 0,
                            used_count: 0,
                            color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0'),
                            icon: '🎁'
                        });
                        // Принудительно обновляем сегменты
                        this.updateWheelSegments();
                    },

                    removeCoupon(index) {
                        this.settings.coupons.splice(index, 1);
                    },

                    addFormField() {
                        if (!this.settings.form.fields) this.settings.form.fields = [];
                        this.settings.form.fields.push({
                            type: 'text',
                            name: 'field_' + Date.now(),
                            label: 'Новое поле',
                            placeholder: 'Введите значение',
                            required: false
                        });
                    },

                    removeFormField(index) {
                        this.settings.form.fields.splice(index, 1);
                    },

                    async updatePreview() {
                        // Базовая реализация без бесконечных циклов
                        const container = document.getElementById('preview-host');
                        if (!container) return;

                        // Простое обновление预览 без сложной логики
                        container.innerHTML = `
                            <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                                <div style="text-align: center; color: white;">
                                    <div style="font-size: 100px;">🎡</div>
                                    <p>Колесо фортуны</p>
                                    <small>Настройки сохранены</small>
                                </div>
                            </div>
                        `;
                    },

                    async saveConfig() {
                        const btn = event.currentTarget;
                        const original = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                        try {
                            const response = await axios.post(window.location.href, { settings: this.settings });
                            if (response.data.status === 'success') {
                                if (typeof showNotification === 'function') {
                                    showNotification(response.data.message, 'success');
                                } else {
                                    alert(response.data.message);
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
                            btn.innerHTML = original;
                        }
                    }
                };
            }

        </script>
    @endpush
@endsection

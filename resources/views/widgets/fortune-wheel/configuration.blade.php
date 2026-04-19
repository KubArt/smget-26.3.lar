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
                                    <a class="nav-link" data-tab="prizes-tab" href="#" @click.prevent="switchTab('prizes-tab')">Призы</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="display-tab" href="#" @click.prevent="switchTab('display-tab')">Показ</a>
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
                                        </select>
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
                                        <label class="form-label">Размер кнопки</label>
                                        <select class="form-select" x-model="settings.button.size">
                                            <option value="small">Маленькая</option>
                                            <option value="medium">Средняя</option>
                                            <option value="large">Большая</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Скругление кнопки</label>
                                        <input type="text" class="form-control" placeholder="50px, 12px" x-model="settings.button.border_radius">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Авто-открытие (сек)</label>
                                        <input type="number" class="form-control" min="0" max="30" step="0.5" x-model="settings.button.auto_open_delay">
                                        <small class="text-muted">0 - не открывать автоматически</small>
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА КОЛЕСО ==================== -->
                                <div class="tab-pane" id="wheel-tab" data-pane="wheel-tab" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Размер колеса (px)</label>
                                        <input type="number" class="form-control" min="200" max="500" step="10" x-model="settings.wheel.size">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Скорость вращения (сек)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="range" class="form-range flex-grow-1" min="2" max="8" step="0.5" x-model="settings.wheel.rotation_speed">
                                            <span class="badge bg-secondary" x-text="settings.wheel.rotation_speed + ' сек'"></span>
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
                                        <label class="form-label">Цвет стрелки</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.wheel.pointer_color">
                                            <input type="text" class="form-control" x-model="settings.wheel.pointer_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Размер шрифта (px)</label>
                                        <input type="number" class="form-control" min="10" max="20" x-model="settings.wheel.font_size">
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
                                </div>

                                <!-- ==================== ВКЛАДКА ПРИЗЫ ==================== -->
                                <div class="tab-pane" id="prizes-tab" data-pane="prizes-tab" style="display: none;">
                                    <div class="mb-3">
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle me-1"></i>
                                            Вероятность выпадения рассчитывается автоматически на основе веса каждого приза.
                                        </div>

                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Призы</span>
                                            <button type="button" class="btn btn-sm btn-primary" @click="addCoupon">
                                                <i class="fa fa-plus me-1"></i> Добавить приз
                                            </button>
                                        </label>

                                        <div class="coupons-list" style="max-height: 400px; overflow-y: auto;">
                                            <template x-for="(coupon, index) in settings.wheel.segments" :key="index">
                                                <div class="card mb-2 border">
                                                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" x-model="coupon.enabled" :id="'coupon_enabled_' + index">
                                                                <label class="form-check-label small fw-bold" :for="'coupon_enabled_' + index" x-text="coupon.label || 'Новый приз'"></label>
                                                            </div>
                                                            <span class="badge bg-secondary">Вес: <span x-text="coupon.weight || 1"></span></span>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeSegment(index)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="card-body py-2" x-show="coupon.enabled !== false">
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-4">
                                                                <label class="small text-muted">Название приза</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.label" placeholder="Скидка 10%">
                                                            </div>
                                                            <div class="col-6 col-md-2">
                                                                <label class="small text-muted">Вес</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="coupon.weight" min="1" step="1" value="1">
                                                            </div>
                                                            <div class="col-6 col-md-3">
                                                                <label class="small text-muted">Цвет сегмента</label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="color" class="form-control form-control-color" style="width: 40px;" x-model="coupon.bg_color">
                                                                    <input type="text" class="form-control" x-model="coupon.bg_color">
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-3">
                                                                <label class="small text-muted">Срок действия (дней)</label>
                                                                <input type="number" class="form-control form-control-sm" x-model="coupon.expiry_days" min="0" placeholder="0 - бессрочно">
                                                            </div>
                                                        </div>
                                                        <div class="row g-2 mt-2">
                                                            <div class="col-12 col-md-6">
                                                                <label class="small text-muted">Промокод</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.value" placeholder="PROMO2024">
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label class="small text-muted">Описание</label>
                                                                <input type="text" class="form-control form-control-sm" x-model="coupon.description" placeholder="Описание приза">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer py-1 bg-white" x-show="coupon.enabled === false">
                                                        <small class="text-muted">Приз отключен</small>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <div x-show="!settings.wheel.segments || settings.wheel.segments.length === 0" class="text-center text-muted py-4 border rounded">
                                            <i class="fa fa-gift fa-3x mb-2 opacity-25"></i>
                                            <p class="mb-0">Нет добавленных призов</p>
                                            <small>Нажмите "Добавить приз" чтобы создать первый приз</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА ПОКАЗ ==================== -->
                                <div class="tab-pane" id="display-tab" data-pane="display-tab" style="display: none;">
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

                                    <hr>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Что собирать у пользователя</label>
                                        <select class="form-select" x-model="settings.form.contact_type">
                                            <option value="tel">Телефон</option>
                                            <option value="email">Email</option>
                                        </select>
                                        <small class="text-muted">Пользователь должен указать контакт перед вращением</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Текст кнопки игры</label>
                                        <input type="text" class="form-control" x-model="settings.form.button_text" placeholder="Играть">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Сообщение при отказе от приза</label>
                                        <input type="text" class="form-control" x-model="settings.messages.reject_prize" placeholder="Вы отказались от приза. Жаль! Возвращайтесь еще!">
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Поведение при закрытии</label>
                                        <select class="form-select" x-model="settings.close_behavior">
                                            <option value="hide_session">Не показывать до конца сессии</option>
                                            <option value="hide_forever">Больше никогда не показывать</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Webhook URL для отправки данных</label>
                                        <input type="url" class="form-control" x-model="settings.form.webhook_url" placeholder="https://your-site.com/webhook">
                                        <small class="text-muted">Данные контакта и выигрыша будут отправлены на этот URL</small>
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

                                    <hr>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Сообщение при достижении лимита</label>
                                        <input type="text" class="form-control" x-model="settings.messages.spin_limit_reached" placeholder="Вы уже использовали все попытки">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Сообщение при отказе от ввода контакта</label>
                                        <input type="text" class="form-control" x-model="settings.messages.fill_contact" placeholder="Пожалуйста, укажите контактные данные">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Сообщение о согласии с условиями</label>
                                        <input type="text" class="form-control" x-model="settings.messages.accept_terms" placeholder="Пожалуйста, согласитесь с условиями">
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА ДИЗАЙН ==================== -->
                                <div class="tab-pane" id="design-tab" data-pane="design-tab" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Заголовок</label>
                                        <input type="text" class="form-control" x-model="settings.design.title" placeholder="Выиграйте приз!">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Описание</label>
                                        <textarea class="form-control" rows="2" x-model="settings.design.description" placeholder="Крутите колесо и получите скидку"></textarea>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет фона модального окна</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.modal_bg_color">
                                            <input type="text" class="form-control" x-model="settings.design.modal_bg_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Цвет текста</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.modal_text_color">
                                            <input type="text" class="form-control" x-model="settings.design.modal_text_color">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Акцентный цвет</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.accent_color">
                                            <input type="text" class="form-control" x-model="settings.design.accent_color">
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <label class="form-label">Заголовок выигрыша</label>
                                        <input type="text" class="form-control" x-model="settings.form.title" placeholder="Поздравляем!">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Сообщение после выигрыша</label>
                                        <textarea class="form-control" rows="2" x-model="settings.form.success_message" placeholder="Ваш купон: {CODE}"></textarea>
                                        <small class="text-muted">Используйте {CODE} для подстановки кода купона</small>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <label class="form-label">Текст согласия с условиями</label>
                                        <input type="text" class="form-control" x-model="settings.form.terms_text" placeholder="Я согласен с условиями розыгрыша">
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
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }"
                 x-init="$watch('previewMode', (value) => updatePreviewMode(value))">
                @include("widgets.configuration.preview")
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .nav-tabs .nav-link { cursor: pointer; }
        .coupons-list, .form-fields-list { overflow-y: auto; }
        .tab-pane { transition: none; }
        .card-header .form-check-input { cursor: pointer; }
    </style>

@endsection
@include("widgets.fortune-wheel.configuration_js")

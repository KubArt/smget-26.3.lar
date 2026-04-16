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

                        <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig">
                            <i class="fa fa-save me-1"></i> Сохранить изменения
                        </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ПРЕДПРОСМОТР -->
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }" x-init="$watch('previewMode', () => $dispatch('preview-mode-changed', previewMode))">
                @include("widgets.configuration.preview")
            </div>

        </div>
    </div>
@endsection
@include("widgets.configuration.js")

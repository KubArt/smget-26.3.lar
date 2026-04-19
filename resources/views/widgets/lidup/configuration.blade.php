@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="lidupEditor({{ json_encode($config) }})" x-init="init">
        <div class="row">
            <!-- КОЛОНКА НАСТРОЕК -->
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка LidUp Popup: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form action="{{ route('cabinet.sites.widgets.design.update', [$site, $widget]) }}" method="POST" id="saveForm">
                        @csrf

                        <!-- Вкладки -->
                            <ul class="nav nav-tabs nav-tabs-alt mb-3" role="tablist" id="widgetTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-tab="content-tab" href="#" @click.prevent="switchTab('content-tab')">Контент</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="form-tab" href="#" @click.prevent="switchTab('form-tab')">Форма</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="behavior-tab" href="#" @click.prevent="switchTab('behavior-tab')">Поведение</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-tab="design-tab" href="#" @click.prevent="switchTab('design-tab')">Дизайн</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- ==================== ВКЛАДКА КОНТЕНТ ==================== -->
                                <div class="tab-pane active" id="content-tab" data-pane="content-tab">
                                    <!-- Выбор скина -->
                                    <div class="mb-4">
                                        <label class="form-label text-primary fw-bold">Выберите макет</label>
                                        <div class="row g-2">
                                            <template x-for="skin in skins" :key="skin.slug">
                                                <div class="col-6">
                                                    <button type="button"
                                                            class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                            :class="settings.template === skin.slug ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                            @click="applyTemplate(skin.slug)">
                                                        <div class="d-flex align-items-center">
                                                            <i class="fa fa-circle me-2 fs-xs" :class="settings.template === skin.slug ? 'text-primary' : 'text-muted'"></i>
                                                            <span class="small fw-semibold" x-text="skin.name"></span>
                                                        </div>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <input type="hidden" name="template" :value="settings.template">
                                    </div>

                                    <hr>

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Контент</label>

                                        <div class="mb-3">
                                            <label class="small text-muted">Заголовок</label>
                                            <input type="text" class="form-control" placeholder="Например: Получите скидку 20%" x-model="settings.title">
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Описание</label>
                                            <textarea class="form-control" rows="3" placeholder="Оставьте заявку прямо сейчас..." x-model="settings.description"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" x-model="settings.has_image">
                                                <label class="form-check-label small">Показывать изображение</label>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3" x-show="settings.has_image">
                                            <div class="col-8">
                                                <label class="small text-muted">URL изображения</label>
                                                <input type="text" class="form-control" placeholder="https://example.com/image.jpg" x-model="settings.image">
                                            </div>
                                            <div class="col-4">
                                                <label class="small text-muted">Позиция</label>
                                                <select class="form-select" x-model="settings.image_position">
                                                    <option value="left">Слева</option>
                                                    <option value="right">Справа</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА ФОРМА ==================== -->
                                <div class="tab-pane" id="form-tab" data-pane="form-tab" style="display: none;">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                            Поля формы
                                            <button type="button" class="btn btn-sm btn-primary" @click="addFormField">
                                                <i class="fa fa-plus me-1"></i> Добавить поле
                                            </button>
                                        </label>

                                        <div class="form-fields-list border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                                            <template x-for="(field, index) in settings.form_fields" :key="index">
                                                <div class="form-field-item bg-white border rounded p-2 mb-2">
                                                    <div class="row g-2 align-items-center">
                                                        <div class="col-3">
                                                            <select class="form-select form-select-sm" x-model="field.type">
                                                                <option value="text">Текст</option>
                                                                <option value="tel">Телефон</option>
                                                                <option value="email">Email</option>
                                                                <option value="name">Имя</option>
                                                                <option value="textarea">Текстовая область</option>
                                                                <option value="hidden">Скрытое поле</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-4">
                                                            <input type="text" class="form-control form-control-sm" placeholder="Название поля" x-model="field.label">
                                                        </div>
                                                        <div class="col-3" x-show="field.type !== 'hidden'">
                                                            <input type="text" class="form-control form-control-sm" placeholder="Placeholder" x-model="field.placeholder">
                                                        </div>
                                                        <div class="col-1" x-show="field.type !== 'hidden'">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" x-model="field.required">
                                                                <label class="small">Req</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-2" x-show="field.type === 'hidden'">
                                                            <input type="text" class="form-control form-control-sm" placeholder="Значение" x-model="field.default_value">
                                                        </div>
                                                        <div class="col-1">
                                                            <button type="button" class="btn btn-sm btn-link text-danger" @click="removeFormField(index)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="settings.form_fields.length === 0" class="text-center text-muted py-3">
                                                <i class="fa fa-edit fa-2x mb-2 opacity-25"></i>
                                                <p class="small mb-0">Нет полей формы. Нажмите "Добавить поле"</p>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <label class="small text-muted">Текст кнопки</label>
                                            <input type="text" class="form-control" placeholder="Отправить заявку" x-model="settings.btn_text">
                                        </div>

                                        <div class="mt-3">
                                            <label class="small text-muted">Сообщение после отправки</label>
                                            <input type="text" class="form-control" placeholder="Спасибо! Мы свяжемся с вами." x-model="settings.success_message">
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА ПОВЕДЕНИЕ ==================== -->
                                <div class="tab-pane" id="behavior-tab" data-pane="behavior-tab" style="display: none;">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Поведение</label>

                                        <div class="mb-3">
                                            <label class="small text-muted">Триггер показа</label>
                                            <select class="form-select" x-model="settings.trigger_type">
                                                <option value="time">По времени</option>
                                                <option value="scroll">При прокрутке страницы</option>
                                                <option value="exit">При уходе мыши с окна</option>
                                                <option value="click">По клику на элемент</option>
                                            </select>
                                        </div>

                                        <div class="mb-3" x-show="settings.trigger_type === 'time'">
                                            <label class="small text-muted">Задержка появления (сек)</label>
                                            <input type="number" class="form-control" min="0" max="30" step="0.5" x-model="settings.delay">
                                        </div>

                                        <div class="mb-3" x-show="settings.trigger_type === 'scroll'">
                                            <label class="small text-muted">Показать при прокрутке (%)</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="range" class="form-range flex-grow-1" x-model="settings.scroll_percent" min="0" max="100">
                                                <span class="badge bg-secondary" x-text="settings.scroll_percent + '%'"></span>
                                            </div>
                                        </div>

                                        <div class="mb-3" x-show="settings.trigger_type === 'click'">
                                            <label class="small text-muted">CSS селектор элемента для клика</label>
                                            <input type="text" class="form-control" placeholder="#open-popup, .open-popup" x-model="settings.click_selector">
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Частота показа</label>
                                            <select class="form-select" x-model="settings.frequency">
                                                <option value="always">Всегда показывать</option>
                                                <option value="once_session">Один раз за сессию</option>
                                                <option value="once_day">Один раз в день</option>
                                                <option value="once_week">Один раз в неделю</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">При закрытии пользователем</label>
                                            <select class="form-select" x-model="settings.close_behavior">
                                                <option value="hide_session">Не показывать до конца сессии</option>
                                                <option value="hide_forever">Больше никогда не показывать</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Авто-закрытие (сек)</label>
                                            <input type="number" class="form-control" min="0" max="60" placeholder="0 - не закрывать автоматически" x-model="settings.auto_close">
                                        </div>
                                    </div>
                                </div>

                                <!-- ==================== ВКЛАДКА ДИЗАЙН ==================== -->
                                <div class="tab-pane" id="design-tab" data-pane="design-tab" style="display: none;">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Внешний вид</label>

                                        <div class="mb-3">
                                            <label class="small text-muted">Позиция на экране</label>
                                            <select class="form-select" x-model="settings.position">
                                                <option value="center">Центр</option>
                                                <option value="top">Сверху</option>
                                                <option value="bottom">Снизу</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Анимация появления</label>
                                            <select class="form-select" x-model="settings.animation_in">
                                                <option value="fadeIn">Плавное появление</option>
                                                <option value="slideInUp">Снизу вверх</option>
                                                <option value="slideInDown">Сверху вниз</option>
                                                <option value="zoomIn">Увеличение</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Цвет фона попапа</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.bg_color">
                                                <input type="text" class="form-control" x-model="settings.design.bg_color">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Акцентный цвет (рамки, фокус)</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.accent_color">
                                                <input type="text" class="form-control" x-model="settings.design.accent_color">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Цвет кнопки отправки</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.btn_color">
                                                <input type="text" class="form-control" x-model="settings.design.btn_color">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Цвет текста кнопки</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.btn_text_color">
                                                <input type="text" class="form-control" x-model="settings.design.btn_text_color">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Цвет текста</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.design.text_color">
                                                <input type="text" class="form-control" x-model="settings.design.text_color">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Цвет overlay (затемнения)</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" style="width: 50px;" x-model="settings.overlay_color">
                                                <input type="text" class="form-control" x-model="settings.overlay_color">
                                            </div>
                                            <small class="text-muted fs-xs">Формат rgba(0,0,0,0.7) или #000000</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="small text-muted">Радиус скругления (px)</label>
                                            <input type="number" class="form-control" min="0" max="50" x-model="settings.design.border_radius">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig">
                                <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
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
    @include("widgets.lidup.configuration_js")
@endsection

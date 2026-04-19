@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="contactButtonEditor({{ json_encode($config) }})" x-init="init">
        <div class="row">
            <!-- КОЛОНКА НАСТРОЕК -->
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка Мультикнопки: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <!-- Вкладки -->
                        <ul class="nav nav-tabs nav-tabs-alt mb-3" role="tablist" id="widgetTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-tab="basic-tab" href="#" @click.prevent="switchTab('basic-tab')">Основные</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-tab="design-tab" href="#" @click.prevent="switchTab('design-tab')">Дизайн</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-tab="colors-tab" href="#" @click.prevent="switchTab('colors-tab')">Цвета</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-tab="channels-tab" href="#" @click.prevent="switchTab('channels-tab')">Каналы связи</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- ==================== ВКЛАДКА ОСНОВНЫЕ ==================== -->
                            <div class="tab-pane active" id="basic-tab" data-pane="basic-tab">
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

                                <!-- Основные параметры -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Основные параметры</label>

                                    <div class="mb-3">
                                        <label class="small text-muted">Позиция</label>
                                        <select class="form-select" x-model="settings.position">
                                            <option value="right">Справа внизу</option>
                                            <option value="left">Слева внизу</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted">Текст подсказки</label>
                                        <input type="text" class="form-control" placeholder="Например: Свяжитесь с нами" x-model="settings.main_tooltip">
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted">Задержка появления (сек)</label>
                                        <input type="number" class="form-control" min="0" max="10" step="0.5" x-model="settings.delay">
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== ВКЛАДКА ДИЗАЙН ==================== -->
                            <div class="tab-pane" id="design-tab" data-pane="design-tab" style="display: none;">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Внешний вид</label>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="small text-muted">Размер кнопки</label>
                                            <select class="form-select" x-model="settings.design.size">
                                                <option value="small">Маленькая</option>
                                                <option value="medium">Средняя</option>
                                                <option value="large">Большая</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="small text-muted">Эффект при наведении</label>
                                            <select class="form-select" x-model="settings.design.hover_effect">
                                                <option value="lift">Приподнимание</option>
                                                <option value="scale">Увеличение</option>
                                                <option value="glow">Свечение</option>
                                                <option value="rotate">Поворот</option>
                                                <option value="pulse">Пульсация</option>
                                                <option value="shake">Тряска</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted">Анимация кнопки</label>
                                        <select class="form-select" x-model="settings.animation.type">
                                            <option value="none">Нет анимации</option>
                                            <option value="wave">Волна</option>
                                            <option value="pulse">Пульсация</option>
                                            <option value="shake">Тряска</option>
                                            <option value="ring">Звонок</option>
                                            <option value="bounce">Подпрыгивание</option>
                                            <option value="glow">Свечение</option>
                                            <option value="spin">Вращение иконки</option>
                                            <option value="heartbeat">Сердцебиение</option>
                                            <option value="flash">Вспышка</option>
                                            <option value="swing">Раскачивание</option>
                                            <option value="wobble">Шаткий эффект</option>
                                            <option value="fade">Исчезание</option>
                                            <option value="rotate">Медленный поворот</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted">Прозрачность: <span x-text="settings.design.opacity"></span></label>
                                        <input type="range" class="form-range" min="0.1" max="1" step="0.1" x-model="settings.design.opacity">
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== ВКЛАДКА ЦВЕТА ==================== -->
                            <div class="tab-pane" id="colors-tab" data-pane="colors-tab" style="display: none;">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Цветовая схема</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="small text-muted">Цвет кнопки</label>
                                            <input type="color" class="form-control form-control-color" x-model="settings.design.main_color">
                                        </div>
                                        <div class="col-6">
                                            <label class="small text-muted">Цвет иконки</label>
                                            <input type="color" class="form-control form-control-color" x-model="settings.design.icon_color">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ==================== ВКЛАДКА КАНАЛЫ СВЯЗИ ==================== -->
                            <div class="tab-pane" id="channels-tab" data-pane="channels-tab" style="display: none;">
                                <div class="mb-4">
                                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                        Каналы связи
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="fa fa-plus me-1"></i> Добавить
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end shadow-lg">
                                                <template x-for="(info, type) in availableTypes" :key="type">
                                                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" @click="addChannel(type)">
                                                        <span x-html="getSvgIcon(type)" style="width: 20px; height: 20px; margin-right: 10px;"></span>
                                                        <span x-text="info.name"></span>
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </label>

                                    <div class="channels-list border rounded p-3 bg-light" style="max-height: 500px; overflow-y: auto;">
                                        <template x-for="(channel, index) in settings.channels" :key="channel.id">
                                            <div class="channel-item bg-white border rounded p-3 mb-3 shadow-sm">
                                                <div class="row g-2 align-items-start">
                                                    <div class="col-auto">
                                                        <div class="drag-handle me-2" style="cursor: grab;">
                                                            <i class="fa fa-grip-vertical text-muted"></i>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                             :style="`width: 40px; height: 40px; background: ${channel.bg_color}; cursor: pointer;`"
                                                             @click="$refs['chanColor'+index]?.click()">
                                                            <span x-html="getSvgIcon(channel.type)" style="width: 22px; height: 22px; color: ${channel.icon_color || '#fff'}"></span>
                                                        </div>
                                                        <input type="color" class="d-none" :x-ref="'chanColor'+index" x-model="channel.bg_color">
                                                    </div>
                                                    <div class="col">
                                                        <input type="text" class="form-control form-control-sm fw-bold mb-2" placeholder="Название" x-model="channel.label">
                                                        <input type="text" class="form-control form-control-sm" :placeholder="getActionPlaceholder(channel.type)" x-model="channel.action_value">
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="mb-2">
                                                            <input type="color" class="form-control form-control-color form-control-sm" style="width: 40px; height: 40px; padding: 0;" x-model="channel.icon_color" title="Цвет иконки">
                                                        </div>
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" x-model="channel.is_active">
                                                            <label class="small">Активен</label>
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-link text-danger w-100" @click="removeChannel(index)">
                                                            <i class="fa fa-trash-can"></i> Удалить
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="settings.channels.length === 0" class="text-center text-muted py-4">
                                            <i class="fa fa-comments fa-2x mb-2 opacity-25"></i>
                                            <p class="small mb-0">Нет добавленных каналов<br>Нажмите "Добавить" чтобы начать</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig">
                            <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                        </button>
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
@include("widgets.contact-button.configuration_js")

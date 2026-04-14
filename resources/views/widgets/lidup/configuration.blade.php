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

                            <!-- КОНТЕНТ -->
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
                                            <option value="top">Сверху</option>
                                            <option value="bottom">Снизу</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- ФОРМА -->
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

                                <div class="mt-3">
                                    <label class="small text-muted">Webhook URL (для отправки данных)</label>
                                    <input type="text" class="form-control" placeholder="https://your-server.com/webhook" x-model="settings.webhook_url">
                                </div>
                            </div>

                            <hr>

                            <!-- ТАЙМЕР -->
                            <div class="mb-4">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" x-model="settings.has_timer">
                                    <label class="form-check-label fw-bold">Показывать таймер обратного отсчета</label>
                                </div>

                                <div x-show="settings.has_timer">
                                    <div class="mb-3">
                                        <label class="small text-muted">Дата окончания акции</label>
                                        <input type="datetime-local" class="form-control" x-model="settings.timer_target_date">
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted">Заголовок таймера</label>
                                        <input type="text" class="form-control" placeholder="До конца акции осталось:" x-model="settings.timer_title">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- ПОВЕДЕНИЕ -->
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

                            <hr>

                            <!-- ДИЗАЙН -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Внешний вид</label>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="small text-muted">Размер попапа</label>
                                        <select class="form-select" x-model="settings.size">
                                            <option value="small">Маленький (400px)</option>
                                            <option value="medium">Средний (600px)</option>
                                            <option value="large">Большой (800px)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Позиция на экране</label>
                                        <select class="form-select" x-model="settings.position">
                                            <option value="center">Центр</option>
                                            <option value="top">Сверху</option>
                                            <option value="bottom">Снизу</option>
                                        </select>
                                    </div>
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
                                    <label class="small text-muted">Акцентный цвет (таймер, рамки)</label>
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

                            <hr>

                            <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig">
                                <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ПРЕДПРОСМОТР -->
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }">
                <div class="block block-rounded sticky-top" style="top: 20px;">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Предпросмотр</h3>
                        <div class="block-options">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'desktop' ? 'active' : ''" @click="previewMode = 'desktop'">
                                    <i class="fa fa-desktop me-1"></i> ПК
                                </button>
                                <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'tablet' ? 'active' : ''" @click="previewMode = 'tablet'">
                                    <i class="fa fa-tablet-alt me-1"></i> Планшет
                                </button>
                                <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'mobile' ? 'active' : ''" @click="previewMode = 'mobile'">
                                    <i class="fa fa-mobile-alt me-1"></i> Мобильный
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
                            <small class="text-muted" x-text="previewMode === 'desktop' ? '1920px × 800px' : (previewMode === 'tablet' ? '768px × 800px' : '375px × 800px')"></small>
                        </div>
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
                height: 800px;
                display: flex;
                flex-direction: column;
                margin: 0 auto;
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
            .browser-mockup.desktop { width: 100%; max-width: 100%; }
            .browser-mockup.tablet { width: 768px; }
            .browser-mockup.mobile { width: 375px; }
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
            .browser-controls { min-width: 60px; text-align: right; }
            .browser-viewport {
                position: relative;
                flex-grow: 1;
                background: #fff;
                overflow-y: auto;
                overflow-x: hidden;
            }
            .site-placeholder { padding: 0; pointer-events: none; }
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
            @media (max-width: 768px) {
                .browser-mockup.tablet,
                .browser-mockup.mobile {
                    width: calc(100% - 32px);
                }
            }
            .form-field-item {
                border-left: 3px solid #3b82f6;
            }
        </style>

        @push('js')
            <script>
                function lidupEditor(config) {
                    return {
                        // Данные
                        slug: config.slug,
                        settings: config.settings,
                        skins: config.skins,
                        previewMode: 'desktop',

                        // Внутреннее состояние
                        rawTemplate: '',
                        rawCss: '',
                        shadowRoot: null,
                        widgetRoot: null,
                        timerInterval: null,

                        async init() {
                            // Инициализация настроек по умолчанию
                            if (!this.settings) {
                                this.settings = {};
                            }
                            if (!this.settings.form_fields || this.settings.form_fields.length === 0) {
                                this.settings.form_fields = [
                                    { type: 'text', name: 'name', label: 'Ваше имя', placeholder: 'Иван Иванов', required: true },
                                    { type: 'tel', name: 'phone', label: 'Телефон', placeholder: '+7 (999) 123-45-67', required: true }
                                ];
                            }
                            if (!this.settings.design) {
                                this.settings.design = {
                                    bg_color: '#FFFFFF',
                                    text_color: '#1F2937',
                                    accent_color: '#3B82F6',
                                    btn_color: '#22C55E',
                                    btn_text_color: '#FFFFFF',
                                    border_radius: '16'
                                };
                            }
                            if (!this.settings.trigger_type) this.settings.trigger_type = 'time';
                            if (!this.settings.delay) this.settings.delay = 3;
                            if (!this.settings.scroll_percent) this.settings.scroll_percent = 50;
                            if (!this.settings.frequency) this.settings.frequency = 'once_session';
                            if (!this.settings.close_behavior) this.settings.close_behavior = 'hide_session';
                            if (!this.settings.size) this.settings.size = 'medium';
                            if (!this.settings.position) this.settings.position = 'center';
                            if (!this.settings.animation_in) this.settings.animation_in = 'fadeIn';
                            if (!this.settings.overlay_color) this.settings.overlay_color = 'rgba(0,0,0,0.7)';
                            if (!this.settings.btn_text) this.settings.btn_text = 'Отправить заявку';
                            if (!this.settings.success_message) this.settings.success_message = 'Спасибо! Мы свяжемся с вами.';
                            if (!this.settings.title) this.settings.title = 'Получите скидку 20%';
                            if (!this.settings.template) this.settings.template = Object.keys(this.skins)[0] || 'default';

                            await this.loadSkin(this.settings.template);
                            this.$watch('settings', () => this.updatePreview(), { deep: true });
                        },

                        async loadSkin(skinId) {
                            try {
                                const baseUrl = `/widgets/${this.slug}/skins/${skinId}`;
                                const [htmlRes, cssRes] = await Promise.all([
                                    fetch(`${baseUrl}/template.html`),
                                    fetch(`${baseUrl}/style.css`)
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

                            this.shadowRoot = container.shadowRoot || container.attachShadow({ mode: 'open' });

                            let css = this.rawCss;
                            css = css.replace(/position:\s*fixed/g, 'position: absolute');
                            css = css.replace(/position:fixed/g, 'position: absolute');
                            css = css.replace(/100vh/g, '100%');
                            css = css.replace(/100vw/g, '100%');

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
                            ${css}
                        </style>
                        <div id="widget-root"></div>
                    `;
                            this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                        },

                        updatePreview() {
                            if (!this.widgetRoot || !this.rawTemplate) return;

                            // Генерация полей формы
                            const formFieldsHtml = this.generateFormFields();

                            // Генерация изображения
                            const imageHtml = this.settings.has_image && this.settings.image
                                ? `<img src="${this.settings.image}" class="sp-lidup-image" alt="${this.escapeHtml(this.settings.title)}">`
                                : '';

                            // Таймер
                            const hasTimer = this.settings.has_timer && this.settings.timer_target_date;
                            const timerDisplay = hasTimer ? 'block' : 'none';

                            // Подстановка в HTML
                            let html = this.rawTemplate
                                .replace(/\{title\}/g, this.escapeHtml(this.settings.title || ''))
                                .replace(/\{description\}/g, this.escapeHtml(this.settings.description || ''))
                                .replace(/\{image_html\}/g, imageHtml)
                                .replace(/\{image_position\}/g, this.settings.image_position || 'left')
                                .replace(/\{btn_text\}/g, this.escapeHtml(this.settings.btn_text || 'Отправить'))
                                .replace(/\{timer_display\}/g, timerDisplay)
                                .replace(/\{timer_title\}/g, this.escapeHtml(this.settings.timer_title || 'До конца акции осталось:'))
                                .replace(/\{timer_days_text\}/g, this.escapeHtml(this.settings.timer_days_text || 'дней'))
                                .replace(/\{timer_hours_text\}/g, this.escapeHtml(this.settings.timer_hours_text || 'часов'))
                                .replace(/\{timer_minutes_text\}/g, this.escapeHtml(this.settings.timer_minutes_text || 'минут'))
                                .replace(/\{timer_seconds_text\}/g, this.escapeHtml(this.settings.timer_seconds_text || 'секунд'))
                                .replace(/\{size\}/g, this.settings.size || 'medium')
                                .replace(/\{position\}/g, this.settings.position || 'center')
                                .replace(/\{animation_in\}/g, this.settings.animation_in || 'fadeIn');

                            this.widgetRoot.innerHTML = html;

                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            // Применяем CSS переменные
                            widget.style.setProperty('--bg-color', this.settings.design?.bg_color || '#FFFFFF');
                            widget.style.setProperty('--text-color', this.settings.design?.text_color || '#1F2937');
                            widget.style.setProperty('--accent-color', this.settings.design?.accent_color || '#3B82F6');
                            widget.style.setProperty('--btn-color', this.settings.design?.btn_color || '#22C55E');
                            widget.style.setProperty('--btn-text-color', this.settings.design?.btn_text_color || '#FFFFFF');
                            widget.style.setProperty('--border-radius', this.settings.design?.border_radius || '16');
                            widget.style.setProperty('--overlay-color', this.settings.overlay_color || 'rgba(0,0,0,0.7)');

                            // Вставляем поля формы
                            const formFieldsContainer = widget.querySelector('#sp-form-fields');
                            if (formFieldsContainer) {
                                formFieldsContainer.innerHTML = formFieldsHtml;
                            }

                            // Запускаем таймер в предпросмотре
                            if (hasTimer && this.settings.timer_target_date) {
                                this.startPreviewTimer(widget);
                            }

                            // Показываем попап в предпросмотре
                            setTimeout(() => {
                                widget.classList.add('sp-active');
                            }, 500);
                        },

                        generateFormFields() {
                            const fields = this.settings.form_fields || [];
                            return fields.map(field => {
                                const required = field.required ? 'required' : '';
                                const placeholder = this.escapeHtml(field.placeholder || field.label || '');
                                const name = field.name || field.type + '_' + Math.random();

                                if (field.type === 'textarea') {
                                    return `<textarea name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field"></textarea>`;
                                }
                                return `<input type="${field.type}" name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field">`;
                            }).join('');
                        },

                        startPreviewTimer(widget) {
                            if (this.timerInterval) clearInterval(this.timerInterval);

                            const targetDate = new Date(this.settings.timer_target_date).getTime();
                            if (isNaN(targetDate)) return;

                            const daysEl = widget.querySelector('.sp-timer-days');
                            const hoursEl = widget.querySelector('.sp-timer-hours');
                            const minutesEl = widget.querySelector('.sp-timer-minutes');
                            const secondsEl = widget.querySelector('.sp-timer-seconds');

                            if (!daysEl) return;

                            this.timerInterval = setInterval(() => {
                                const now = Date.now();
                                const diff = targetDate - now;

                                if (diff <= 0) {
                                    clearInterval(this.timerInterval);
                                    daysEl.textContent = '00';
                                    hoursEl.textContent = '00';
                                    minutesEl.textContent = '00';
                                    secondsEl.textContent = '00';
                                    return;
                                }

                                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                                const hours = Math.floor((diff % (86400000)) / (1000 * 60 * 60));
                                const minutes = Math.floor((diff % (3600000)) / (1000 * 60));
                                const seconds = Math.floor((diff % (60000)) / 1000);

                                daysEl.textContent = days.toString().padStart(2, '0');
                                hoursEl.textContent = hours.toString().padStart(2, '0');
                                minutesEl.textContent = minutes.toString().padStart(2, '0');
                                secondsEl.textContent = seconds.toString().padStart(2, '0');
                            }, 1000);
                        },

                        async applyTemplate(skinId) {
                            if (this.settings.template === skinId) return;
                            this.settings.template = skinId;
                            await this.loadSkin(skinId);
                        },

                        addFormField() {
                            if (!this.settings.form_fields) this.settings.form_fields = [];
                            this.settings.form_fields.push({
                                type: 'text',
                                name: 'field_' + Date.now(),
                                label: 'Новое поле',
                                placeholder: 'Введите значение',
                                required: false
                            });
                        },

                        removeFormField(index) {
                            this.settings.form_fields.splice(index, 1);
                        },

                        hexToRgb(hex) {
                            hex = hex.replace(/^#/, '');
                            if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
                            const int = parseInt(hex, 16);
                            return { r: (int >> 16) & 255, g: (int >> 8) & 255, b: int & 255 };
                        },

                        escapeHtml(str) {
                            if (!str) return '';
                            const div = document.createElement('div');
                            div.textContent = str;
                            return div.innerHTML;
                        },

                        saveConfig() {
                            const btn = event.currentTarget;
                            const original = btn.innerHTML;
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                            axios.post(window.location.href, { settings: this.settings })
                                .then(response => {
                                    if (response.data.status === 'success') {
                                        if (typeof showNotification === 'function') {
                                            showNotification(response.data.message, 'success');
                                        } else {
                                            alert(response.data.message);
                                        }
                                    }
                                })
                                .catch(error => {
                                    const msg = error.response?.data?.message || 'Ошибка при сохранении';
                                    if (typeof showNotification === 'function') {
                                        showNotification(msg, 'danger');
                                    } else {
                                        alert(msg);
                                    }
                                })
                                .finally(() => {
                                    btn.disabled = false;
                                    btn.innerHTML = original;
                                });
                        }
                    };
                }
            </script>
    @endpush
@endsection

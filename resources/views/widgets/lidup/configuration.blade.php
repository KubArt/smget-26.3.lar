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


        @push('js')
            <script>
                function lidupEditor(config) {
                    return {
                        slug: config.slug,
                        settings: config.settings,
                        skins: config.skins,
                        rawTemplate: '',
                        rawCss: '',
                        shadowRoot: null,
                        widgetRoot: null,

                        async init() {
                            // Инициализация настроек по умолчанию
                            if (!this.settings) this.settings = {};

                            // Дефолтные поля формы (имя + телефон)
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

                            // Базовые настройки
                            if (!this.settings.trigger_type) this.settings.trigger_type = 'time';
                            if (!this.settings.delay) this.settings.delay = 3;
                            if (!this.settings.scroll_percent) this.settings.scroll_percent = 50;
                            if (!this.settings.frequency) this.settings.frequency = 'once_session';
                            if (!this.settings.close_behavior) this.settings.close_behavior = 'hide_session';
                            if (!this.settings.auto_close) this.settings.auto_close = 0;
                            if (!this.settings.position) this.settings.position = 'center';
                            if (!this.settings.animation_in) this.settings.animation_in = 'fadeIn';
                            if (!this.settings.overlay_color) this.settings.overlay_color = 'rgba(0,0,0,0.7)';
                            if (!this.settings.btn_text) this.settings.btn_text = 'Отправить заявку';
                            if (!this.settings.success_message) this.settings.success_message = 'Спасибо! Мы свяжемся с вами.';
                            if (!this.settings.title) this.settings.title = 'Получите скидку 20%';
                            if (!this.settings.template) this.settings.template = Object.keys(this.skins)[0] || 'default';

                            await this.loadSkin(this.settings.template);

                            // Отдельные watchers для каждого поля (чтобы не обновлять весь preview)
                            this.$watch('settings.title', () => this.updateContent());
                            this.$watch('settings.description', () => this.updateContent());
                            this.$watch('settings.has_image', () => this.updateContent());
                            this.$watch('settings.image', () => this.updateContent());
                            this.$watch('settings.image_position', () => this.updateContent());
                            this.$watch('settings.btn_text', () => this.updateContent());
                            this.$watch('settings.form_fields', () => this.updateFormFields(), { deep: true });
                            this.$watch('settings.position', () => this.updatePosition());
                            this.$watch('settings.animation_in', () => this.updateAnimation());
                            this.$watch('settings.design', () => this.updateColors(), { deep: true });
                            this.$watch('settings.overlay_color', () => this.updateColors());
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

                            // Генерация изображения
                            const imageHtml = this.settings.has_image && this.settings.image
                                ? `<img src="${this.settings.image}" class="sp-lidup-image" alt="${this.escapeHtml(this.settings.title)}">`
                                : '';

                            let html = this.rawTemplate
                                .replace(/\{title\}/g, this.escapeHtml(this.settings.title || ''))
                                .replace(/\{description\}/g, this.escapeHtml(this.settings.description || ''))
                                .replace(/\{image_html\}/g, imageHtml)
                                .replace(/\{image_position\}/g, this.settings.image_position || 'left')
                                .replace(/\{btn_text\}/g, this.escapeHtml(this.settings.btn_text || 'Отправить'))
                                .replace(/\{position\}/g, this.settings.position || 'center')
                                .replace(/\{animation_in\}/g, this.settings.animation_in || 'fadeIn');

                            this.widgetRoot.innerHTML = html;

                            const widget = this.widgetRoot.firstElementChild;
                            if (widget) {
                                this.applyColors(widget);
                                this.updateFormFields();

                                // Показываем попап в предпросмотре
                                setTimeout(() => widget.classList.add('sp-active'), 100);
                            }
                        },

                        updateContent() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            // Обновляем заголовок и описание
                            const titleEl = widget.querySelector('.sp-lidup-title');
                            const descEl = widget.querySelector('.sp-lidup-description');
                            const btnEl = widget.querySelector('.sp-lidup-submit');
                            const imageContainer = widget.querySelector('.sp-lidup-content');

                            if (titleEl) titleEl.textContent = this.settings.title || '';
                            if (descEl) descEl.textContent = this.settings.description || '';
                            if (btnEl) btnEl.textContent = this.settings.btn_text || 'Отправить';

                            // Обновляем изображение
                            if (imageContainer) {
                                const oldImage = imageContainer.querySelector('.sp-lidup-image');
                                if (oldImage) oldImage.remove();

                                if (this.settings.has_image && this.settings.image) {
                                    const img = document.createElement('img');
                                    img.src = this.settings.image;
                                    img.className = 'sp-lidup-image';
                                    img.alt = this.settings.title || '';
                                    imageContainer.insertBefore(img, imageContainer.firstChild);
                                }

                                // Обновляем класс позиции изображения
                                imageContainer.classList.remove('sp-image-left', 'sp-image-right', 'sp-image-top', 'sp-image-bottom');
                                imageContainer.classList.add(`sp-image-${this.settings.image_position || 'left'}`);
                            }
                        },

                        updateFormFields() {
                            if (!this.widgetRoot) return;
                            const formFieldsContainer = this.widgetRoot.querySelector('#sp-form-fields');
                            if (!formFieldsContainer) return;

                            const fields = this.settings.form_fields || [];
                            const fieldsHtml = fields.map(field => {
                                const required = field.required ? 'required' : '';
                                const placeholder = this.escapeHtml(field.placeholder || field.label || '');
                                const name = field.name || field.type + '_' + Date.now() + '_' + Math.random();

                                if (field.type === 'hidden') {
                                    return `<input type="hidden" name="${name}" value="${this.escapeHtml(field.default_value || '')}">`;
                                }
                                if (field.type === 'textarea') {
                                    return `<textarea name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field"></textarea>`;
                                }
                                return `<input type="${field.type}" name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field">`;
                            }).join('');

                            formFieldsContainer.innerHTML = fieldsHtml;
                        },

                        updatePosition() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            const popup = widget.querySelector('.sp-lidup-popup');
                            if (popup) {
                                popup.classList.remove('sp-position-center', 'sp-position-top', 'sp-position-bottom');
                                popup.classList.add(`sp-position-${this.settings.position || 'center'}`);
                            }
                        },

                        updateAnimation() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            widget.classList.remove('sp-fadeIn', 'sp-slideInUp', 'sp-slideInDown', 'sp-zoomIn');
                            widget.classList.add(`sp-${this.settings.animation_in || 'fadeIn'}`);
                        },

                        applyColors(widget) {
                            const design = this.settings.design || {};
                            widget.style.setProperty('--bg-color', design.bg_color || '#FFFFFF');
                            widget.style.setProperty('--text-color', design.text_color || '#1F2937');
                            widget.style.setProperty('--accent-color', design.accent_color || '#3B82F6');
                            widget.style.setProperty('--btn-color', design.btn_color || '#22C55E');
                            widget.style.setProperty('--btn-text-color', design.btn_text_color || '#FFFFFF');
                            widget.style.setProperty('--border-radius', design.border_radius || '16');
                            widget.style.setProperty('--overlay-color', this.settings.overlay_color || 'rgba(0,0,0,0.7)');
                        },

                        updateColors() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (widget) this.applyColors(widget);
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

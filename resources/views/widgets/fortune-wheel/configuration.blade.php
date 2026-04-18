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
                        <!-- Вкладки настроек -->
                        <ul class="nav nav-tabs nav-tabs-alt mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" @click.prevent="activeTab = 'basic'" href="#basic" role="tab">
                                    <i class="fa fa-sliders-h me-1"></i> Основные
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" @click.prevent="activeTab = 'wheel'" href="#wheel" role="tab">
                                    <i class="fa fa-circle-notch me-1"></i> Колесо
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" @click.prevent="activeTab = 'form'" href="#form" role="tab">
                                    <i class="fa fa-envelope me-1"></i> Форма
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" @click.prevent="activeTab = 'design'" href="#design" role="tab">
                                    <i class="fa fa-palette me-1"></i> Дизайн
                                </a>
                            </li>
                        </ul>

                        <!-- Вкладка: Основные -->
                        <div x-show="activeTab === 'basic'" x-cloak>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Позиция кнопки</label>
                                <select class="form-select" x-model="settings.button.position">
                                    <option value="bottom-right">Снизу справа</option>
                                    <option value="bottom-left">Снизу слева</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Текст кнопки</label>
                                <input type="text" class="form-control" x-model="settings.button.text">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Иконка кнопки</label>
                                <input type="text" class="form-control" x-model="settings.button.icon">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Автооткрытие (сек)</label>
                                <input type="number" class="form-control" min="0" max="30" x-model="settings.button.auto_open_delay">
                                <small class="text-muted">0 - не открывать автоматически</small>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Лимиты</label>

                                <div class="mb-3">
                                    <label class="small text-muted">Частота показа</label>
                                    <select class="form-select" x-model="settings.limits.frequency">
                                        <option value="always">Всегда</option>
                                        <option value="once_session">Один раз за сессию</option>
                                        <option value="once_day">Один раз в день</option>
                                        <option value="once_forever">Один раз навсегда</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Вкладка: Колесо -->
                        <div x-show="activeTab === 'wheel'" x-cloak>
                            <div class="mb-4">
                                <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    Сегменты колеса
                                    <button type="button" class="btn btn-sm btn-primary" @click="addSegment">
                                        <i class="fa fa-plus me-1"></i> Добавить
                                    </button>
                                </label>

                                <div class="segments-list border rounded p-3 bg-light" style="max-height: 400px; overflow-y: auto;">
                                    <template x-for="(segment, index) in settings.wheel.segments" :key="index">
                                        <div class="segment-item bg-white border rounded p-3 mb-2">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-5">
                                                    <input type="text" class="form-control form-control-sm" placeholder="Название" x-model="segment.label">
                                                </div>
                                                <div class="col-3">
                                                    <input type="color" class="form-control form-control-color" x-model="segment.bg_color">
                                                </div>
                                                <div class="col-3">
                                                    <input type="text" class="form-control form-control-sm" placeholder="Код купона" x-model="segment.value">
                                                </div>
                                                <div class="col-1">
                                                    <button type="button" class="btn btn-sm btn-link text-danger" @click="removeSegment(index)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="settings.wheel.segments.length === 0" class="text-center text-muted py-3">
                                        <i class="fa fa-circle-notch fa-2x mb-2 opacity-25"></i>
                                        <p class="small mb-0">Нет сегментов. Добавьте хотя бы 2 сегмента</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Параметры колеса</label>

                                <div class="mb-3">
                                    <label class="small text-muted">Размер (px)</label>
                                    <input type="number" class="form-control" min="200" max="400" step="10" x-model="settings.wheel.size">
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Скорость вращения (сек)</label>
                                    <input type="number" class="form-control" min="2" max="8" step="0.5" x-model="settings.wheel.rotation_speed">
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Цвет указателя</label>
                                    <input type="color" class="form-control form-control-color" x-model="settings.wheel.pointer_color">
                                </div>
                            </div>
                        </div>

                        <!-- Вкладка: Форма -->
                        <div x-show="activeTab === 'form'" x-cloak>
                            <div class="mb-4">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" x-model="settings.form.enabled">
                                    <label class="form-check-label">Включить сбор данных</label>
                                </div>
                            </div>

                            <div x-show="settings.form.enabled">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Заголовок формы</label>
                                    <input type="text" class="form-control" x-model="settings.form.title">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                        Поля формы
                                        <button type="button" class="btn btn-sm btn-primary" @click="addFormField">
                                            <i class="fa fa-plus me-1"></i> Добавить
                                        </button>
                                    </label>

                                    <div class="form-fields-list border rounded p-3 bg-light">
                                        <template x-for="(field, index) in settings.form.fields" :key="index">
                                            <div class="bg-white border rounded p-2 mb-2">
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-4">
                                                        <select class="form-select form-select-sm" x-model="field.type">
                                                            <option value="text">Текст</option>
                                                            <option value="tel">Телефон</option>
                                                            <option value="email">Email</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="text" class="form-control form-control-sm" placeholder="Placeholder" x-model="field.placeholder">
                                                    </div>
                                                    <div class="col-3">
                                                        <button type="button" class="btn btn-sm btn-link text-danger" @click="removeFormField(index)">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Текст кнопки</label>
                                    <input type="text" class="form-control" x-model="settings.form.button_text">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Сообщение об успехе</label>
                                    <input type="text" class="form-control" x-model="settings.form.success_message">
                                </div>
                            </div>
                        </div>

                        <!-- Вкладка: Дизайн -->
                        <div x-show="activeTab === 'design'" x-cloak>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Цвета кнопки</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small text-muted">Фон</label>
                                        <input type="color" class="form-control form-control-color" x-model="settings.button.bg_color">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Текст</label>
                                        <input type="color" class="form-control form-control-color" x-model="settings.button.text_color">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Цвета модального окна</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small text-muted">Фон</label>
                                        <input type="color" class="form-control form-control-color" x-model="settings.design.modal_bg_color">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Акцент</label>
                                        <input type="color" class="form-control form-control-color" x-model="settings.design.accent_color">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Заголовок и описание</label>
                                <input type="text" class="form-control mb-2" placeholder="Заголовок" x-model="settings.design.title">
                                <textarea class="form-control" rows="2" placeholder="Описание" x-model="settings.design.description"></textarea>
                            </div>
                        </div>

                        <hr>

                        <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig" :disabled="isSaving">
                            <i class="fa fa-save opacity-50 me-1"></i>
                            <span x-show="!isSaving">Сохранить изменения</span>
                            <span x-show="isSaving">Сохранение...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ПРЕДПРОСМОТР -->
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }"
                 x-init="$watch('previewMode', (value) => updatePreviewMode(value))">
                @include("widgets.configuration.preview")
                <style>
                    #preview-host {
                        display: block;
                        min-height: 500px; /* Резервируем место под раскрытое колесо */
                        width: 100%;
                        position: relative;
                    }
                </style>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .nav-tabs .nav-link { cursor: pointer; }
        .segments-list, .form-fields-list { max-height: 400px; overflow-y: auto; }
    </style>

@endsection

@push('js')
    <script>
        function fortuneWheelEditor(config) {
            return {
                // Данные
                slug: config.slug,
                settings: config.settings,
                skins: config.skins,

                // Preview
                rawTemplate: '',
                rawCss: '',
                shadowRoot: null,
                widgetRoot: null,
                previewMode: 'desktop',

                // UI
                isLoading: false,
                isSaving: false,
                activeTab: 'basic',

                // Состояние виджета в preview
                isSpinning: false,
                currentRotation: 0,
                currentWonSegment: null,

                // Инициализация
                async init() {
                    this.initDefaultSettings();
                    await this.loadSkin(this.settings.template);

                    this.$watch('settings', () => this.updatePreview(), { deep: true });
                },

                // Настройки по умолчанию
                initDefaultSettings() {
                    if (!this.settings) this.settings = {};

                    if (!this.settings.button) {
                        this.settings.button = {
                            position: 'bottom-right',
                            text: 'Крутить колесо',
                            icon: '🎡',
                            bg_color: '#6366f1',
                            text_color: '#ffffff',
                            auto_open_delay: 0
                        };
                    }

                    if (!this.settings.wheel) {
                        this.settings.wheel = {
                            size: 280,
                            rotation_speed: 4,
                            text_color: '#333333',
                            pointer_color: '#ff4444',
                            font_size: 12,
                            segments: [
                                { label: 'Скидка 10%', bg_color: '#f1f5f9', value: 'DISCOUNT10' },
                                { label: 'Скидка 20%', bg_color: '#e2e8f0', value: 'DISCOUNT20' },
                                { label: 'Скидка 30%', bg_color: '#f1f5f9', value: 'DISCOUNT30' },
                                { label: 'Бесплатная доставка', bg_color: '#e2e8f0', value: 'FREESHIP' }
                            ]
                        };
                    }

                    if (!this.settings.design) {
                        this.settings.design = {
                            modal_bg_color: '#ffffff',
                            modal_text_color: '#1f2937',
                            accent_color: '#6366f1',
                            title: 'Выиграйте приз!',
                            description: 'Испытайте свою удачу прямо сейчас'
                        };
                    }

                    if (!this.settings.form) {
                        this.settings.form = {
                            enabled: true,
                            title: 'Поздравляем!',
                            fields: [
                                { type: 'text', placeholder: 'Ваше имя', required: true },
                                { type: 'email', placeholder: 'Ваш Email', required: true }
                            ],
                            button_text: 'Получить приз',
                            success_message: 'Ваш купон: {CODE}'
                        };
                    }

                    if (!this.settings.limits) {
                        this.settings.limits = {
                            spins_per_user: 1,
                            frequency: 'once_session'
                        };
                    }

                    if (!this.settings.template) {
                        this.settings.template = Object.keys(this.skins)[0] || 'default';
                    }
                },

                async loadSkin(skinId) {
                    try {
                        this.isLoading = true;
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
                        this.showError('Не удалось загрузить скин');
                    } finally {
                        this.isLoading = false;
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

                    // Подставляем значения в шаблон
                    let html = this.rawTemplate
                        .replace(/{id}/g, 'preview')
                        .replace(/{widget_id}/g, 'preview')
                        .replace(/{position}/g, this.settings.button.position === 'bottom-right' ? 'right' : 'left')
                        .replace(/{wheel_size}/g, this.settings.wheel.size)
                        .replace(/{title}/g, this.escapeHtml(this.settings.design.title))
                        .replace(/{description}/g, this.escapeHtml(this.settings.design.description));

                    this.widgetRoot.innerHTML = html;

                    // Получаем корневой элемент виджета
                    const widget = this.widgetRoot.querySelector('.sfw-root');
                    if (!widget) return;

                    // Добавляем класс позиции
                    if (this.settings.button.position === 'bottom-right') {
                        widget.classList.add('sp-position-right');
                        widget.classList.remove('sp-position-left');
                    } else {
                        widget.classList.add('sp-position-left');
                        widget.classList.remove('sp-position-right');
                    }

                    // Применяем CSS переменные
                    widget.style.setProperty('--sfw-btn-bg', this.settings.button.bg_color);
                    widget.style.setProperty('--sfw-btn-text', this.settings.button.text_color);
                    widget.style.setProperty('--sfw-accent', this.settings.design.accent_color);
                    widget.style.setProperty('--sfw-modal-bg', this.settings.design.modal_bg_color);
                    widget.style.setProperty('--sfw-modal-text', this.settings.design.modal_text_color);
                    widget.style.setProperty('--sfw-pointer', this.settings.wheel.pointer_color);

                    // Обновляем кнопку-триггер
                    const trigger = widget.querySelector('.sfw-trigger');
                    if (trigger) {
                        trigger.style.background = this.settings.button.bg_color;
                        trigger.style.color = this.settings.button.text_color;
                        const iconSpan = trigger.querySelector('.sfw-icon');
                        if (iconSpan) iconSpan.textContent = this.settings.button.icon;
                    }

                    // Обновляем кнопку вращения
                    const spinBtn = widget.querySelector('.sfw-spin-trigger');
                    if (spinBtn) {
                        spinBtn.textContent = this.settings.button.text;
                    }

                    // Рисуем колесо
                    this.drawWheel(widget);

                    // Привязываем события
                    this.attachEvents(widget);
                },

                drawWheel(widget) {
                    const canvas = widget.querySelector('#sfw-canvas-preview');
                    if (!canvas) return;

                    const ctx = canvas.getContext('2d');
                    const segments = this.settings.wheel.segments || [];
                    const size = this.settings.wheel.size || 280;

                    canvas.width = size;
                    canvas.height = size;

                    if (segments.length === 0) {
                        ctx.fillStyle = '#e5e7eb';
                        ctx.fillRect(0, 0, size, size);
                        return;
                    }

                    const radius = size / 2;
                    const centerX = radius;
                    const centerY = radius;
                    const arc = (2 * Math.PI) / segments.length;

                    ctx.clearRect(0, 0, size, size);

                    segments.forEach((seg, i) => {
                        const startAngle = i * arc;
                        const endAngle = startAngle + arc;

                        ctx.beginPath();
                        ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
                        ctx.fill();

                        ctx.beginPath();
                        ctx.strokeStyle = '#fff';
                        ctx.lineWidth = 2;
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
                        ctx.lineTo(centerX, centerY);
                        ctx.stroke();

                        ctx.save();
                        ctx.translate(centerX, centerY);
                        ctx.rotate(startAngle + arc / 2);
                        ctx.textAlign = "center";
                        ctx.fillStyle = this.settings.wheel.text_color || '#333';
                        ctx.font = `bold ${Math.min(this.settings.wheel.font_size || 12, 14)}px system-ui`;

                        let label = seg.label || '';
                        if (label.length > 12) label = label.slice(0, 10) + '..';
                        ctx.fillText(label, radius - 45, 5);
                        ctx.restore();
                    });

                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
                    ctx.fillStyle = '#fff';
                    ctx.fill();
                },

                attachEvents(widget) {
                    const self = this;

                    // Кнопка открытия модалки
                    const toggleBtn = widget.querySelector('[data-sp-toggle]');
                    if (toggleBtn) {
                        const newToggle = toggleBtn.cloneNode(true);
                        toggleBtn.parentNode.replaceChild(newToggle, toggleBtn);
                        newToggle.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            widget.classList.add('sp-active');
                        });
                    }

                    // Кнопки закрытия (оверлей и крестик)
                    const closeBtns = widget.querySelectorAll('[data-sp-close]');
                    closeBtns.forEach(btn => {
                        const newBtn = btn.cloneNode(true);
                        btn.parentNode.replaceChild(newBtn, btn);
                        newBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            widget.classList.remove('sp-active');
                            self.resetWheelRotation(widget);
                        });
                    });

                    // Кнопка Spin
                    const spinBtn = widget.querySelector('.sfw-spin-trigger');
                    if (spinBtn) {
                        const newSpin = spinBtn.cloneNode(true);
                        spinBtn.parentNode.replaceChild(newSpin, spinBtn);
                        newSpin.addEventListener('click', function(e) {
                            e.preventDefault();
                            self.startSpin(widget);
                        });
                    }
                },

                resetWheelRotation(widget) {
                    this.currentRotation = 0;
                    const canvas = widget.querySelector('#sfw-canvas-preview');
                    if (canvas) {
                        canvas.style.transform = 'rotate(0deg)';
                        canvas.style.transition = 'none';
                    }

                    // Восстанавливаем кнопку Spin
                    const formContainer = widget.querySelector('#sfw-form-fields-preview');
                    if (formContainer) {
                        formContainer.innerHTML = `<button class="sfw-spin-trigger">${this.escapeHtml(this.settings.button.text || 'Крутить колесо')}</button>`;
                        const newSpin = formContainer.querySelector('.sfw-spin-trigger');
                        if (newSpin) {
                            const self = this;
                            newSpin.addEventListener('click', function(e) {
                                e.preventDefault();
                                self.startSpin(widget);
                            });
                        }
                    }
                },

                startSpin(widget) {
                    if (this.isSpinning) return;

                    const canvas = widget.querySelector('#sfw-canvas-preview');
                    const segments = this.settings.wheel.segments;

                    if (!segments || segments.length === 0) {
                        alert('Добавьте сегменты колеса в настройках');
                        return;
                    }

                    this.isSpinning = true;

                    const winIndex = Math.floor(Math.random() * segments.length);
                    this.currentWonSegment = segments[winIndex];

                    const segmentDeg = 360 / segments.length;
                    const rotationNeeded = (360 - (winIndex * segmentDeg)) - (segmentDeg / 2);
                    const totalRotation = 1440 + rotationNeeded;

                    this.currentRotation += totalRotation;

                    canvas.style.transition = `transform ${this.settings.wheel.rotation_speed}s cubic-bezier(0.25, 0.1, 0.15, 1)`;
                    canvas.style.transform = `rotate(${this.currentRotation}deg)`;

                    setTimeout(() => {
                        this.isSpinning = false;
                        this.showWinForm(widget);
                    }, this.settings.wheel.rotation_speed * 1000);
                },

                showWinForm(widget) {
                    const formContainer = widget.querySelector('#sfw-form-fields-preview');
                    if (!formContainer) return;

                    const wonSegment = this.currentWonSegment;

                    if (!this.settings.form.enabled) {
                        formContainer.innerHTML = `<div class="sfw-win-msg">🎉 Вы выиграли: <strong>${this.escapeHtml(wonSegment.label)}</strong> 🎉</div>`;
                        return;
                    }

                    let html = `<h4 style="margin: 0 0 10px 0; font-size: 20px;">${this.escapeHtml(this.settings.form.title)}</h4>`;
                    html += `<p style="margin-bottom: 20px;">Ваш приз: <strong>${this.escapeHtml(wonSegment.label)}</strong></p>`;

                    (this.settings.form.fields || []).forEach(field => {
                        html += `<input type="${field.type}" placeholder="${this.escapeHtml(field.placeholder)}" class="sfw-input">`;
                    });

                    html += `<button class="sfw-submit-btn">${this.escapeHtml(this.settings.form.button_text)}</button>`;

                    formContainer.innerHTML = html;

                    const submitBtn = formContainer.querySelector('.sfw-submit-btn');
                    if (submitBtn) {
                        const self = this;
                        submitBtn.addEventListener('click', () => {
                            const couponCode = wonSegment.value || 'PROMO2024';
                            const msg = (self.settings.form.success_message || 'Ваш купон: {CODE}').replace('{CODE}', couponCode);
                            formContainer.innerHTML = `<div class="sfw-success-final">🎁 ${msg} 🎁</div>`;

                            setTimeout(() => {
                                widget.classList.remove('sp-active');
                                setTimeout(() => self.resetWheelRotation(widget), 300);
                            }, 2000);
                        });
                    }
                },

                async applyTemplate(skinId) {
                    if (this.settings.template === skinId) return;
                    this.settings.template = skinId;
                    await this.loadSkin(skinId);
                },

                addSegment() {
                    if (!this.settings.wheel.segments) this.settings.wheel.segments = [];
                    this.settings.wheel.segments.push({
                        label: 'Новый сегмент',
                        bg_color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0'),
                        value: 'PROMO' + Math.floor(Math.random()*1000)
                    });
                },

                removeSegment(index) {
                    this.settings.wheel.segments.splice(index, 1);
                },

                addFormField() {
                    if (!this.settings.form.fields) this.settings.form.fields = [];
                    this.settings.form.fields.push({
                        type: 'text',
                        placeholder: 'Новое поле'
                    });
                },

                removeFormField(index) {
                    this.settings.form.fields.splice(index, 1);
                },

                updatePreviewMode(mode) {
                    this.previewMode = mode;
                    this.$dispatch('preview-mode-changed', mode);
                },

                async saveConfig(event) {
                    this.isSaving = true;
                    try {
                        const response = await axios.post(window.location.href, { settings: this.settings });
                        if (response.data.status === 'success') {
                            this.showNotification(response.data.message, 'success');
                        }
                    } catch (error) {
                        this.showNotification('Ошибка при сохранении', 'danger');
                    } finally {
                        this.isSaving = false;
                    }
                },

                escapeHtml(str) {
                    if (!str) return '';
                    const div = document.createElement('div');
                    div.textContent = str;
                    return div.innerHTML;
                },

                showNotification(message, type) {
                    if (typeof window.showNotification === 'function') {
                        window.showNotification(message, type);
                    } else {
                        alert(message);
                    }
                },

                showError(message) {
                    this.showNotification(message, 'danger');
                }
            };
        }
    </script>
@endpush

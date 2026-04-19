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
                activeTab: 'button-tab',

                // Инициализация
                async init() {
                    this.initDefaultSettings();
                    await this.loadSkin(this.settings.template);
                    this.setupWatchers();
                },

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
                                { type: 'text', placeholder: 'Ваше имя' },
                                { type: 'email', placeholder: 'Ваш Email' }
                            ],
                            button_text: 'Получить приз',
                            success_message: 'Ваш купон: {CODE}'
                        };
                    }

                    if (!this.settings.limits) {
                        this.settings.limits = {
                            spins_per_user: 1,
                            frequency: 'once_session',
                            require_auth: false
                        };
                    }

                    if (!this.settings.trigger_type) this.settings.trigger_type = 'click';
                    if (!this.settings.delay) this.settings.delay = 3;
                    if (!this.settings.scroll_percent) this.settings.scroll_percent = 50;
                    if (!this.settings.frequency) this.settings.frequency = 'always';
                    if (!this.settings.close_behavior) this.settings.close_behavior = 'hide_session';

                    if (!this.settings.template) {
                        this.settings.template = Object.keys(this.skins)[0] || 'default';
                    }
                },

                setupWatchers() {
                    this.$watch('settings.button.position', () => this.updatePosition());
                    this.$watch('settings.button.bg_color', () => this.updateTriggerColor());
                    this.$watch('settings.button.icon', () => this.updateTriggerIcon());
                    this.$watch('settings.button.text', () => this.updateSpinButtonText());
                    this.$watch('settings.design.title', () => this.updateTitle());
                    this.$watch('settings.design.description', () => this.updateDescription());
                    this.$watch('settings.design.accent_color', () => this.updateAccentColor());
                    this.$watch('settings.design.modal_bg_color', () => this.updateModalBgColor());
                    this.$watch('settings.design.modal_text_color', () => this.updateModalTextColor());
                    this.$watch('settings.wheel.segments', () => this.redrawWheel(), { deep: true });
                    this.$watch('settings.wheel.size', () => this.redrawWheel());
                    this.$watch('settings.wheel.text_color', () => this.redrawWheel());
                    this.$watch('settings.wheel.pointer_color', () => this.updatePointerColor());
                    this.$watch('settings.wheel.font_size', () => this.redrawWheel());
                    this.$watch('settings.template', (value) => this.applyTemplate(value));
                },

                switchTab(tabId) {
                    this.activeTab = tabId;
                    // Скрыть все панели
                    document.querySelectorAll('[data-pane]').forEach(pane => {
                        pane.style.display = 'none';
                    });
                    // Показать выбранную
                    const activePane = document.querySelector(`[data-pane="${tabId}"]`);
                    if (activePane) activePane.style.display = 'block';
                    // Обновить активный класс вкладок
                    document.querySelectorAll('[data-tab]').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    const activeTab = document.querySelector(`[data-tab="${tabId}"]`);
                    if (activeTab) activeTab.classList.add('active');
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
                    } catch (e) {
                        console.error('Error loading skin:', e);
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
                    this.renderWidget();
                },

                renderWidget() {
                    if (!this.widgetRoot || !this.rawTemplate) return;

                    let html = this.rawTemplate
                        .replace(/{id}/g, 'preview')
                        .replace(/{widget_id}/g, 'preview')
                        .replace(/{position}/g, this.settings.button.position === 'bottom-right' ? 'right' : 'left')
                        .replace(/{wheel_size}/g, this.settings.wheel.size)
                        .replace(/{title}/g, this.escapeHtml(this.settings.design.title))
                        .replace(/{description}/g, this.escapeHtml(this.settings.design.description));

                    this.widgetRoot.innerHTML = html;

                    this.widget = this.widgetRoot.querySelector('.sfw-root');
                    this.canvas = this.widget?.querySelector('#sfw-canvas-preview');
                    this.trigger = this.widget?.querySelector('.sfw-trigger');
                    this.spinBtn = this.widget?.querySelector('.sfw-spin-trigger');
                    this.titleEl = this.widget?.querySelector('.sfw-form-body h3');
                    this.descEl = this.widget?.querySelector('.sfw-form-body p');

                    this.updateTriggerColor();
                    this.updateTriggerIcon();
                    this.updateAccentColor();
                    this.updateModalBgColor();
                    this.updateModalTextColor();
                    this.updatePointerColor();
                    this.redrawWheel();
                    this.bindEvents();
                },

                updatePosition() {
                    if (!this.widget) return;
                    const position = this.settings.button.position;
                    this.widget.classList.remove('sp-position-right', 'sp-position-left');
                    this.widget.classList.add(position === 'bottom-right' ? 'sp-position-right' : 'sp-position-left');
                },

                updateTriggerColor() {
                    if (!this.trigger) return;
                    this.trigger.style.background = this.settings.button.bg_color;
                    this.trigger.style.color = this.settings.button.text_color;
                },

                updateTriggerIcon() {
                    if (!this.trigger) return;
                    const iconSpan = this.trigger.querySelector('.sfw-icon');
                    if (iconSpan) iconSpan.textContent = this.settings.button.icon;
                },

                updateSpinButtonText() {
                    if (!this.spinBtn) return;
                    this.spinBtn.textContent = this.settings.button.text;
                },

                updateTitle() {
                    if (!this.titleEl) return;
                    this.titleEl.textContent = this.settings.design.title;
                },

                updateDescription() {
                    if (!this.descEl) return;
                    this.descEl.textContent = this.settings.design.description;
                },

                updateAccentColor() {
                    if (!this.widget) return;
                    this.widget.style.setProperty('--sfw-accent', this.settings.design.accent_color);
                    if (this.spinBtn) this.spinBtn.style.background = this.settings.design.accent_color;
                },

                updateModalBgColor() {
                    if (!this.widget) return;
                    this.widget.style.setProperty('--sfw-modal-bg', this.settings.design.modal_bg_color);
                },

                updateModalTextColor() {
                    if (!this.widget) return;
                    this.widget.style.setProperty('--sfw-modal-text', this.settings.design.modal_text_color);
                },

                updatePointerColor() {
                    if (!this.widget) return;
                    this.widget.style.setProperty('--sfw-pointer', this.settings.wheel.pointer_color);
                },

                redrawWheel_OLD() {
                    if (!this.canvas) return;

                    const ctx = this.canvas.getContext('2d');
                    const segments = this.settings.wheel.segments || [];
                    const size = this.settings.wheel.size || 280;

                    this.canvas.width = size;
                    this.canvas.height = size;

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

                //
                redrawWheel() {
                    if (!this.canvas) return;

                    const ctx = this.canvas.getContext('2d');
                    const segments = this.settings.wheel.segments || [];
                    const size = this.settings.wheel.size || 280;

                    this.canvas.width = size;
                    this.canvas.height = size;

                    if (segments.length === 0) {
                        ctx.fillStyle = '#e5e7eb';
                        ctx.fillRect(0, 0, size, size);
                        return;
                    }

                    const radius = size / 2;
                    const centerX = radius;
                    const centerY = radius;
                    const arc = (2 * Math.PI) / segments.length;

                    // Включаем сглаживание для более качественной отрисовки
                    ctx.imageSmoothingEnabled = true;
                    ctx.shadowBlur = 0; // Сброс тени перед отрисовкой сегментов

                    ctx.clearRect(0, 0, size, size);

                    // 1. Рисуем внешнюю тень колеса
                    ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
                    ctx.shadowBlur = 15;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 5;

                    // 2. Рисуем сегменты с градиентами
                    segments.forEach((seg, i) => {
                        const startAngle = i * arc;
                        const endAngle = startAngle + arc;

                        // Создаем градиент для каждого сегмента
                        const gradient = ctx.createLinearGradient(
                            centerX - radius * 0.3,
                            centerY - radius * 0.3,
                            centerX + radius * 0.3,
                            centerY + radius * 0.3
                        );

                        const baseColor = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');

                        // Добавляем эффект градиента для объема
                        gradient.addColorStop(0, this.lightenColor(baseColor, 20));
                        gradient.addColorStop(0.5, baseColor);
                        gradient.addColorStop(1, this.darkenColor(baseColor, 10));

                        ctx.beginPath();
                        ctx.fillStyle = gradient;
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius - 8, startAngle, endAngle);
                        ctx.fill();

                        // Добавляем блик на каждый сегмент
                        ctx.save();
                        ctx.globalAlpha = 0.3;
                        ctx.beginPath();
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius - 8, startAngle, startAngle + arc / 2);
                        ctx.fillStyle = '#ffffff';
                        ctx.fill();
                        ctx.restore();

                        // Рисуем границу сегмента с эффектом
                        ctx.beginPath();
                        ctx.strokeStyle = '#ffffff';
                        ctx.lineWidth = 3;
                        ctx.shadowBlur = 0;
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius - 8, startAngle, endAngle);
                        ctx.lineTo(centerX, centerY);
                        ctx.stroke();

                        // Рисуем текст с тенью
                        ctx.save();
                        ctx.translate(centerX, centerY);
                        ctx.rotate(startAngle + arc / 2);
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";

                        // Тень для текста
                        ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
                        ctx.shadowBlur = 3;
                        ctx.shadowOffsetX = 1;
                        ctx.shadowOffsetY = 1;

                        ctx.fillStyle = this.settings.wheel.text_color || '#1f2937';
                        ctx.font = `bold ${Math.min(this.settings.wheel.font_size || 13, 16)}px system-ui, -apple-system, sans-serif`;

                        let label = seg.label || '';
                        if (label.length > 12) label = label.slice(0, 10) + '..';

                        // Позиционирование текста
                        const textRadius = radius - 35;
                        ctx.fillText(label, textRadius, 5);
                        ctx.restore();
                    });

                    // 3. Рисуем внутренние декоративные круги
                    ctx.shadowBlur = 0;

                    // Внутренний круг с градиентом
                    const innerGradient = ctx.createRadialGradient(centerX, centerY, 5, centerX, centerY, 35);
                    innerGradient.addColorStop(0, '#ffffff');
                    innerGradient.addColorStop(0.7, '#f8fafc');
                    innerGradient.addColorStop(1, '#e2e8f0');

                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 28, 0, 2 * Math.PI);
                    ctx.fillStyle = innerGradient;
                    ctx.fill();

                    // Тень для внутреннего круга
                    ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
                    ctx.shadowBlur = 5;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 2;

                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
                    ctx.fillStyle = '#ffffff';
                    ctx.fill();

                    // Декоративная окантовка внутреннего круга
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
                    ctx.strokeStyle = this.settings.design.accent_color || '#6366f1';
                    ctx.lineWidth = 2;
                    ctx.stroke();

                    // 4. Рисуем центральную точку
                    ctx.shadowBlur = 0;
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);

                    const centerGradient = ctx.createRadialGradient(centerX - 3, centerY - 3, 2, centerX, centerY, 10);
                    centerGradient.addColorStop(0, this.settings.design.accent_color || '#6366f1');
                    centerGradient.addColorStop(1, this.darkenColor(this.settings.design.accent_color || '#6366f1', 20));

                    ctx.fillStyle = centerGradient;
                    ctx.fill();

                    // Блик на центральной точке
                    ctx.beginPath();
                    ctx.arc(centerX - 2, centerY - 2, 2, 0, 2 * Math.PI);
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                    ctx.fill();

                    // 5. Рисуем внешнее кольцо с эффектом
                    ctx.shadowBlur = 0;
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, radius - 5, 0, 2 * Math.PI);
                    ctx.strokeStyle = this.settings.design.accent_color || '#6366f1';
                    ctx.lineWidth = 4;
                    ctx.stroke();

                    // Внешняя тень колеса
                    ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
                    ctx.shadowBlur = 20;
                    ctx.shadowOffsetX = 0;
                    ctx.shadowOffsetY = 8;

                    // Сбрасываем тень для последующих отрисовок
                    ctx.shadowBlur = 0;

                    // 6. Добавляем эффект медленного вращения по умолчанию
                    if (!this.wheelAnimationStarted) {
                        this.wheelAnimationStarted = true;
                        this.currentRotation = 0;
                        this.startIdleRotation();
                    }
                },
                // Вспомогательные методы для работы с цветами
                lightenColor(color, percent) {
                    if (!color) return '#ffffff';
                    // Упрощенная версия - для реального проекта лучше использовать полноценную функцию
                    return color;
                },

                darkenColor(color, percent) {
                    if (!color) return '#000000';
                    return color;
                },

// Медленное вращение по умолчанию
                startIdleRotation() {
                    if (this.idleRotationInterval) clearInterval(this.idleRotationInterval);

                    // Медленное вращение с очень низкой скоростью
                    let idleRotation = 0;
                    this.idleRotationInterval = setInterval(() => {
                        if (!this.canvas || this.isSpinning) return;

                        // Очень медленное вращение (0.5 градуса в секунду)
                        idleRotation += 0.5;
                        if (idleRotation >= 360) idleRotation = 0;

                        // Применяем вращение с плавным transition
                        this.canvas.style.transition = 'transform 0.1s linear';
                        this.canvas.style.transform = `rotate(${idleRotation}deg)`;
                    }, 100);
                },

// Остановка медленного вращения при начале спина
                stopIdleRotation() {
                    if (this.idleRotationInterval) {
                        clearInterval(this.idleRotationInterval);
                        this.idleRotationInterval = null;
                    }
                },


                //

                bindEvents() {
                    if (!this.widget) return;

                    const toggleBtn = this.widget.querySelector('[data-sp-toggle]');
                    if (toggleBtn) {
                        toggleBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.widget.classList.add('sp-active');
                        });
                    }

                    const closeBtns = this.widget.querySelectorAll('[data-sp-close]');
                    closeBtns.forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.widget.classList.remove('sp-active');
                        });
                    });
                },

                addCoupon() {
                    if (!this.settings.wheel.segments) this.settings.wheel.segments = [];
                    this.settings.wheel.segments.push({
                        label: 'Новый приз',
                        bg_color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0'),
                        value: 'PROMO' + Math.floor(Math.random()*1000)
                    });
                },

                removeSegment(index) {
                    this.settings.wheel.segments.splice(index, 1);
                },

                addFormField() {
                    if (!this.settings.form.fields) this.settings.form.fields = [];
                    this.settings.form.fields.push({ type: 'text', placeholder: 'Новое поле' });
                },

                removeFormField(index) {
                    this.settings.form.fields.splice(index, 1);
                },

                async applyTemplate(skinId) {
                    if (this.settings.template === skinId) return;
                    this.settings.template = skinId;
                    await this.loadSkin(skinId);
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
                }
            };
        }
    </script>
@endpush

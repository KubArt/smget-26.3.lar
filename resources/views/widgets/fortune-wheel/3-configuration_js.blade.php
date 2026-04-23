@push('js')
    <script>
        function fortuneWheelEditor(config) {
            return {
                // Данные (приходят из PHP, уже с дефолтами)
                slug: config.slug,
                settings: config.settings,  // Уже содержит все дефолты из PHP
                skins: config.skins,

                // Preview
                rawTemplate: '',
                rawCss: '',
                shadowRoot: null,
                widgetRoot: null,
                previewMode: 'desktop',

                // UI состояния
                isLoading: false,
                isSaving: false,
                activeTab: 'button-tab',

                // Состояние preview (только для демонстрации)
                isSpinning: false,
                currentRotation: 0,
                currentWonSegment: null,
                showContactForm: true,
                userContact: '',
                termsAccepted: false,
                winResult: null,

                // Инициализация
                async init() {
                    await this.loadSkin(this.settings.template);
                    this.setupWatchers();
                },

                setupWatchers() {
                    // Только watchers для preview обновления
                    this.$watch('settings.button.position', () => this.updatePosition());
                    this.$watch('settings.button.bg_color', () => this.updateTriggerColor());
                    this.$watch('settings.button.text_color', () => this.updateTriggerColor());
                    this.$watch('settings.button.icon', () => this.updateTriggerIcon());
                    this.$watch('settings.button.text', () => this.updateSpinButtonText());
                    this.$watch('settings.button.size', () => this.updateButtonSize());
                    this.$watch('settings.button.border_radius', () => this.updateButtonRadius());
                    this.$watch('settings.design.hover_effect', () => this.updateHoverEffect());
                    this.$watch('settings.design.opacity', () => this.updateOpacity());
                    this.$watch('settings.animation.type', () => this.updateAnimation());
                    this.$watch('settings.design.title', () => this.updateTitle());
                    this.$watch('settings.design.description', () => this.updateDescription());
                    this.$watch('settings.design.accent_color', () => this.updateAccentColor());
                    this.$watch('settings.design.modal_bg_color', () => this.updateModalBgColor());
                    this.$watch('settings.design.modal_text_color', () => this.updateModalTextColor());
                    this.$watch('settings.wheel.segments', () => this.redrawWheel(), { deep: true });
                    this.$watch('settings.wheel.text_color', () => this.redrawWheel());
                    this.$watch('settings.wheel.pointer_color', () => this.updatePointerColor());
                    this.$watch('settings.wheel.font_size', () => this.redrawWheel());
                    this.$watch('settings.wheel.border_color', () => this.redrawWheel());
                    this.$watch('settings.wheel.border_width', () => this.redrawWheel());
                    this.$watch('settings.template', (value) => this.applyTemplate(value));
                },

                // ============ Методы обновления preview UI ============

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

                updateButtonSize() {
                    if (!this.trigger) return;
                    const sizeMap = { small: '45px', medium: '60px', large: '75px' };
                    const size = sizeMap[this.settings.button.size] || '60px';
                    this.trigger.style.width = size;
                    this.trigger.style.height = size;
                    this.trigger.style.fontSize = this.settings.button.size === 'small' ? '20px' : (this.settings.button.size === 'large' ? '32px' : '28px');
                },

                updateButtonRadius() {
                    if (!this.trigger) return;
                    this.trigger.style.borderRadius = this.settings.button.border_radius || '50px';
                },

                updateHoverEffect() {
                    if (!this.widget) return;
                    const effects = ['lift', 'scale', 'glow', 'rotate', 'pulse', 'shake'];
                    effects.forEach(effect => {
                        this.widget.classList.remove(`sp-hover-${effect}`);
                    });
                    if (this.settings.design.hover_effect !== 'none') {
                        this.widget.classList.add(`sp-hover-${this.settings.design.hover_effect}`);
                    }
                },

                updateOpacity() {
                    if (!this.widget) return;
                    const trigger = this.widget.querySelector('.sfw-trigger');
                    if (trigger) trigger.style.opacity = this.settings.design.opacity;
                },

                updateAnimation() {
                    if (!this.widget) return;
                    const animations = ['wave', 'pulse', 'shake', 'ring', 'bounce', 'glow', 'spin', 'heartbeat', 'flash', 'swing', 'wobble', 'fade', 'rotate'];
                    animations.forEach(anim => {
                        this.widget.classList.remove(`sp-animation-${anim}`);
                    });
                    if (this.settings.animation.type !== 'none') {
                        this.widget.classList.add(`sp-animation-${this.settings.animation.type}`);
                    }
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

                // ============ Управление вкладками ============

                switchTab(tabId) {
                    this.activeTab = tabId;
                    document.querySelectorAll('[data-pane]').forEach(pane => {
                        pane.style.display = 'none';
                    });
                    const activePane = document.querySelector(`[data-pane="${tabId}"]`);
                    if (activePane) activePane.style.display = 'block';
                    document.querySelectorAll('[data-tab]').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    const activeTab = document.querySelector(`[data-tab="${tabId}"]`);
                    if (activeTab) activeTab.classList.add('active');
                },

                // ============ Загрузка скина ============

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
                        .replace(/{title}/g, this.escapeHtml(this.settings.design.title))
                        .replace(/{description}/g, this.escapeHtml(this.settings.design.description));

                    this.widgetRoot.innerHTML = html;

                    this.widget = this.widgetRoot.querySelector('.sfw-root');
                    this.canvas = this.widget?.querySelector('#sfw-canvas-preview');
                    this.trigger = this.widget?.querySelector('.sfw-trigger');
                    this.spinBtn = this.widget?.querySelector('.sfw-spin-trigger');
                    this.titleEl = this.widget?.querySelector('.sfw-form-body h3');
                    this.descEl = this.widget?.querySelector('.sfw-form-body p');

                    // Применяем все стили
                    this.updatePosition();
                    this.updateTriggerColor();
                    this.updateTriggerIcon();
                    this.updateSpinButtonText();
                    this.updateButtonSize();
                    this.updateButtonRadius();
                    this.updateHoverEffect();
                    this.updateOpacity();
                    this.updateAnimation();
                    this.updateAccentColor();
                    this.updateModalBgColor();
                    this.updateModalTextColor();
                    this.updatePointerColor();

                    this.redrawWheel();
                    this.renderContactForm();
                    this.bindEvents();
                },

                // ============ Форма контакта для preview ============

                renderContactForm() {
                    const actionsContainer = this.widget?.querySelector('.sfw-actions');
                    if (!actionsContainer) return;

                    if (!this.showContactForm) {
                        // Показываем спиннер
                        actionsContainer.innerHTML = `<div class="sfw-spinner"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Вращаем колесо...</p></div>`;
                        // Запускаем вращение
                        setTimeout(() => this.startPreviewSpin(), 100);
                        return;
                    }

                    const contactType = this.settings.form.contact_type || 'tel';
                    const placeholder = contactType === 'tel' ? '+7 (999) 123-45-67' : 'your@email.com';

                    let html = `
                            <div class="sfw-contact-form">
                                <div class="sfw-form-group">
                                    <input type="${contactType}" class="sfw-contact-input" placeholder="${placeholder}">
                                </div>
                                <div class="sfw-terms">
                                    <label class="sfw-checkbox-label">
                                        <input type="checkbox" class="sfw-terms-checkbox">
                                        <span>${this.escapeHtml(this.settings.form.terms_text || 'Я согласен с условиями розыгрыша')}</span>
                                    </label>
                                </div>
                                <div class="sfw-actions-buttons">
                                    <button class="sfw-spin-trigger">${this.escapeHtml(this.settings.button.text || 'Крутить колесо')}</button>
                                    <button class="sfw-decline-btn">Отказаться</button>
                                </div>
                            </div>
                        `;

                    actionsContainer.innerHTML = html;

                    const spinBtn = actionsContainer.querySelector('.sfw-spin-trigger');
                    const declineBtn = actionsContainer.querySelector('.sfw-decline-btn');
                    const contactInput = actionsContainer.querySelector('.sfw-contact-input');
                    const termsCheckbox = actionsContainer.querySelector('.sfw-terms-checkbox');

                    if (spinBtn) {
                        spinBtn.addEventListener('click', () => {
                            if (!contactInput?.value) {
                                alert(this.settings.messages?.fill_contact || 'Пожалуйста, укажите контактные данные');
                                return;
                            }
                            if (!termsCheckbox?.checked) {
                                alert(this.settings.messages?.accept_terms || 'Пожалуйста, согласитесь с условиями');
                                return;
                            }
                            this.userContact = contactInput.value;
                            this.termsAccepted = true;
                            this.showContactForm = false;
                            this.renderContactForm();
                        });
                    }

                    if (declineBtn) {
                        declineBtn.addEventListener('click', () => {
                            this.widget?.classList.remove('sp-active');
                            setTimeout(() => {
                                this.showContactForm = true;
                                this.renderContactForm();
                            }, 300);
                        });
                    }
                },

                // ============ Отрисовка колеса ============

                redrawWheel() {
                    if (!this.canvas) return;

                    const ctx = this.canvas.getContext('2d');
                    const segments = (this.settings.wheel.segments || []).filter(s => s.enabled !== false);
                    const size = 380;

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

                    ctx.imageSmoothingEnabled = true;
                    ctx.clearRect(0, 0, size, size);

                    segments.forEach((seg, i) => {
                        const startAngle = i * arc;
                        const endAngle = startAngle + arc;

                        const baseColor = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');

                        ctx.beginPath();
                        ctx.fillStyle = baseColor;
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius - 8, startAngle, endAngle);
                        ctx.fill();

                        // Граница
                        ctx.beginPath();
                        ctx.strokeStyle = this.settings.wheel.border_color || '#ffffff';
                        ctx.lineWidth = this.settings.wheel.border_width || 3;
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius - 8, startAngle, endAngle);
                        ctx.lineTo(centerX, centerY);
                        ctx.stroke();

                        // Текст
                        ctx.save();
                        ctx.translate(centerX, centerY);
                        ctx.rotate(startAngle + arc / 2);
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        ctx.fillStyle = this.settings.wheel.text_color || '#1f2937';
                        ctx.font = `bold ${Math.min(this.settings.wheel.font_size || 13, 16)}px system-ui`;

                        let label = seg.label || '';
                        if (label.length > 12) label = label.slice(0, 10) + '..';
                        ctx.fillText(label, radius - 35, 5);
                        ctx.restore();
                    });

                    // Внутренний круг
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
                    ctx.fillStyle = '#ffffff';
                    ctx.fill();

                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
                    ctx.strokeStyle = this.settings.design.accent_color || '#6366f1';
                    ctx.lineWidth = 2;
                    ctx.stroke();

                    // Центральная точка
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
                    ctx.fillStyle = this.settings.design.accent_color || '#6366f1';
                    ctx.fill();
                },

                // ============ Preview вращение ============

                startPreviewSpin() {
                    if (this.isSpinning) return;

                    const segments = (this.settings.wheel.segments || []).filter(s => s.enabled !== false);
                    if (segments.length === 0) {
                        alert('Добавьте призы в настройках');
                        return;
                    }

                    this.isSpinning = true;

                    // Выбираем случайный выигрыш
                    const winIndex = Math.floor(Math.random() * segments.length);
                    this.currentWonSegment = segments[winIndex];

                    const segmentDeg = 360 / segments.length;
                    const rotationNeeded = (360 - (winIndex * segmentDeg)) - (segmentDeg / 2);
                    const totalRotation = 1440 + rotationNeeded;

                    this.currentRotation += totalRotation;

                    this.canvas.style.transition = `transform ${this.settings.wheel.rotation_speed || 4}s cubic-bezier(0.25, 0.1, 0.15, 1)`;
                    this.canvas.style.transform = `rotate(${this.currentRotation}deg)`;

                    setTimeout(() => {
                        this.isSpinning = false;
                        this.showPreviewWin();
                    }, (this.settings.wheel.rotation_speed || 4) * 1000);
                },

                showPreviewWin() {
                    const actionsContainer = this.widget?.querySelector('.sfw-actions');
                    if (!actionsContainer) return;

                    const wonSegment = this.currentWonSegment;
                    const successMsg = (this.settings.form.success_message || 'Ваш купон: {CODE}').replace('{CODE}', wonSegment.value || 'PROMO2024');

                    actionsContainer.innerHTML = `
                        <div class="sfw-win-result">
                            <div class="sfw-win-icon">🎉</div>
                            <h4>${this.escapeHtml(this.settings.form.title || 'Поздравляем!')}</h4>
                            <p>Вы выиграли: <strong>${this.escapeHtml(wonSegment.label)}</strong></p>
                            <div class="sfw-coupon-code">${this.escapeHtml(wonSegment.value || 'PROMO2024')}</div>
                            <button class="sfw-close-win">Закрыть</button>
                        </div>
                    `;

                    const closeBtn = actionsContainer.querySelector('.sfw-close-win');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', () => {
                            this.widget?.classList.remove('sp-active');
                            setTimeout(() => {
                                this.currentRotation = 0;
                                if (this.canvas) {
                                    this.canvas.style.transform = 'rotate(0deg)';
                                    this.canvas.style.transition = 'none';
                                }
                                this.showContactForm = true;
                                this.renderContactForm();
                            }, 300);
                        });
                    }
                },

                // ============ События виджета ============

                bindEvents() {
                    if (!this.widget) return;

                    const toggleBtn = this.widget.querySelector('[data-sp-toggle]');
                    if (toggleBtn) {
                        const newBtn = toggleBtn.cloneNode(true);
                        toggleBtn.parentNode.replaceChild(newBtn, toggleBtn);
                        newBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.showContactForm = true;
                            this.widget.classList.add('sp-active');
                            setTimeout(() => this.renderContactForm(), 50);
                        });
                    }

                    const closeBtns = this.widget.querySelectorAll('[data-sp-close]');
                    closeBtns.forEach(btn => {
                        const newBtn = btn.cloneNode(true);
                        btn.parentNode.replaceChild(newBtn, btn);
                        newBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.widget.classList.remove('sp-active');
                        });
                    });
                },

                // ============ Управление призами ============

                addCoupon() {
                    if (!this.settings.wheel.segments) this.settings.wheel.segments = [];
                    this.settings.wheel.segments.push({
                        label: 'Новый приз',
                        bg_color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0'),
                        value: 'PROMO' + Math.floor(Math.random()*1000),
                        enabled: true,
                        expiry_days: 30,
                        description: ''
                    });
                },

                removeSegment(index) {
                    this.settings.wheel.segments.splice(index, 1);
                },

                generateUniqueCode(index) {
                    const code = 'PROMO_' + Math.random().toString(36).substring(2, 10).toUpperCase();
                    this.settings.wheel.segments[index].value = code;
                    this.showNotification('Промокод сгенерирован', 'success');
                },

                // ============ Смена шаблона ============

                async applyTemplate(skinId) {
                    if (this.settings.template === skinId) return;
                    this.settings.template = skinId;
                    await this.loadSkin(skinId);
                },

                // ============ Режим предпросмотра ============

                updatePreviewMode(mode) {
                    this.previewMode = mode;
                    this.$dispatch('preview-mode-changed', mode);
                },

                // ============ Сохранение ============

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

                // ============ Утилиты ============

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

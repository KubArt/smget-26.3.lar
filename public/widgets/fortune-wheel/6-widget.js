/**
 * Виджет "Колесо Фортуны"
 * Полная версия для встраивания на сайт
 */
window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        // Выносим настройки для удобства
        this.buttonConfig = settings.button || {};
        this.wheelConfig = settings.wheel || {};
        this.formConfig = settings.form || {};
        this.designConfig = settings.design || {};
        this.animationConfig = settings.animation || {};
        this.messages = settings.messages || {};
        this.limits = settings.limits || {};

        // Ключи для хранения
        this.storageKey = `sfw_${id}`;
        this.spinsCountKey = `sfw_${id}_spins`;

        // Состояние виджета
        this.isSpinning = false;
        this.currentRotation = 0;
        this.wonSegment = null;
        this.userContact = null;
        this.termsAccepted = false;
        this.isContactFormShown = true;

        // DOM элементы (будут заполнены в mount)
        this.canvas = null;
        this.ctx = null;
        this.actionsContainer = null;
    }

    init() {
        // Проверка лимита попыток
        if (this.isLimitReached()) {
            return;
        }
        super._init();
    }

    mount() {
        this.injectStyles();
        this.injectCSSVariables();
        this.render();
        this.initCanvas();
        this.renderContactForm();
        this.bindEvents();
        this.track('view');

        // Автооткрытие если настроено
        if (this.buttonConfig.auto_open_delay > 0) {
            setTimeout(() => this.openModal(), this.buttonConfig.auto_open_delay * 1000);
        }
    }

    /**
     * Инжекция CSS переменных
     */
    injectCSSVariables() {
        const styleId = `sfw-vars-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                .sfw-root {
                    --sfw-btn-bg: ${this.buttonConfig.bg_color || '#6366f1'};
                    --sfw-btn-text: ${this.buttonConfig.text_color || '#ffffff'};
                    --sfw-accent: ${this.designConfig.accent_color || '#6366f1'};
                    --sfw-modal-bg: ${this.designConfig.modal_bg_color || '#ffffff'};
                    --sfw-modal-text: ${this.designConfig.modal_text_color || '#1f2937'};
                    --sfw-pointer: ${this.wheelConfig.pointer_color || '#ff4444'};
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Инжекция CSS скина
     */
    injectStyles() {
        const styleId = `sfw-style-${this.id}`;
        if (!document.getElementById(styleId) && this.assets.css) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = this.assets.css;
            document.head.appendChild(style);
        }
    }

    /**
     * Рендер HTML виджета
     */
    render() {
        let html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{widget_id}/g, this.id)
            .replace(/{position}/g, this.buttonConfig.position === 'bottom-right' ? 'right' : 'left')
            .replace(/{title}/g, this.escapeHtml(this.designConfig.title || 'Выиграйте приз!'))
            .replace(/{description}/g, this.escapeHtml(this.designConfig.description || 'Испытайте свою удачу'));

        this.container = this.createContainer(html, `sfw-root sp-position-${this.buttonConfig.position === 'bottom-right' ? 'right' : 'left'}`);

        // Применяем стили к кнопке
        const trigger = this.container.querySelector('.sfw-trigger');
        if (trigger) {
            const sizeMap = { small: '45px', medium: '60px', large: '75px' };
            const size = sizeMap[this.buttonConfig.size] || '60px';
            trigger.style.width = size;
            trigger.style.height = size;
            trigger.style.fontSize = this.buttonConfig.size === 'small' ? '20px' : (this.buttonConfig.size === 'large' ? '32px' : '28px');
            trigger.style.borderRadius = this.buttonConfig.border_radius || '50px';
            trigger.style.background = this.buttonConfig.bg_color;
            trigger.style.color = this.buttonConfig.text_color;

            const iconSpan = trigger.querySelector('.sfw-icon');
            if (iconSpan) iconSpan.textContent = this.buttonConfig.icon || '🎡';
        }

        // Применяем эффекты к корню
        if (this.designConfig.hover_effect && this.designConfig.hover_effect !== 'none') {
            this.container.classList.add(`sp-hover-${this.designConfig.hover_effect}`);
        }
        if (this.animationConfig.type && this.animationConfig.type !== 'none') {
            this.container.classList.add(`sp-animation-${this.animationConfig.type}`);
        }
        if (this.designConfig.opacity) {
            const trigger = this.container.querySelector('.sfw-trigger');
            if (trigger) trigger.style.opacity = this.designConfig.opacity;
        }
    }

    /**
     * Инициализация Canvas
     */
    initCanvas() {
        this.canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.drawWheel();
    }

    /**
     * Отрисовка колеса
     */
    drawWheel() {
        const segments = (this.wheelConfig.segments || []).filter(s => s.enabled !== false);
        const size = 300;

        this.canvas.width = size;
        this.canvas.height = size;

        if (segments.length === 0) {
            this.ctx.fillStyle = '#e5e7eb';
            this.ctx.fillRect(0, 0, size, size);
            return;
        }

        const radius = size / 2;
        const centerX = radius;
        const centerY = radius;
        const arc = (2 * Math.PI) / segments.length;

        this.ctx.clearRect(0, 0, size, size);

        segments.forEach((seg, i) => {
            const startAngle = i * arc;
            const endAngle = startAngle + arc;

            // Сегмент
            this.ctx.beginPath();
            this.ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            this.ctx.fill();

            // Граница
            this.ctx.beginPath();
            this.ctx.strokeStyle = this.wheelConfig.border_color || '#ffffff';
            this.ctx.lineWidth = this.wheelConfig.border_width || 3;
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            this.ctx.lineTo(centerX, centerY);
            this.ctx.stroke();

            // Текст
            this.ctx.save();
            this.ctx.translate(centerX, centerY);
            this.ctx.rotate(startAngle + arc / 2);
            this.ctx.textAlign = "center";
            this.ctx.textBaseline = "middle";
            this.ctx.fillStyle = this.wheelConfig.text_color || '#333';
            this.ctx.font = `bold ${Math.min(this.wheelConfig.font_size || 13, 16)}px system-ui`;

            let label = seg.label || '';
            if (label.length > 12) label = label.slice(0, 10) + '..';
            this.ctx.fillText(label, radius - 35, 5);
            this.ctx.restore();
        });

        // Внутренний круг
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fill();

        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
        this.ctx.strokeStyle = this.designConfig.accent_color || '#6366f1';
        this.ctx.lineWidth = 2;
        this.ctx.stroke();

        // Центральная точка
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
        this.ctx.fillStyle = this.designConfig.accent_color || '#6366f1';
        this.ctx.fill();
    }

    /**
     * Рендер формы контакта
     */
    renderContactForm() {
        this.actionsContainer = this.container.querySelector('.sfw-actions');
        if (!this.actionsContainer) return;

        if (!this.isContactFormShown) {
            this.actionsContainer.innerHTML = `<div class="sfw-spinner"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Вращаем колесо...</p></div>`;
            return;
        }

        const contactType = this.formConfig.contact_type || 'tel';
        const placeholder = contactType === 'tel' ? '+7 (999) 123-45-67' : 'your@email.com';

        this.actionsContainer.innerHTML = `
            <div class="sfw-contact-form">
                <div class="sfw-form-group">
                    <input type="${contactType}" class="sfw-contact-input" placeholder="${this.escapeHtml(placeholder)}">
                </div>
                <div class="sfw-terms">
                    <label class="sfw-checkbox-label">
                        <input type="checkbox" class="sfw-terms-checkbox">
                        <span>${this.escapeHtml(this.formConfig.terms_text || 'Я согласен с условиями розыгрыша')}</span>
                    </label>
                </div>
                <div class="sfw-actions-buttons">
                    <button class="sfw-spin-trigger">${this.escapeHtml(this.buttonConfig.text || 'Крутить колесо')}</button>
                    <button class="sfw-decline-btn">Отказаться</button>
                </div>
            </div>
        `;

        this.bindFormEvents();
    }

    /**
     * Привязка событий формы
     */
    bindFormEvents() {
        const spinBtn = this.actionsContainer.querySelector('.sfw-spin-trigger');
        const declineBtn = this.actionsContainer.querySelector('.sfw-decline-btn');
        const contactInput = this.actionsContainer.querySelector('.sfw-contact-input');
        const termsCheckbox = this.actionsContainer.querySelector('.sfw-terms-checkbox');

        if (spinBtn) {
            spinBtn.onclick = () => {
                if (!contactInput?.value) {
                    alert(this.messages.fill_contact || 'Пожалуйста, укажите контактные данные');
                    return;
                }
                if (!termsCheckbox?.checked) {
                    alert(this.messages.accept_terms || 'Пожалуйста, согласитесь с условиями');
                    return;
                }
                this.userContact = contactInput.value;
                this.termsAccepted = true;
                this.isContactFormShown = false;
                this.renderContactForm();
                this.startSpin();
            };
        }

        if (declineBtn) {
            declineBtn.onclick = () => {
                this.closeModal();
                this.track('decline');
            };
        }
    }

    /**
     * Запуск вращения
     */
    startSpin() {
        if (this.isSpinning) return;

        const segments = (this.wheelConfig.segments || []).filter(s => s.enabled !== false);
        if (segments.length === 0) return;

        this.isSpinning = true;

        // Выбор случайного выигрыша с учетом веса
        const winIndex = this.getRandomSegmentIndex(segments);
        this.wonSegment = segments[winIndex];

        // Расчет угла остановки
        const segmentDeg = 360 / segments.length;
        const rotationNeeded = (360 - (winIndex * segmentDeg)) - (segmentDeg / 2);
        const totalRotation = 1440 + rotationNeeded;

        this.currentRotation += totalRotation;

        this.canvas.style.transition = `transform ${this.wheelConfig.rotation_speed || 4}s cubic-bezier(0.25, 0.1, 0.15, 1)`;
        this.canvas.style.transform = `rotate(${this.currentRotation}deg)`;

        this.track('spin_start');

        setTimeout(() => this.onSpinEnd(), (this.wheelConfig.rotation_speed || 4) * 1000);
    }

    /**
     * Выбор случайного сегмента с учетом веса
     */
    getRandomSegmentIndex(segments) {
        // TODO: добавить поддержку веса призов
        return Math.floor(Math.random() * segments.length);
    }

    /**
     * Завершение вращения
     */
    onSpinEnd() {
        this.isSpinning = false;
        this.track('spin_win');
        this.saveSpin();
        this.showWinResult();
    }

    /**
     * Показ результата выигрыша
     */
    showWinResult() {
        if (!this.actionsContainer) return;

        const successMsg = (this.formConfig.success_message || 'Ваш купон: {CODE}').replace('{CODE}', this.wonSegment.value || 'PROMO2024');

        this.actionsContainer.innerHTML = `
            <div class="sfw-win-result">
                <div class="sfw-win-icon">🎉</div>
                <h4>${this.escapeHtml(this.formConfig.title || 'Поздравляем!')}</h4>
                <p>Вы выиграли: <strong>${this.escapeHtml(this.wonSegment.label)}</strong></p>
                <div class="sfw-coupon-code">${this.escapeHtml(this.wonSegment.value || 'PROMO2024')}</div>
                <button class="sfw-close-win">Закрыть</button>
            </div>
        `;

        const closeBtn = this.actionsContainer.querySelector('.sfw-close-win');
        if (closeBtn) {
            closeBtn.onclick = () => {
                this.closeModal();
                this.resetWheel();
            };
        }

        // Отправка данных на webhook
        this.sendLead();
    }

    /**
     * Отправка лида на webhook
     */
    sendLead() {
        const webhookUrl = this.formConfig.webhook_url;
        if (!webhookUrl) return;

        const data = {
            widget_id: this.id,
            contact: this.userContact,
            prize: this.wonSegment.label,
            code: this.wonSegment.value,
            timestamp: new Date().toISOString()
        };

        fetch(webhookUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        }).catch(e => console.error('Webhook error:', e));
    }

    /**
     * Сброс колеса
     */
    resetWheel() {
        this.currentRotation = 0;
        if (this.canvas) {
            this.canvas.style.transform = 'rotate(0deg)';
            this.canvas.style.transition = 'none';
        }
        this.isContactFormShown = true;
        this.userContact = null;
        this.termsAccepted = false;
        this.renderContactForm();
    }

    /**
     * Проверка лимита попыток
     */
    isLimitReached() {
        const spinsPerUser = this.limits.spins_per_user || 0;
        if (spinsPerUser === 0) return false;

        const spins = parseInt(localStorage.getItem(this.spinsCountKey) || '0');
        return spins >= spinsPerUser;
    }

    /**
     * Сохранение попытки
     */
    saveSpin() {
        const spinsPerUser = this.limits.spins_per_user || 0;
        if (spinsPerUser === 0) return;

        const currentSpins = parseInt(localStorage.getItem(this.spinsCountKey) || '0');
        localStorage.setItem(this.spinsCountKey, (currentSpins + 1).toString());
    }

    /**
     * Привязка событий виджета
     */
    bindEvents() {
        // Кнопка открытия
        const toggleBtn = this.container.querySelector('[data-sp-toggle]');
        if (toggleBtn) {
            toggleBtn.onclick = () => this.openModal();
        }

        // Кнопки закрытия
        const closeBtns = this.container.querySelectorAll('[data-sp-close]');
        closeBtns.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                this.closeModal();
            };
        });

        // Закрытие по оверлею
        const overlay = this.container.querySelector('.sfw-overlay');
        if (overlay) {
            overlay.onclick = (e) => {
                if (e.target === overlay) this.closeModal();
            };
        }
    }

    /**
     * Открытие модального окна
     */
    openModal() {
        if (this.isLimitReached()) {
            alert(this.messages.spin_limit_reached || 'Вы уже использовали все попытки');
            return;
        }
        this.container.classList.add('sp-active');
        this.track('open');
    }

    /**
     * Закрытие модального окна
     */
    closeModal() {
        this.container.classList.remove('sp-active');
    }

    /**
     * HEX в RGB
     */
    hexToRgb(hex) {
        hex = hex.replace(/^#/, '');
        if (hex.length === 3) {
            hex = hex.split('').map(c => c + c).join('');
        }
        const intVal = parseInt(hex, 16);
        return {
            r: (intVal >> 16) & 255,
            g: (intVal >> 8) & 255,
            b: intVal & 255
        };
    }

    /**
     * Экранирование HTML
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

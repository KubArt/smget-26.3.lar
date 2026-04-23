/**
 * Виджет "Колесо Фортуны"
 * Оптимизированная версия с серверным выбором приза
 */
window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        // Настройки
        this.buttonConfig = settings.button || {};
        this.wheelConfig = settings.wheel || {};
        this.formConfig = settings.form || {};
        this.designConfig = settings.design || {};
        this.animationConfig = settings.animation || {};
        this.messages = settings.messages || {};
        this.limits = settings.limits || {};

        // API настройки
        this.apiUrl = settings.api_url || 'http://smget-26.3.lar/api/v1/capture/fortune-wheel';
        this.widgetId = this.id;

        // Состояние виджета
        this.isSpinning = false;
        this.currentRotation = 0;
        this.userContact = null;
        this.prizeData = null;
        this.targetRotation = 0;

        // DOM элементы
        this.canvas = null;
        this.ctx = null;
    }

    init() {
        if (this.isLimitReached()) return;
        super._init();
    }

    mount() {
        this.injectStyles();
        this.injectCSSVariables();
        this.render();
        this.initStates();
        this.initCanvas();
        this.bindEvents();
        this.track('view');

        if (this.buttonConfig.auto_open_delay > 0) {
            setTimeout(() => this.openModal(), this.buttonConfig.auto_open_delay * 1000);
        }
    }

    // ========== СТИЛИ ==========
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

    injectStyles() {
        const styleId = `sfw-style-${this.id}`;
        if (!document.getElementById(styleId) && this.assets.css) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = this.assets.css;
            document.head.appendChild(style);
        }
    }

    // ========== ОТРИСОВКА ==========
    render() {
        let html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{widget_id}/g, this.id)
            .replace(/{position}/g, this.buttonConfig.position === 'bottom-right' ? 'right' : 'left')
            .replace(/{title}/g, this.escapeHtml(this.designConfig.title || 'Выиграйте приз!'))
            .replace(/{description}/g, this.escapeHtml(this.designConfig.description || 'Испытайте свою удачу'))
            .replace(/{contact_type}/g, this.formConfig.contact_type || 'tel')
            .replace(/{contact_placeholder}/g, this.formConfig.contact_type === 'tel' ? '+7 (999) 123-45-67' : 'your@email.com')
            .replace(/{terms_text}/g, this.escapeHtml(this.formConfig.terms_text || 'Я согласен с условиями розыгрыша'))
            .replace(/{spin_button_text}/g, this.escapeHtml(this.buttonConfig.text || 'Крутить колесо'))
            .replace(/{decline_text}/g, this.escapeHtml(this.formConfig.decline_text || 'Отказаться'))
            .replace(/{win_title}/g, this.escapeHtml(this.formConfig.title || 'Поздравляем!'));

        this.container = this.createContainer(html, `sfw-root sp-position-${this.buttonConfig.position === 'bottom-right' ? 'right' : 'left'}`);

        // Стилизация кнопки
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

        // Эффекты
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

    initStates() {
        const contactInput = this.container.querySelector('.sfw-contact-input');
        if (contactInput) {
            const contactType = this.formConfig.contact_type || 'tel';
            contactInput.type = contactType;
            contactInput.placeholder = contactType === 'tel' ? '+7 (999) 123-45-67' : 'your@email.com';
            contactInput.name = contactType === 'tel' ? 'phone' : 'email';
        }

        const termsSpan = this.container.querySelector('.sfw-terms-checkbox + span');
        if (termsSpan) {
            termsSpan.textContent = this.formConfig.terms_text || 'Я согласен с условиями розыгрыша';
        }

        const spinBtn = this.container.querySelector('.sfw-state-contact .sfw-spin-trigger');
        if (spinBtn) {
            spinBtn.textContent = this.buttonConfig.text || 'Крутить колесо';
        }

        this.setState('contact');
    }

    setState(state) {
        const states = {
            contact: this.container?.querySelector('.sfw-state-contact'),
            spinner: this.container?.querySelector('.sfw-state-spinner'),
            result: this.container?.querySelector('.sfw-state-result')
        };

        Object.values(states).forEach(el => { if (el) el.style.display = 'none'; });
        if (states[state]) states[state].style.display = 'block';
    }

    // ========== CANVAS КОЛЕСО ==========
    initCanvas() {
        this.canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.drawWheel();
    }

    drawWheel() {
        const segments = (this.wheelConfig.segments || []).filter(s => s.enabled !== false);
        const size = 380;

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

            // Заливка
            this.ctx.beginPath();
            this.ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            this.ctx.fill();

            // Границы
            this.ctx.beginPath();
            this.ctx.strokeStyle = this.wheelConfig.border_color || '#ffffff';
            this.ctx.lineWidth = this.wheelConfig.border_width || 3;
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            this.ctx.lineTo(centerX, centerY);
            this.ctx.stroke();

            // Текст
            this.drawTextOnSegment(seg.label, centerX, centerY, radius, startAngle, endAngle);
        });

        // Внешняя обводка
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, radius - 10, 0, 2 * Math.PI);
        this.ctx.strokeStyle = this.wheelConfig.border_color || '#ffffff';
        this.ctx.lineWidth = this.wheelConfig.outer_border_width || 9;
        this.ctx.stroke();

        // Центр
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fill();

        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
        this.ctx.fillStyle = this.designConfig.accent_color || '#6366f1';
        this.ctx.fill();
    }

    drawTextOnSegment(text, centerX, centerY, radius, startAngle, endAngle) {
        if (!text) return;

        const angle = startAngle + (endAngle - startAngle) / 2;
        const textRadius = radius - 75;
        const lines = this.wrapText(text, 10);
        const fontSize = this.calculateFontSize(lines, radius);

        this.ctx.save();
        this.ctx.translate(centerX, centerY);
        this.ctx.rotate(angle);
        this.ctx.textAlign = "center";
        this.ctx.textBaseline = "middle";
        this.ctx.fillStyle = this.wheelConfig.text_color || '#1f2937';
        this.ctx.font = `bold ${fontSize}px system-ui`;

        const lineHeight = fontSize * 1.2;
        const startY = -((lines.length - 1) * lineHeight) / 2;

        lines.forEach((line, i) => {
            this.ctx.fillText(line, textRadius, startY + (i * lineHeight));
        });

        this.ctx.restore();
    }

    wrapText(text, maxCharsPerLine) {
        const words = text.split(' ');
        const lines = [];
        let currentLine = '';

        for (let word of words) {
            if ((currentLine + ' ' + word).trim().length <= maxCharsPerLine) {
                currentLine = currentLine ? currentLine + ' ' + word : word;
            } else {
                if (currentLine) lines.push(currentLine);
                currentLine = word;
            }
        }
        if (currentLine) lines.push(currentLine);

        if (lines.length === 1 && lines[0].length > maxCharsPerLine) {
            return [lines[0].slice(0, maxCharsPerLine - 2) + '..'];
        }

        return lines;
    }

    calculateFontSize(lines, radius) {
        const baseSize = Math.min(24, radius / 11);
        if (lines.length === 1) return baseSize;
        if (lines.length === 2) return baseSize - 2;
        return baseSize - 4;
    }

    // ========== ОСНОВНАЯ ЛОГИКА ==========
    async sendLead() {
        const contact = this.userContact;
        if (!contact) {
            this.showError('Ошибка получения контакта');
            return;
        }

        this.setState('spinner');

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Api-Key': 'widget',
                },
                body: JSON.stringify({
                    widget_id: this.widgetId,
                    contact: contact,
                    name: contact,
                    page_url: window.location.href,
                })
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                console.log(data);
                // Сохраняем индекс выигрышного сегмента
                //this.targetIndex = data.widget_data?.target_index ?? 0;
                this.targetIndex = data.target_index ?? 0;
                this.prizeData = data.widget_data?.prize || data.prize;
                this.winMessage = data.widget_data?.message || data.message;

                // Запускаем вращение к выигрышному сегменту
                this.startSpinToPrize();
            } else {
                this.handleApiError(data);
            }
        } catch (error) {
            console.error('API error:', error);
            this.showError('Ошибка соединения. Пожалуйста, попробуйте позже.');
        }
    }

    /**
     * Расчет угла остановки по индексу сегмента
     * @param {number} segmentIndex - индекс выигрышного сегмента (0-based)
     * @returns {number} угол остановки в градусах
     */
    calculateTargetRotation(segmentIndex) {
        const segments = (this.wheelConfig.segments || []).filter(s => s.enabled !== false);
        const segmentCount = segments.length;

        if (segmentCount === 0) return 0;

        const segmentDeg = 360 / segmentCount;
        const pointerAngle = 0; // указатель справа

        // Середина выигрышного сегмента (в градусах от 12 часов)
        const segmentMiddle = (segmentIndex * segmentDeg) + (segmentDeg / 2);

        // Расчет угла для вращения ПО ЧАСОВОЙ СТРЕЛКЕ
        // Нужно повернуть так, чтобы середина сегмента оказалась под указателем
        // Направление: противоположное
        let targetRotation = -(segmentMiddle - pointerAngle);

        // Нормализация
        targetRotation = ((targetRotation % 360) + 360) % 360;

        console.log('Segment middle:', segmentMiddle);
        console.log('Target rotation (positive = clockwise):', targetRotation);

        return 1800 + targetRotation;
    }

    startSpinToPrize() {
        if (this.isSpinning) return;

        // Останавливаем медленное вращение
        this.canvas.classList.remove('sfw-idle-spin');

        // Сбрасываем в 0 (ОДНО присваивание)
        this.canvas.style.transition = 'none';
        this.canvas.style.transform = 'rotate(0deg)';
        this.currentRotation = 0;

        // Форсируем перерисовку
        void this.canvas.offsetHeight;

        this.isSpinning = true;

        // Рассчитываем угол остановки по индексу
        const totalRotation = this.calculateTargetRotation(this.targetIndex);
        this.currentRotation = totalRotation;

        // Применяем анимацию (ОДНО присваивание)
        this.canvas.style.transition = `transform ${this.wheelConfig.rotation_speed || 6}s cubic-bezier(0.25, 0.1, 0.15, 1)`;
        this.canvas.style.transform = `rotate(${this.currentRotation}deg)`;

        this.track('spin_start');

        setTimeout(() => {
            this.isSpinning = false;
            this.track('spin_win');
            this.saveSpin();
            this.showWinResult();
        }, (this.wheelConfig.rotation_speed || 4) * 1000);
    }

    showWinResult() {
        const labelEl = this.container?.querySelector('.sfw-win-label');
        const codeEl = this.container?.querySelector('.sfw-win-code');
        const messageEl = this.container?.querySelector('.sfw-win-message');
        const expiresEl = this.container?.querySelector('.sfw-win-expires');

        if (labelEl) labelEl.textContent = this.prizeData?.name || 'Приз';
        if (codeEl) codeEl.textContent = this.prizeData?.code;
        if (messageEl) messageEl.textContent = this.winMessage || this.formConfig.success_message;
        if (expiresEl && this.prizeData?.expires_at) {
            const date = new Date(this.prizeData.expires_at);
            expiresEl.textContent = `Действителен до: ${date.toLocaleDateString()}`;
            expiresEl.style.display = 'block';
        }

        this.setState('result');
        this.track('prize_received');
    }

    handleApiError(data) {
        const errorCode = data.code;
        let errorMessage = this.messages.default_error || 'Ошибка получения приза';

        switch (errorCode) {
            case 'MAX_ATTEMPTS_REACHED':
                const attempts = data.attempts_used || 0;
                const limit = data.attempts_limit || 3;
                errorMessage = `Вы уже использовали ${attempts} из ${limit} попыток`;
                break;
            case 'CONTACT_REQUIRED':
                errorMessage = this.messages.fill_contact || 'Укажите контактные данные';
                break;
            default:
                errorMessage = data.error || this.messages.default_error;
        }

        this.showError(errorMessage);

        setTimeout(() => {
            this.setState('contact');
            this.resetWheel();
        }, 2000);
    }

    showError(message) {
        const actionsContainer = this.container?.querySelector('.sfw-actions');
        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <div class="sfw-error-result">
                    <div class="sfw-error-icon">⚠️</div>
                    <p>${this.escapeHtml(message)}</p>
                    <button class="sfw-close-error">Закрыть</button>
                </div>
            `;

            const closeBtn = actionsContainer.querySelector('.sfw-close-error');
            if (closeBtn) {
                closeBtn.onclick = () => {
                    this.closeModal();
                    this.resetWheel();
                };
            }
        }
    }

    resetWheel() {
        this.currentRotation = 0;
        if (this.canvas) {
            this.canvas.style.transform = 'rotate(0deg)';
            this.canvas.style.transition = 'none';
        }
        this.userContact = null;
        this.prizeData = null;
        this.targetRotation = 0;

        // Запускаем медленное вращение
        this.canvas.classList.add('sfw-idle-spin');
    }

    // ========== ЛИМИТЫ ==========
    isLimitReached() {
        const spinsPerUser = this.limits.spins_per_user || 0;
        if (spinsPerUser === 0) return false;
        const spins = parseInt(localStorage.getItem(`sfw_${this.id}_spins`) || '0');
        return spins >= spinsPerUser;
    }

    saveSpin() {
        const spinsPerUser = this.limits.spins_per_user || 0;
        if (spinsPerUser === 0) return;
        const currentSpins = parseInt(localStorage.getItem(`sfw_${this.id}_spins`) || '0');
//        localStorage.setItem(`sfw_${this.id}_spins`, (currentSpins + 1).toString());
    }

    // ========== СОБЫТИЯ ==========
    bindEvents() {
        // Открытие
        const toggleBtn = this.container.querySelector('[data-sp-toggle]');
        if (toggleBtn) toggleBtn.onclick = () => this.openModal();

        // Закрытие
        this.container.querySelectorAll('[data-sp-close]').forEach(btn => {
            btn.onclick = (e) => { e.preventDefault(); this.closeModal(); };
        });

        // Оверлей
        const overlay = this.container.querySelector('.sfw-overlay');
        if (overlay) {
            overlay.onclick = (e) => { if (e.target === overlay) this.closeModal(); };
        }

        // Кнопка спина
        const spinBtn = this.container.querySelector('.sfw-state-contact .sfw-spin-trigger');
        if (spinBtn) {
            spinBtn.onclick = () => {
                const contactInput = this.container.querySelector('.sfw-contact-input');
                const termsCheckbox = this.container.querySelector('.sfw-terms-checkbox');

                if (!contactInput?.value) {
                    this.showFieldError(contactInput);
                    return;
                }
                if (!termsCheckbox?.checked) {
                    this.showFieldError(termsCheckbox);
                    return;
                }

                this.userContact = contactInput.value;
                this.sendLead();
            };
        }

        // Отказ
        const declineBtn = this.container.querySelector('.sfw-state-contact .sfw-decline-btn');
        if (declineBtn) {
            declineBtn.onclick = () => {
                this.closeModal();
                this.track('decline');
            };
        }

        // Закрытие результата
        const closeWinBtn = this.container.querySelector('.sfw-state-result .sfw-close-win');
        if (closeWinBtn) {
            closeWinBtn.onclick = () => {
                this.closeModal();
                this.resetWheel();
                this.setState('contact');
            };
        }
    }

    showFieldError(element) {
        if (!element) return;

        if (element.type === 'checkbox') {
            const parent = element.closest('.sfw-terms');
            if (parent) parent.classList.add('sfw-error');
            element.classList.add('sfw-error');
        } else {
            element.classList.add('sfw-error');
            element.style.borderColor = '#ef4444';
            element.style.backgroundColor = '#fef2f2';
        }

        element.style.animation = 'sfwShake 0.3s ease';
        setTimeout(() => { element.style.animation = ''; }, 300);

        const removeError = () => {
            element.classList.remove('sfw-error');
            if (element.type === 'checkbox') {
                const parent = element.closest('.sfw-terms');
                if (parent) parent.classList.remove('sfw-error');
            } else {
                element.style.borderColor = '';
                element.style.backgroundColor = '';
            }
            element.removeEventListener('focus', removeError);
            element.removeEventListener('click', removeError);
        };

        element.addEventListener('focus', removeError, { once: true });
        element.addEventListener('click', removeError, { once: true });
    }

    openModal() {
        if (this.isLimitReached()) {
            alert(this.messages.spin_limit_reached || 'Вы уже использовали все попытки');
            return;
        }
        this.container.classList.add('sp-active');
        this.track('open');
    }

    closeModal() {
        this.container.classList.remove('sp-active');
    }
};

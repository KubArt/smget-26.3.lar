/**
 * Виджет "Колесо Фортуны"
 * Интегрирован с системой конфигурации Blade
 */
window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        // Дефолтные настройки
        this.defaults = {
            button: {
                position: 'bottom-right',
                text: 'Крутить колесо',
                icon: '🎡',
                bg_color: '#6366f1',
                text_color: '#ffffff',
                size: 'medium',
                border_radius: '50px',
                show_on_load: true,
                auto_open_delay: 0,
                custom_position: { x: 20, y: 20 }
            },
            wheel: {
                size: 300,
                rotation_speed: 4,
                text_color: '#333333',
                pointer_color: '#ff4444',
                background_color: '#ffffff',
                border_color: '#ffffff',
                border_width: 3,
                font_size: 13,
                segments: []
            },
            design: {
                modal_bg_color: '#ffffff',
                modal_text_color: '#1f2937',
                accent_color: '#6366f1',
                title: 'Выиграйте приз!',
                description: 'Испытайте свою удачу прямо сейчас'
            },
            form: {
                enabled: true,
                title: 'Поздравляем!',
                subtitle: 'Введите данные для получения приза',
                button_text: 'Получить приз',
                success_message: 'Ваш купон: {CODE}',
                webhook_url: '',
                fields: [
                    { type: 'tel', placeholder: '+7 (999) 123-45-67', required: true, label: 'Телефон' }
                ]
            },
            limits: {
                spins_per_user: 1,
                spins_per_day: 0,
                spins_total: 0,
                frequency: 'once_session',
                require_auth: false
            },
            messages: {
                spin_limit_reached: 'Вы уже использовали все попытки',
                fill_contact: 'Пожалуйста, укажите контактные данные',
                accept_terms: 'Пожалуйста, согласитесь с условиями',
                reject_prize: 'Вы отказались от приза. Жаль! Возвращайтесь еще!'
            },
            trigger_type: 'click',
            delay: 3,
            scroll_percent: 50,
            frequency: 'always',
            close_behavior: 'hide_session',
            template: 'default'
        };

        // Глубокое слияние настроек
        this.settings = this.mergeDefaults(settings, this.defaults);

        this.activeClass = 'sp-active';
        this.isSpinning = false;
        this.isContactSubmitted = false;
        this.storageKey = `sm_fortune_${this.id}`;
        this.currentRotation = 0;
        this.wonSegment = null;
        this.userContact = null;
    }

    /**
     * Точка входа
     */
    init() {
        super._init();
        //this.mount();
        /*
        if (this.shouldHide()) return;
        const delay = (this.settings.button.auto_open_delay || 0) * 1000;
        if (this.settings.trigger_type === 'scroll') {
            this.initScrollTrigger(this.settings.scroll_percent || 50, delay);
        } else if (this.settings.trigger_type === 'time') {
            setTimeout(() => this.mount(), (this.settings.delay || 3) * 1000);
            if (delay > 0) setTimeout(() => this.openModal(), delay);
        } else if (this.settings.trigger_type === 'exit') {
            this.initExitTrigger(delay);
        } else {
            this.mount();
            if (delay > 0) setTimeout(() => this.openModal(), delay);
        }
        //*/
    }

    initExitTrigger(delay) {
        const handleExit = (e) => {
            if (e.clientY <= 0) {
                this.mount();
                if (delay > 0) setTimeout(() => this.openModal(), delay);
                document.removeEventListener('mouseleave', handleExit);
            }
        };
        document.addEventListener('mouseleave', handleExit);
    }

    mount() {
        console.log("Условия выполнены! Рисуем Колесо Фортуны...");
        const design = this.settings.design || {};

        // Инжекция стилей
        const styleId = `sfw-style-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                .sfw-root {
                    --sfw-btn-bg: ${this.settings.button.bg_color};
                    --sfw-btn-text: ${this.settings.button.text_color};
                    --sfw-accent: ${design.accent_color || '#6366f1'};
                    --sfw-modal-bg: ${design.modal_bg_color || '#ffffff'};
                    --sfw-modal-text: ${design.modal_text_color || '#1f2937'};
                    --sfw-pointer: ${this.settings.wheel.pointer_color};
                }
                ${this.assets.css}
            `;
            document.head.appendChild(style);
        }

        // Подготовка HTML
        let html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{position}/g, this.settings.button.position === 'bottom-right' ? 'right' : 'left')
            .replace(/{title}/g, this.escapeHtml(design.title))
            .replace(/{description}/g, this.escapeHtml(design.description));

        const positionClass = `sp-position-${this.settings.button.position === 'bottom-right' ? 'right' : 'left'}`;
        this.container = this.createContainer(html, `sfw-root ${positionClass}`);

        // Применяем размер кнопки
        const trigger = this.container.querySelector('.sfw-trigger');
        if (trigger) {
            const sizeMap = { small: '45px', medium: '60px', large: '75px' };
            const size = sizeMap[this.settings.button.size] || '60px';
            trigger.style.width = size;
            trigger.style.height = size;
            trigger.style.fontSize = this.settings.button.size === 'small' ? '20px' : (this.settings.button.size === 'large' ? '32px' : '28px');
            trigger.style.borderRadius = this.settings.button.border_radius || '50px';
        }

        this.initCanvas();
        this.renderContactForm();
        this.bindEvents();

        if (this.settings.button.auto_open_delay > 0) {
            setTimeout(() => this.openModal(), this.settings.button.auto_open_delay * 1000);
        }
    }

    initCanvas() {
        this.canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!this.canvas) return;

        const size = this.settings.wheel.size || 300;
        this.canvas.width = size;
        this.canvas.height = size;

        this.ctx = this.canvas.getContext('2d');
        this.drawWheel();

        this.canvas.style.width = `${size}px`;
        this.canvas.style.height = `${size}px`;
        this.canvas.style.filter = 'drop-shadow(0 10px 15px rgba(0,0,0,0.15))';
    }

    drawWheel() {
        if (!this.ctx) return;

        const segments = this.settings.wheel.segments || [];
        const size = this.settings.wheel.size || 300;

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

            const gradient = this.ctx.createLinearGradient(
                centerX - radius * 0.3, centerY - radius * 0.3,
                centerX + radius * 0.3, centerY + radius * 0.3
            );
            const baseColor = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            gradient.addColorStop(0, this.lightenColor(baseColor, 20));
            gradient.addColorStop(1, this.darkenColor(baseColor, 10));

            this.ctx.beginPath();
            this.ctx.fillStyle = gradient;
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            this.ctx.fill();

            this.ctx.beginPath();
            this.ctx.strokeStyle = this.settings.wheel.border_color || '#ffffff';
            this.ctx.lineWidth = this.settings.wheel.border_width || 3;
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            this.ctx.lineTo(centerX, centerY);
            this.ctx.stroke();

            this.ctx.save();
            this.ctx.translate(centerX, centerY);
            this.ctx.rotate(startAngle + arc / 2);
            this.ctx.textAlign = "center";
            this.ctx.textBaseline = "middle";
            this.ctx.fillStyle = this.settings.wheel.text_color || '#333';
            this.ctx.font = `bold ${Math.min(this.settings.wheel.font_size || 13, 16)}px system-ui`;

            let label = seg.label || '';
            if (label.length > 12) label = label.slice(0, 10) + '..';
            this.ctx.fillText(label, radius - 45, 5);
            this.ctx.restore();
        });

        // Центральный круг
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 28, 0, 2 * Math.PI);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fill();

        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
        this.ctx.strokeStyle = this.settings.design.accent_color || '#6366f1';
        this.ctx.lineWidth = 3;
        this.ctx.stroke();

        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
        this.ctx.fillStyle = this.settings.design.accent_color || '#6366f1';
        this.ctx.fill();
    }

    renderContactForm() {
        const actionsContainer = this.container.querySelector('.sfw-actions');
        if (!actionsContainer) return;

        if (!this.settings.form.enabled || this.isContactSubmitted) {
            actionsContainer.innerHTML = `<button class="sfw-spin-trigger">${this.escapeHtml(this.settings.button.text || 'Крутить колесо')}</button>`;
            return;
        }

        // Рендерим форму контакта
        let html = `<div class="sfw-contact-form">`;

        // Только одно поле (телефон или email)
        const field = this.settings.form.fields[0] || { type: 'tel', placeholder: '+7 (999) 123-45-67' };
        html += `
            <div class="sfw-form-group">
                <input type="${field.type}"
                       class="sfw-contact-input"
                       placeholder="${this.escapeHtml(field.placeholder)}"
                       data-field="${field.type}"
                       required>
            </div>
        `;

        html += `
            <div class="sfw-terms">
                <label class="sfw-checkbox-label">
                    <input type="checkbox" class="sfw-terms-checkbox">
                    <span>Я согласен с <a href="#" class="sfw-terms-link">условиями</a> розыгрыша</span>
                </label>
            </div>
            <div class="sfw-actions-buttons">
                <button class="sfw-spin-trigger sfw-play-btn">Играть</button>
                <button class="sfw-decline-btn">Отказаться</button>
            </div>
        `;

        actionsContainer.innerHTML = html;

        // Привязываем события
        const playBtn = actionsContainer.querySelector('.sfw-play-btn');
        const declineBtn = actionsContainer.querySelector('.sfw-decline-btn');
        const termsCheckbox = actionsContainer.querySelector('.sfw-terms-checkbox');
        const contactInput = actionsContainer.querySelector('.sfw-contact-input');

        playBtn?.addEventListener('click', () => this.validateAndStartSpin());
        declineBtn?.addEventListener('click', () => this.declinePrize());

        // Добавляем стили для кнопок
        const style = document.createElement('style');
        style.textContent = `
            .sfw-actions-buttons {
                display: flex;
                gap: 12px;
                margin-top: 15px;
            }
            .sfw-play-btn {
                flex: 2;
                background: var(--sfw-accent, #6366f1);
                color: white;
                border: none;
                border-radius: 12px;
                padding: 12px 20px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }
            .sfw-play-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(99,102,241,0.3);
            }
            .sfw-decline-btn {
                flex: 1;
                background: transparent;
                color: #6b7280;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 12px 20px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s;
            }
            .sfw-decline-btn:hover {
                background: #f9fafb;
                border-color: #d1d5db;
            }
            .sfw-contact-input {
                width: 100%;
                padding: 14px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 12px;
                font-size: 16px;
                transition: all 0.2s;
                margin-bottom: 15px;
            }
            .sfw-contact-input:focus {
                border-color: var(--sfw-accent, #6366f1);
                outline: none;
            }
            .sfw-contact-input.error {
                border-color: #ef4444;
                background: #fef2f2;
            }
            .sfw-terms {
                margin: 15px 0;
            }
            .sfw-checkbox-label {
                display: flex;
                align-items: center;
                gap: 10px;
                cursor: pointer;
                font-size: 13px;
                color: #6b7280;
            }
            .sfw-checkbox-label input {
                width: 18px;
                height: 18px;
                cursor: pointer;
            }
            .sfw-terms-link {
                color: var(--sfw-accent, #6366f1);
                text-decoration: none;
            }
            .sfw-terms-link:hover {
                text-decoration: underline;
            }
            .sfw-spinner {
                text-align: center;
                padding: 30px;
            }
            .sfw-spinner i {
                color: var(--sfw-accent, #6366f1);
                margin-bottom: 15px;
            }
            .sfw-spinner p {
                margin: 0;
                color: #6b7280;
            }
            .sfw-win-result {
                text-align: center;
                padding: 20px;
            }
            .sfw-win-icon {
                font-size: 48px;
                margin-bottom: 15px;
            }
            .sfw-win-result h4 {
                margin: 0 0 10px;
                font-size: 24px;
                color: var(--sfw-modal-text, #1f2937);
            }
            .sfw-win-result p {
                margin: 0 0 15px;
                font-size: 16px;
            }
            .sfw-coupon-code {
                background: linear-gradient(135deg, #f0fdf4, #dcfce7);
                padding: 12px;
                border-radius: 12px;
                font-size: 18px;
                font-weight: bold;
                letter-spacing: 2px;
                margin: 15px 0;
                color: #166534;
            }
            .sfw-close-win {
                background: var(--sfw-accent, #6366f1);
                color: white;
                border: none;
                border-radius: 12px;
                padding: 12px 24px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                width: 100%;
                margin-top: 10px;
            }
            .sfw-decline-message {
                text-align: center;
                padding: 30px;
            }
            .sfw-decline-message i {
                font-size: 48px;
                color: #9ca3af;
                margin-bottom: 15px;
            }
            .sfw-decline-message p {
                margin: 0 0 20px;
                font-size: 16px;
                color: #6b7280;
            }
            .sfw-decline-message button {
                background: var(--sfw-accent, #6366f1);
                color: white;
                border: none;
                border-radius: 12px;
                padding: 10px 20px;
                cursor: pointer;
            }
        `;
        if (!document.querySelector('#sfw-dynamic-styles')) {
            style.id = 'sfw-dynamic-styles';
            document.head.appendChild(style);
        }
    }

    validateAndStartSpin() {
        const actionsContainer = this.container.querySelector('.sfw-actions');
        const contactInput = actionsContainer.querySelector('.sfw-contact-input');
        const termsCheckbox = actionsContainer.querySelector('.sfw-terms-checkbox');

        // Проверяем заполнение поля
        if (!contactInput || !contactInput.value.trim()) {
            contactInput?.classList.add('error');
            alert(this.settings.messages.fill_contact);
            return;
        }

        contactInput.classList.remove('error');

        // Проверяем согласие с условиями
        if (!termsCheckbox?.checked) {
            alert(this.settings.messages.accept_terms);
            return;
        }

        // Сохраняем контакт
        this.userContact = contactInput.value.trim();
        this.isContactSubmitted = true;

        // Отправляем данные на webhook если указан
        if (this.settings.form.webhook_url) {
            fetch(this.settings.form.webhook_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ contact: this.userContact, widget_id: this.id, action: 'contact' })
            }).catch(e => console.error('Webhook error:', e));
        }

        // Запускаем вращение
        this.startSpin();
    }

    declinePrize() {
        const actionsContainer = this.container.querySelector('.sfw-actions');

        // Показываем сообщение об отказе
        actionsContainer.innerHTML = `
            <div class="sfw-decline-message">
                <i class="fa fa-frown-o"></i>
                <p>${this.escapeHtml(this.settings.messages.reject_prize)}</p>
                <button class="sfw-close-decline">Закрыть</button>
            </div>
        `;

        const closeBtn = actionsContainer.querySelector('.sfw-close-decline');
        closeBtn?.addEventListener('click', () => {
            this.close();
            setTimeout(() => {
                this.isContactSubmitted = false;
                this.userContact = null;
                this.renderContactForm();
            }, 300);
        });

        this.track('decline_prize');
    }

    bindEvents() {
        const trigger = this.container.querySelector('[data-sp-toggle]');
        const closeBtn = this.container.querySelector('[data-sp-close]');

        trigger?.addEventListener('click', () => this.toggle());
        closeBtn?.addEventListener('click', () => this.close());

        this.container.querySelector('.sfw-overlay')?.addEventListener('click', (e) => {
            if (e.target.classList.contains('sfw-overlay')) this.close();
        });
    }

    toggle() {
        this.container.classList.toggle(this.activeClass);
        if (this.container.classList.contains(this.activeClass)) {
            this.track('open_wheel');
            this.drawWheel();
            this.isContactSubmitted = false;
            this.renderContactForm();
        }
    }

    close() {
        this.container.classList.remove(this.activeClass);
        this.isSpinning = false;
        this.isContactSubmitted = false;
    }

    startSpin() {
        if (this.isSpinning) return;

        if (this.isLimitReached()) {
            alert(this.settings.messages.spin_limit_reached);
            return;
        }

        const segments = this.settings.wheel.segments;
        if (!segments || segments.length === 0) return;

        this.isSpinning = true;

        // Показываем спиннер
        const actionsContainer = this.container.querySelector('.sfw-actions');
        actionsContainer.innerHTML = `<div class="sfw-spinner"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Вращаем колесо...</p></div>`;

        // Расчет выигрыша
        const winIndex = Math.floor(Math.random() * segments.length);
        this.wonSegment = segments[winIndex];

        const segmentDeg = 360 / segments.length;
        const rotationNeeded = (360 - (winIndex * segmentDeg)) - (segmentDeg / 2);
        const totalRotation = 1440 + rotationNeeded;

        this.currentRotation += totalRotation;

        this.canvas.style.transition = `transform ${this.settings.wheel.rotation_speed || 4}s cubic-bezier(0.25, 0.1, 0.15, 1)`;
        this.canvas.style.transform = `rotate(${this.currentRotation}deg)`;

        this.track('spin_start');

        setTimeout(() => this.onSpinEnd(), (this.settings.wheel.rotation_speed || 4) * 1000);
    }

    onSpinEnd() {
        this.isSpinning = false;
        this.track('spin_win');
        this.saveSpinFactor();

        const actionsContainer = this.container.querySelector('.sfw-actions');
        this.renderWinResult(actionsContainer);
    }

    renderWinResult(container) {
        const wonSegment = this.wonSegment;

        // Сохраняем выигрыш
        const wins = JSON.parse(localStorage.getItem(`${this.storageKey}_wins`) || '[]');
        wins.push({ prize: wonSegment.label, code: wonSegment.value, contact: this.userContact, date: Date.now() });
        localStorage.setItem(`${this.storageKey}_wins`, JSON.stringify(wins));

        let html = `<div class="sfw-win-result">`;
        html += `<div class="sfw-win-icon">🎉</div>`;
        html += `<h4>${this.escapeHtml(this.settings.form.title)}</h4>`;
        html += `<p>Вы выиграли: <strong>${this.escapeHtml(wonSegment.label)}</strong></p>`;

        if (wonSegment.value) {
            html += `<div class="sfw-coupon-code">${this.escapeHtml(wonSegment.value)}</div>`;
        }

        html += `<button class="sfw-close-win">Закрыть</button></div>`;

        container.innerHTML = html;

        const closeBtn = container.querySelector('.sfw-close-win');
        closeBtn?.addEventListener('click', () => {
            this.close();
            setTimeout(() => this.resetWheel(), 300);
        });

        // Отправка данных о выигрыше на webhook
        if (this.settings.form.webhook_url && this.userContact) {
            fetch(this.settings.form.webhook_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    contact: this.userContact,
                    prize: wonSegment.label,
                    code: wonSegment.value,
                    widget_id: this.id,
                    action: 'win'
                })
            }).catch(e => console.error('Webhook error:', e));
        }
    }

    resetWheel() {
        this.currentRotation = 0;
        if (this.canvas) {
            this.canvas.style.transform = 'rotate(0deg)';
            this.canvas.style.transition = 'none';
        }
        this.isContactSubmitted = false;
        this.userContact = null;
    }

    isLimitReached() {
        const frequency = this.settings.limits?.frequency || 'once_session';
        const stored = localStorage.getItem(this.storageKey);

        if (stored) {
            try {
                const data = JSON.parse(stored);
                if (frequency === 'once_forever') return true;
                if (frequency === 'once_session') return true;
                if (frequency === 'once_day' && (Date.now() - data.timestamp) < 86400000) return true;
            } catch(e) {}
        }

        const wins = JSON.parse(localStorage.getItem(`${this.storageKey}_wins`) || '[]');
        const spinsPerUser = this.settings.limits?.spins_per_user || 0;
        if (spinsPerUser > 0 && wins.length >= spinsPerUser) return true;

        const spinsPerDay = this.settings.limits?.spins_per_day || 0;
        if (spinsPerDay > 0) {
            const todayWins = wins.filter(w => Date.now() - w.date < 86400000).length;
            if (todayWins >= spinsPerDay) return true;
        }

        return false;
    }

    saveSpinFactor() {
        localStorage.setItem(this.storageKey, JSON.stringify({ timestamp: Date.now() }));
    }

    shouldHide() {
        if (this.settings.limits?.require_auth && !window.SmGet?.user?.id) return true;

        const stored = localStorage.getItem(this.storageKey);
        if (!stored) return false;

        const frequency = this.settings.limits?.frequency || 'once_session';
        if (frequency === 'once_forever') return true;
        if (frequency === 'once_session') return true;

        return false;
    }

    lightenColor(color, percent) {
        if (!color) return '#ffffff';
        return color;
    }

    darkenColor(color, percent) {
        if (!color) return '#000000';
        return color;
    }

    mergeDefaults(settings, defaults) {
        const merged = JSON.parse(JSON.stringify(defaults));

        const deepMerge = (target, source) => {
            for (const key in source) {
                if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                    if (!target[key]) target[key] = {};
                    deepMerge(target[key], source[key]);
                } else {
                    target[key] = source[key];
                }
            }
        };

        deepMerge(merged, settings);
        return merged;
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

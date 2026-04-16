window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets) {
        super(settings, id, assets);

        // === ВСЕ ДЕФОЛТНЫЕ ЗНАЧЕНИЯ ===
        this.defaults = {
            button: {
                position: 'bottom-right',
                custom_position: { x: 20, y: 20 },
                text: 'Крутить колесо',
                icon: '🎡',
                bg_color: '#FF6B6B',
                text_color: '#FFFFFF',
                size: 'medium',
                border_radius: '50px',
                show_on_load: true,
                auto_open_delay: 0
            },
            wheel: {
                size: 400,
                rotation_speed: 8,
                background_color: '#FFFFFF',
                text_color: '#FFFFFF',
                border_color: '#FFD700',
                border_width: 3,
                pointer_color: '#FF4444',
                font_size: 14,
                segments: []
            },
            design: {
                modal_bg_color: '#FFFFFF',
                modal_text_color: '#2C3E50',
                accent_color: '#FF6B6B',
                title: 'Выиграйте приз!',
                description: 'Крутите колесо и получите скидку до 50%'
            },
            form: {
                enabled: true,
                title: 'Поздравляем!',
                subtitle: 'Введите ваши данные, чтобы получить приз',
                fields: [
                    { type: 'text', name: 'name', label: 'Ваше имя', required: true, placeholder: 'Иван Иванов' },
                    { type: 'email', name: 'email', label: 'Email', required: true, placeholder: 'ivan@example.com' }
                ],
                button_text: 'Получить приз',
                success_message: 'Ваш купон: {CODE}',
                webhook_url: ''
            },
            limits: {
                spins_per_user: 1,
                spins_per_day: 1,
                spins_total: 0,
                require_auth: false,
                collect_email: true
            },
            messages: {
                spin_limit_reached: 'Вы уже использовали все попытки',
                already_spun_today: 'Вы уже крутили колесо сегодня',
                coupon_expired: 'Срок действия купона истек',
                coupon_usage_limit: 'Лимит использования купона исчерпан'
            },
            trigger_type: 'click',
            delay: 0,
            scroll_percent: 50,
            frequency: 'once_session',
            close_behavior: 'hide_session',
            coupons: []
        };

        // Мержим настройки с дефолтами
        this.settings = this.mergeDefaults(settings, this.defaults);

        this.storageKey = `sm_fortune_wheel_${this.id}`;
        this.isSpinning = false;
        this.currentRotation = 0;
        this.selectedSegment = null;
        this.canvas = null;
        this.ctx = null;
        this.initialized = false;
        this.modal = null;
        this.button = null;
    }

    mergeDefaults(settings, defaults) {
        const result = { ...defaults };
        if (!settings) return result;

        for (const key in settings) {
            if (settings[key] && typeof settings[key] === 'object' && !Array.isArray(settings[key])) {
                result[key] = this.mergeDefaults(settings[key], defaults[key] || {});
            } else if (settings[key] !== undefined) {
                result[key] = settings[key];
            }
        }
        return result;
    }

    init() {
        console.log('Fortune wheel init:', this.id);

        if (this.shouldHide()) {
            console.log('Widget hidden by frequency');
            return;
        }

        this.initWidget();

        const trigger = this.settings.trigger_type || 'click';

        if (trigger === 'time') {
            const delay = (this.settings.delay || 0) * 1000;
            setTimeout(() => this.openModal(), delay);
        } else if (trigger === 'scroll') {
            this.initScrollTrigger();
        } else if (trigger === 'exit') {
            this.initExitIntent();
        }

        if (this.settings.button.auto_open_delay > 0) {
            setTimeout(() => this.openModal(), this.settings.button.auto_open_delay * 1000);
        }
    }

    initScrollTrigger() {
        const percent = this.settings.scroll_percent || 50;
        let triggered = false;

        const checkScroll = () => {
            if (triggered) return;
            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent >= percent) {
                triggered = true;
                this.openModal();
                window.removeEventListener('scroll', checkScroll);
            }
        };

        window.addEventListener('scroll', checkScroll);
        setTimeout(checkScroll, 500);
    }

    initExitIntent() {
        let triggered = false;
        document.addEventListener('mouseleave', (e) => {
            if (triggered) return;
            if (e.clientY <= 0) {
                triggered = true;
                this.openModal();
            }
        });
    }

    initWidget() {
        if (this.initialized) return;
        this.initialized = true;

        this.injectStyles();
        this.renderButton();
        this.renderModal();
        this.bindEvents();
    }

    injectStyles() {
        const design = this.settings.design || {};
        const wheel = this.settings.wheel || {};
        const btn = this.settings.button || {};

        const styleId = `sp-style-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;

            // Подставляем CSS переменные
            let css = this.assets.css || '';
            css = css.replace(/--modal-bg:.*?;/g, `--modal-bg: ${design.modal_bg_color || '#FFFFFF'};`);
            css = css.replace(/--accent-color:.*?;/g, `--accent-color: ${design.accent_color || '#FF6B6B'};`);
            css = css.replace(/--pointer-color:.*?;/g, `--pointer-color: ${wheel.pointer_color || '#FF4444'};`);
            css = css.replace(/--wheel-bg:.*?;/g, `--wheel-bg: ${wheel.background_color || '#FFFFFF'};`);
            css = css.replace(/--wheel-text-color:.*?;/g, `--wheel-text-color: ${wheel.text_color || '#FFFFFF'};`);
            css = css.replace(/--btn-bg:.*?;/g, `--btn-bg: ${btn.bg_color || '#FF6B6B'};`);
            css = css.replace(/--btn-text:.*?;/g, `--btn-text: ${btn.text_color || '#FFFFFF'};`);

            style.textContent = css;
            document.head.appendChild(style);
        }
    }

    renderButton() {
        if (document.querySelector(`[data-fw-open="${this.id}"]`)) return;

        // Используем шаблон из assets
        let buttonHtml = this.assets.buttonHtml || this.assets.html;

        // Ищем секцию кнопки или используем весь шаблон
        if (buttonHtml.includes('fw-open-button')) {
            const temp = document.createElement('div');
            temp.innerHTML = buttonHtml;
            const buttonElement = temp.querySelector('.fw-open-button');
            if (buttonElement) {
                buttonHtml = buttonElement.outerHTML;
            }
        }

        // Подставляем значения
        const btn = this.settings.button;
        buttonHtml = buttonHtml
            .replace(/\{button_icon\}/g, btn.icon || '🎡')
            .replace(/\{button_text\}/g, this.escapeHtml(btn.text || 'Крутить колесо'));

        const div = document.createElement('div');
        div.innerHTML = buttonHtml;
        this.button = div.firstElementChild;

        if (this.button) {
            this.button.setAttribute('data-fw-open', this.id);

            // Применяем стили из настроек
            this.button.style.background = btn.bg_color;
            this.button.style.color = btn.text_color;
            this.button.style.borderRadius = btn.border_radius;

            // Позиция
            if (btn.position === 'custom') {
                this.button.style.bottom = `${btn.custom_position?.y || 20}px`;
                this.button.style.right = `${btn.custom_position?.x || 20}px`;
            } else {
                const pos = btn.position || 'bottom-right';
                this.button.classList.add(`fw-position-${pos}`);
            }

            // Размер
            this.button.classList.add(`fw-button-${btn.size || 'medium'}`);

            document.body.appendChild(this.button);

            if (btn.show_on_load) {
                this.button.style.display = 'flex';
            }
        }
    }

    renderModal() {
        if (document.querySelector(`[data-fw-modal="${this.id}"]`)) return;

        // Используем HTML шаблон из assets
        let modalHtml = this.assets.html;

        // Подставляем значения
        const wheel = this.settings.wheel;
        const design = this.settings.design;
        const form = this.settings.form;

        modalHtml = modalHtml
            .replace(/\{id\}/g, this.id)
            .replace(/\{wheel_size\}/g, wheel.size || 400)
            .replace(/\{title\}/g, this.escapeHtml(design.title || 'Выиграйте приз!'))
            .replace(/\{description\}/g, this.escapeHtml(design.description || 'Крутите колесо и получите скидку до 50%'))
            .replace(/\{form_title\}/g, this.escapeHtml(form.title || 'Поздравляем!'))
            .replace(/\{form_subtitle\}/g, this.escapeHtml(form.subtitle || 'Введите ваши данные, чтобы получить приз'))
            .replace(/\{form_button_text\}/g, this.escapeHtml(form.button_text || 'Получить приз'));

        const div = document.createElement('div');
        div.innerHTML = modalHtml;
        this.modal = div.firstElementChild;

        if (this.modal) {
            this.modal.setAttribute('data-fw-modal', this.id);
            this.modal.style.display = 'none';
            document.body.appendChild(this.modal);

            // Инициализируем canvas после рендера
            setTimeout(() => this.initCanvas(), 100);
        }
    }

    initCanvas() {
        const canvas = document.querySelector(`#fw-canvas-${this.id}`);
        if (!canvas) return;

        const size = this.settings.wheel.size || 400;
        canvas.width = size;
        canvas.height = size;

        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');

        this.drawWheel();
    }

    drawWheel() {
        if (!this.ctx || !this.canvas) return;

        const segments = this.settings.wheel.segments || [];
        const size = this.canvas.width;
        const centerX = size / 2;
        const centerY = size / 2;
        const radius = size / 2 - 10;

        this.ctx.clearRect(0, 0, size, size);

        if (segments.length === 0) {
            this.drawDefaultWheel();
            return;
        }

        let startAngle = (this.currentRotation || 0) * Math.PI / 180;

        segments.forEach((segment) => {
            const angle = (segment.angle || 0) * Math.PI / 180;
            const endAngle = startAngle + angle;

            this.ctx.beginPath();
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            this.ctx.closePath();

            this.ctx.fillStyle = segment.color || '#FF6B6B';
            this.ctx.fill();

            // Рисуем текст
            const textAngle = startAngle + angle / 2;
            const textRadius = radius * 0.65;
            const textX = centerX + textRadius * Math.cos(textAngle);
            const textY = centerY + textRadius * Math.sin(textAngle);

            let text = segment.name || '';
            if (text.length > 12) text = text.substr(0, 10) + '..';

            this.ctx.save();
            this.ctx.translate(textX, textY);
            this.ctx.rotate(textAngle + Math.PI / 2);
            this.ctx.fillStyle = this.settings.wheel.text_color || '#FFFFFF';
            this.ctx.font = `bold ${Math.max(10, Math.min(14, (segment.angle || 20) / 12))}px Arial`;
            this.ctx.textAlign = 'center';
            this.ctx.textBaseline = 'middle';
            this.ctx.fillText(text, 0, 0);
            this.ctx.restore();

            startAngle = endAngle;
        });

        // Центральный круг
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
        this.ctx.fillStyle = this.settings.wheel.background_color || '#FFFFFF';
        this.ctx.fill();
        this.ctx.stroke();

        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
        this.ctx.fillStyle = this.settings.wheel.pointer_color || '#FF4444';
        this.ctx.fill();
    }

    drawDefaultWheel() {
        if (!this.ctx) return;

        const colors = ['#FF6B6B', '#4ECDC4', '#FFE66D', '#95A5A6', '#FF6B6B', '#4ECDC4', '#FFE66D', '#95A5A6'];
        const centerX = this.canvas.width / 2;
        const centerY = this.canvas.height / 2;
        const radius = this.canvas.width / 2 - 10;

        for (let i = 0; i < 8; i++) {
            const startAngle = (i * 45 + this.currentRotation) * Math.PI / 180;
            const endAngle = ((i + 1) * 45 + this.currentRotation) * Math.PI / 180;

            this.ctx.beginPath();
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            this.ctx.closePath();

            this.ctx.fillStyle = colors[i % colors.length];
            this.ctx.fill();
        }
    }

    generateFormFields() {
        const container = this.modal?.querySelector('#fw-form-fields');
        if (!container) return;

        const fields = this.settings.form.fields || [];
        const fieldsHtml = fields.map(field => {
            const required = field.required ? 'required' : '';
            const placeholder = this.escapeHtml(field.placeholder || field.label || '');
            const name = field.name || field.type + '_' + Date.now();

            if (field.type === 'textarea') {
                return `<textarea name="${name}" placeholder="${placeholder}" ${required} class="fw-field"></textarea>`;
            }
            if (field.type === 'hidden') {
                return `<input type="hidden" name="${name}" value="${this.escapeHtml(field.default_value || '')}">`;
            }
            return `<input type="${field.type}" name="${name}" placeholder="${placeholder}" ${required} class="fw-field">`;
        }).join('');

        container.innerHTML = fieldsHtml || '<div class="fw-field-empty">Нет полей формы</div>';
    }

    bindEvents() {
        // Кнопка открытия
        if (this.button) {
            this.button.addEventListener('click', (e) => {
                e.preventDefault();
                this.openModal();
            });
        }

        if (!this.modal) return;

        // Закрытие модалки
        const closeBtn = this.modal.querySelector('[data-fw-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeModal());
        }

        const closeBtn2 = this.modal.querySelector('.fw-close-btn');
        if (closeBtn2) {
            closeBtn2.addEventListener('click', () => this.closeModal());
        }

        // Клик по оверлею
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeModal();
        });

        // Вращение колеса
        const wheel = this.modal.querySelector(`#fw-canvas-${this.id}`);
        if (wheel) {
            wheel.addEventListener('click', () => this.spin());
        }

        // Отправка формы
        const form = this.modal.querySelector('[data-fw-form]');
        if (form) {
            form.addEventListener('submit', (e) => this.submitForm(e));
        }

        // Генерируем поля формы
        this.generateFormFields();
    }

    async spin() {
        if (this.isSpinning) {
            this.showMessage('Колесо уже крутится...');
            return;
        }

        this.isSpinning = true;

        // Выбираем случайный купон на основе вероятности
        const activeCoupons = (this.settings.coupons || []).filter(c => c.enabled !== false);
        let selectedCoupon = null;

        if (activeCoupons.length > 0) {
            const totalProb = activeCoupons.reduce((sum, c) => sum + (c.probability || 0), 0);
            let random = Math.random() * totalProb;
            let current = 0;

            for (const coupon of activeCoupons) {
                current += coupon.probability || 0;
                if (random <= current) {
                    selectedCoupon = coupon;
                    break;
                }
            }
        }

        if (!selectedCoupon) {
            selectedCoupon = { id: 'no_prize', name: 'Попробуй еще раз', type: 'no_prize', color: '#95A5A6', icon: '😢', description: 'К сожалению, вы ничего не выиграли' };
        }

        // Анимация вращения
        const spins = 5 + Math.random() * 3;
        const targetRotation = this.currentRotation + spins * 360 + Math.random() * 360;

        this.animateSpin(targetRotation, () => {
            this.isSpinning = false;
            this.onSpinComplete(selectedCoupon);
        });
    }

    animateSpin(targetRotation, callback) {
        const startRotation = this.currentRotation;
        const duration = (this.settings.wheel.rotation_speed || 8) * 1000;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(1, elapsed / duration);
            const easeOut = (t) => 1 - Math.pow(1 - t, 3);
            const eased = easeOut(progress);

            this.currentRotation = startRotation + (targetRotation - startRotation) * eased;
            this.drawWheel();

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                this.currentRotation = targetRotation % 360;
                this.drawWheel();
                callback();
            }
        };

        requestAnimationFrame(animate);
    }

    onSpinComplete(coupon) {
        if (coupon.type === 'no_prize') {
            this.showResult(coupon, false);
        } else {
            const code = coupon.generate_unique
                ? 'COUPON_' + Math.random().toString(36).substr(2, 8).toUpperCase()
                : coupon.code;
            this.showResult(coupon, true, code);
        }

        this.track('spin', { coupon: coupon.id });
    }

    async submitForm(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);
        const userData = {};
        formData.forEach((value, key) => { userData[key] = value; });

        // Здесь будет отправка на сервер
        console.log('Form submitted:', userData);

        // Показываем форму с результатом
        const spinBtn = this.modal.querySelector('.fw-spin-btn');
        if (spinBtn) {
            spinBtn.click();
        }
    }

    showResult(coupon, isWin, code = null) {
        if (!this.modal) return;

        const wheelContainer = this.modal.querySelector('.fw-wheel-container');
        const formContainer = this.modal.querySelector('.fw-form-container');
        const resultContainer = this.modal.querySelector('.fw-result-container');

        if (wheelContainer) wheelContainer.style.display = 'none';
        if (formContainer) formContainer.style.display = 'none';
        if (resultContainer) resultContainer.style.display = 'block';

        const iconEl = resultContainer?.querySelector('.fw-result-icon');
        const titleEl = resultContainer?.querySelector('.fw-result-title');
        const descEl = resultContainer?.querySelector('.fw-result-description');
        const codeEl = resultContainer?.querySelector('.fw-coupon-code');

        if (isWin) {
            if (iconEl) iconEl.textContent = coupon.icon || '🎉';
            if (titleEl) titleEl.textContent = 'Поздравляем!';
            if (descEl) descEl.textContent = `Вы выиграли: ${coupon.name}`;
            if (codeEl && code) {
                codeEl.textContent = `Ваш купон: ${code}`;
                codeEl.style.display = 'block';
            }
            if (this.settings.design.confetti_enabled) {
                this.celebrate();
            }
        } else {
            if (iconEl) iconEl.textContent = '😢';
            if (titleEl) titleEl.textContent = 'Повезет в следующий раз!';
            if (descEl) descEl.textContent = coupon.description || 'Попробуйте еще раз';
            if (codeEl) codeEl.style.display = 'none';
        }
    }

    celebrate() {
        const colors = ['#FF6B6B', '#4ECDC4', '#FFE66D', '#95A5A6', '#667eea', '#764ba2'];

        for (let i = 0; i < 80; i++) {
            const confetti = document.createElement('div');
            confetti.style.cssText = `
                position: fixed;
                left: ${Math.random() * window.innerWidth}px;
                top: -10px;
                width: ${5 + Math.random() * 10}px;
                height: ${5 + Math.random() * 10}px;
                background: ${colors[Math.floor(Math.random() * colors.length)]};
                z-index: 1000000;
                pointer-events: none;
                animation: fw-confetti-fall ${2 + Math.random() * 2}s linear forwards;
            `;
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 3000);
        }
    }

    openModal() {
        if (this.modal) {
            this.modal.style.display = 'flex';
            this.track('open');
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.style.display = 'none';

            const wheelContainer = this.modal.querySelector('.fw-wheel-container');
            const formContainer = this.modal.querySelector('.fw-form-container');
            const resultContainer = this.modal.querySelector('.fw-result-container');

            if (wheelContainer) wheelContainer.style.display = 'block';
            if (formContainer) formContainer.style.display = 'block';
            if (resultContainer) resultContainer.style.display = 'none';

            this.track('close');

            const timestamp = Date.now();
            const closeBehavior = this.settings.close_behavior || 'hide_session';
            if (closeBehavior === 'hide_forever') {
                localStorage.setItem(this.storageKey, timestamp.toString());
            } else {
                sessionStorage.setItem(this.storageKey, timestamp.toString());
            }
        }
    }

    showMessage(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000000;
            animation: fw-toast-fade 2s ease forwards;
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }

    shouldHide() {
        const closedData = localStorage.getItem(this.storageKey) || sessionStorage.getItem(this.storageKey);
        if (!closedData) return false;

        const frequency = this.settings.frequency || 'once_session';
        const now = Date.now();

        if (frequency === 'once_session') return true;
        if (frequency === 'once_day' && now - parseInt(closedData) < 86400000) return true;
        if (frequency === 'once_week' && now - parseInt(closedData) < 604800000) return true;

        return false;
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

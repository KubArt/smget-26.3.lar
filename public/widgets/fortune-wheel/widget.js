window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        // Сохраняем доступ к конфигам
        this.cfg = {
            btn: settings.button || {},
            wheel: settings.wheel || {},
            form: settings.form || {},
            design: settings.design || {},
            anim: settings.animation || {},
            msg: settings.messages || {}
        };

        this.apiUrl = settings.api_url || 'http://smget-26.3.lar/api/v1/capture/fortune-wheel';
        this.isSpinning = false;
        this.currentRotation = 0;
    }

    init() {
        this._init();
    }

    mount() {
        this.injectStyles();
        this.render();
        this.initCanvas();
        this.bindEvents();

        if (this.cfg.btn.auto_open_delay > 0) {
            setTimeout(() => this.openModal(), this.cfg.btn.auto_open_delay * 1000);
        }
    }

    injectStyles() {
        const cssVars = `
            .sfw-root {
                --sfw-btn-bg: ${this.cfg.btn.bg_color || '#6366f1'};
                --sfw-btn-text: ${this.cfg.btn.text_color || '#ffffff'};
                --sfw-accent: ${this.cfg.design.accent_color || '#6366f1'};
                --sfw-modal-bg: ${this.cfg.design.modal_bg_color || '#ffffff'};
                --sfw-modal-text: ${this.cfg.design.modal_text_color || '#1f2937'};
                --sfw-pointer: ${this.cfg.wheel.pointer_color || '#ff4444'};
            }`;
        this.injectCustomStyles(cssVars + (this.assets.css || ''));
    }

    render() {
        const isRight = this.cfg.btn.position === 'bottom-right';
        const html = this.processTemplate(this.assets.html, {
            id: this.id,
            widget_id: this.id,
            position: isRight ? 'right' : 'left',
            title: this.cfg.design.title || 'Выиграйте приз!',
            description: this.cfg.design.description || 'Испытайте свою удачу',
            contact_type: this.cfg.form.contact_type || 'tel',
            contact_placeholder: this.cfg.form.contact_type === 'tel' ? '+7 (999) 123-45-67' : 'your@email.com',
            terms_text: this.cfg.form.terms_text || 'Я согласен с условиями розыгрыша',
            spin_button_text: this.cfg.btn.text || 'Крутить колесо',
            decline_text: this.cfg.form.decline_text || 'Отказаться',
            win_title: this.cfg.form.title || 'Поздравляем!'
        });

        this.container = this.createContainer(html, `sfw-root sp-position-${isRight ? 'right' : 'left'}`);

        const trigger = this.container.querySelector('.sfw-trigger');
        if (trigger) {
            const sizes = { small: '45px', medium: '60px', large: '75px' };
            const size = sizes[this.cfg.btn.size] || '60px';
            Object.assign(trigger.style, {
                width: size, height: size,
                borderRadius: this.cfg.btn.border_radius || '50px',
                background: this.cfg.btn.bg_color,
                color: this.cfg.btn.text_color,
                opacity: this.cfg.design.opacity || 1
            });
            const icon = trigger.querySelector('.sfw-icon');
            if (icon) icon.textContent = this.cfg.btn.icon || '🎡';
        }

        this.setState('contact');
    }

    // ========== CANVAS (ВОЗВРАЩЕН ОРИГИНАЛ) ==========
    initCanvas() {
        this.canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.drawWheel();
    }

    drawWheel() {
        const segments = (this.cfg.wheel.segments || []).filter(s => s.enabled !== false);
        const size = 380, radius = size / 2;
        if (!segments.length) return;

        this.canvas.width = this.canvas.height = size;
        const arc = (2 * Math.PI) / segments.length;

        segments.forEach((seg, i) => {
            const startAngle = i * arc;
            const endAngle = startAngle + arc;

            // Сектор
            this.ctx.beginPath();
            this.ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            this.ctx.moveTo(radius, radius);
            this.ctx.arc(radius, radius, radius - 10, startAngle, endAngle);
            this.ctx.fill();

            // Обводка
            this.ctx.beginPath();
            this.ctx.strokeStyle = this.cfg.wheel.border_color || '#ffffff';
            this.ctx.lineWidth = this.cfg.wheel.border_width || 3;
            this.ctx.moveTo(radius, radius);
            this.ctx.arc(radius, radius, radius - 10, startAngle, endAngle);
            this.ctx.lineTo(radius, radius);
            this.ctx.stroke();

            this.drawTextOnSegment(seg.label, radius, radius, radius, startAngle, endAngle);
        });

        // Внешний борт и центр
        this.ctx.beginPath();
        this.ctx.arc(radius, radius, radius - 10, 0, 2 * Math.PI);
        this.ctx.strokeStyle = this.cfg.wheel.border_color || '#ffffff';
        this.ctx.lineWidth = this.cfg.wheel.outer_border_width || 9;
        this.ctx.stroke();

        this.drawCenterCircle(radius);
    }

    drawTextOnSegment(text, cX, cY, radius, startAngle, endAngle) {
        if (!text) return;
        const angle = startAngle + (endAngle - startAngle) / 2;
        const lines = this.wrapText(text, 10);
        const fontSize = lines.length > 1 ? 14 : 16;

        this.ctx.save();
        this.ctx.translate(cX, cY);
        this.ctx.rotate(angle);
        this.ctx.textAlign = "center";
        this.ctx.textBaseline = "middle";
        this.ctx.fillStyle = this.cfg.wheel.text_color || '#1f2937';
        this.ctx.font = `bold ${fontSize}px system-ui`;

        const lineHeight = fontSize * 1.2;
        lines.forEach((line, i) => {
            this.ctx.fillText(line, radius - 75, ((i - (lines.length - 1) / 2) * lineHeight));
        });
        this.ctx.restore();
    }

    wrapText(text, max) {
        const words = text.split(' '), lines = [];
        let cur = '';
        words.forEach(w => {
            if ((cur + ' ' + w).trim().length <= max) cur = (cur ? cur + ' ' + w : w);
            else { lines.push(cur); cur = w; }
        });
        if (cur) lines.push(cur);
        return lines;
    }

    drawCenterCircle(r) {
        this.ctx.beginPath();
        this.ctx.arc(r, r, 25, 0, 2 * Math.PI);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fill();
        this.ctx.beginPath();
        this.ctx.arc(r, r, 8, 0, 2 * Math.PI);
        this.ctx.fillStyle = this.cfg.design.accent_color || '#6366f1';
        this.ctx.fill();
    }

    // ========== ЛОГИКА ИСПРАВЛЕНА ==========
    async sendLead() {
        const contactInput = this.container.querySelector('.sfw-contact-input');
        const terms = this.container.querySelector('.sfw-terms-checkbox');

        if (!contactInput?.value) return this.showFieldError(contactInput);
        if (!terms?.checked) return this.showFieldError(terms);

        this.userContact = contactInput.value;
        this.setState('spinner');

        try {
            const res = await fetch(this.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Api-Key': 'widget' },
                body: JSON.stringify({ widget_id: this.id, contact: this.userContact, name: this.userContact, page_url: window.location.href })
            });
            const data = await res.json();

            if (res.ok && data.status === 'success') {
                this.targetIndex = data.target_index ?? 0;
                this.prizeData = data.widget_data?.prize || data.prize;
                this.winMessage = data.widget_data?.message || data.message;
                this.startSpin();
            } else {
                this.handleApiError(data);
            }
        } catch (e) {
            this.showError('Ошибка соединения');
        }
    }

    startSpin() {
        if (this.isSpinning) return;
        this.isSpinning = true;

        // 1. Сброс состояния анимации
        this.canvas.classList.remove('sfw-idle-spin');
        this.canvas.style.transition = 'none';
        this.canvas.style.transform = 'rotate(0deg)';
        void this.canvas.offsetHeight; // Force reflow

        // 2. Расчет угла (указатель справа на 0 градусов)
        const segments = (this.cfg.wheel.segments || []).filter(s => s.enabled !== false);
        const segmentDeg = 360 / segments.length;

        // Смещение: чтобы нужный индекс оказался под указателем (0°),
        // нужно повернуть колесо на -(индекс * градус + половина сегмента)
        const targetRotation = 360 - (this.targetIndex * segmentDeg + segmentDeg / 2);
        const totalRotation = 1800 + targetRotation; // 5 оборотов + цель

        // 3. Запуск
        this.canvas.style.transition = `transform ${this.cfg.wheel.rotation_speed || 6}s cubic-bezier(0.15, 0, 0.15, 1)`;
        this.canvas.style.transform = `rotate(${totalRotation}deg)`;

        this.track('spin_start');

        setTimeout(() => {
            this.isSpinning = false;
            this.showWinResult();
            this.track('spin_win');
        }, (this.cfg.wheel.rotation_speed || 6) * 1000);
    }

    showWinResult() {
        const p = this.prizeData;
        const mapping = {
            '.sfw-win-label': p?.name || 'Приз',
            '.sfw-win-code': p?.code || '',
            '.sfw-win-message': this.winMessage || this.cfg.form.success_message
        };

        Object.entries(mapping).forEach(([sel, val]) => {
            const el = this.container.querySelector(sel);
            if (el) el.textContent = val;
        });

        const expiresEl = this.container.querySelector('.sfw-win-expires');
        if (expiresEl && p?.expires_at) {
            expiresEl.textContent = `Действителен до: ${new Date(p.expires_at).toLocaleDateString()}`;
            expiresEl.style.display = 'block';
        }

        this.setState('result');
    }

    setState(state) {
        ['contact', 'spinner', 'result'].forEach(s => {
            const el = this.container.querySelector(`.sfw-state-${s}`);
            if (el) el.style.display = (s === state) ? 'block' : 'none';
        });
    }

    bindEvents() {
        this.container.addEventListener('click', (e) => {
            const t = e.target;
            if (t.closest('[data-sp-toggle]')) this.openModal();
            if (t.closest('[data-sp-close]') || t.classList.contains('sfw-overlay')) this.closeModal();
            if (t.classList.contains('sfw-spin-trigger')) this.sendLead();
            if (t.classList.contains('sfw-decline-btn')) this.closeModal();
            if (t.classList.contains('sfw-close-win')) {
                this.closeModal();
                this.resetWheel();
            }
        });
    }

    resetWheel() {
        this.isSpinning = false;
        this.canvas.style.transition = 'none';
        this.canvas.style.transform = 'rotate(0deg)';
        this.canvas.classList.add('sfw-idle-spin');
        this.setState('contact');
    }

    openModal() {
        if (this.shouldHideByFrequency()) return;
        this.container.classList.add('sp-active');
        this.track('open');
    }

    closeModal() {
        this.container.classList.remove('sp-active');
    }

    showFieldError(el) {
        if (!el) return;
        el.classList.add('sfw-error');
        el.style.animation = 'sfwShake 0.3s ease';
        setTimeout(() => {
            el.classList.remove('sfw-error');
            el.style.animation = '';
        }, 2000);
    }
};

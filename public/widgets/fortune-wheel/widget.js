/**
 * Виджет "Колесо Фортуны" - Исправленная и структурированная версия
 */
window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        const design = settings.design || {};
        this.config = {
            btn: {
                pos: settings.button?.position || 'bottom-right',
                text: settings.button?.text || 'Крутить колесо',
                bg: settings.button?.bg_color || '#6366f1',
                color: settings.button?.text_color || '#ffffff',
                delay: settings.button?.auto_open_delay || 0
            },
            wheel: {
                size: settings.wheel?.size || 300,
                speed: settings.wheel?.rotation_speed || 4,
                textColor: settings.wheel?.text_color || '#333',
                segments: settings.wheel?.segments || []
            },
            design: {
                title: settings.design?.title || 'Выиграйте приз!',
                desc: settings.design?.description || 'Испытайте удачу',
                bg: design.modal_bg_color || '#fff',
                accent: design.accent_color || '#6366f1'
            },
            form: {
                enabled: settings.form?.enabled !== false,
                fields: settings.form?.fields || [{ type: 'tel', placeholder: 'Телефон', required: true }]
            }
        };

        this.state = {
            isSpinning: false,
            rotation: 0,
            userContact: null
        };
    }
    init() {
        super._init();
    }

    mount() {
        this.injectStyles();

        const html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{title}/g, this.escapeHtml(this.config.design.title))
            .replace(/{description}/g, this.escapeHtml(this.config.design.desc));

        this.container = this.createContainer(html, `sfw-root sp-position-${this.config.btn.pos.includes('right') ? 'right' : 'left'}`);

        this.initCanvas();
        this.renderActions(); // Стандартизированный вызов
        this.bindEvents();

        if (this.config.btn.delay > 0) {
            setTimeout(() => this.toggle(true), this.config.btn.delay * 1000);
        }
    }

    /**
     * Стандартизированный рендеринг зоны действий (форма или кнопка)
     */
    renderActions() {
        const container = this.container.querySelector('.sfw-actions');
        if (!container) return;

        if (this.config.form.enabled && !this.state.userContact) {
            container.innerHTML = this.renderContactForm();
        } else {
            container.innerHTML = `<button class="sfw-spin-trigger sp-btn-main">${this.config.btn.text}</button>`;
        }
    }

    /**
     * Метод отрисовки формы - его можно переопределять в наследниках
     */
    renderContactForm() {
        const field = this.config.form.fields[0];
        return `
            <div class="sfw-contact-form">
                <input type="${field.type}" class="sfw-input" placeholder="${this.escapeHtml(field.placeholder)}" required>
                <div class="sfw-group-btns">
                    <button class="sfw-play-btn sp-btn-main">Играть</button>
                    <button class="sfw-decline-btn">Закрыть</button>
                </div>
            </div>`;
    }

    initCanvas() {
        this.canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!this.canvas) return;
        this.canvas.width = this.canvas.height = this.config.wheel.size;
        this.ctx = this.canvas.getContext('2d');
        this.drawWheel();
    }

    /**
     * Исправленный метод отрисовки (добавлена проверка на наличие текста)
     */
    drawWheel() {
        const { segments, size, textColor } = this.config.wheel;
        if (!segments.length) return;

        const radius = size / 2;
        const arc = (Math.PI * 2) / segments.length;
        this.ctx.clearRect(0, 0, size, size);

        segments.forEach((seg, i) => {
            const angle = i * arc;
            // Сектор
            this.ctx.beginPath();
            this.ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            this.ctx.moveTo(radius, radius);
            this.ctx.arc(radius, radius, radius - 10, angle, angle + arc);
            this.ctx.fill();

            // Текст (С ИСПРАВЛЕНИЕМ slice)
            this.ctx.save();
            this.ctx.translate(radius, radius);
            this.ctx.rotate(angle + arc / 2);
            this.ctx.fillStyle = textColor;
            this.ctx.font = "bold 13px sans-serif";

            // Безопасное извлечение текста
            const label = (seg.label || '').toString();
            this.ctx.fillText(label.slice(0, 15), radius - 55, 5);
            this.ctx.restore();
        });
    }

    bindEvents() {
        this.container.onclick = (e) => {
            const t = e.target;
            if (t.closest('[data-sp-toggle]')) this.toggle();
            if (t.classList.contains('sfw-play-btn')) this.handleFormSubmit();
            if (t.classList.contains('sfw-spin-trigger')) this.startSpin();
            if (t.classList.contains('sfw-decline-btn') || t.hasAttribute('data-sp-close')) this.toggle(false);
        };
    }

    handleFormSubmit() {
        const input = this.container.querySelector('.sfw-input');
        if (!input?.value.trim()) {
            input?.classList.add('sp-error');
            return;
        }
        this.state.userContact = input.value;
        this.renderActions(); // Переключаемся на кнопку "Крутить"
        this.startSpin();
    }

    startSpin() {
        if (this.state.isSpinning) return;
        this.state.isSpinning = true;

        const segments = this.config.wheel.segments;
        const winIndex = Math.floor(Math.random() * segments.length);
        const rotationNeeded = 1440 + (360 - (winIndex * (360 / segments.length)));

        this.state.rotation += rotationNeeded;
        this.canvas.style.transition = `transform ${this.config.wheel.speed}s cubic-bezier(0.15, 0, 0.15, 1)`;
        this.canvas.style.transform = `rotate(${this.state.rotation}deg)`;

        setTimeout(() => {
            this.state.isSpinning = false;
            alert(`Ваш приз: ${segments[winIndex].label}`);
            this.track('spin_win');
        }, this.config.wheel.speed * 1000);
    }

    toggle(force) {
        const method = force !== undefined ? (force ? 'add' : 'remove') : 'toggle';
        this.container.classList[method]('sp-active');
        if (this.container.classList.contains('sp-active')) this.drawWheel();
    }

    injectStyles() {
        const styleId = `sp-style-${this.id}`;
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .sfw-root {
                --accent: ${this.config.design.accent};
                --btn-bg: ${this.config.btn.bg};
                --btn-color: ${this.config.btn.color};
            }
            .sp-error { border: 1px solid red !important; }
            ${this.assets.css}
        `;
        document.head.appendChild(style);
    }

    escapeHtml(t) {
        const d = document.createElement('div');
        d.textContent = t || '';
        return d.innerHTML;
    }
};

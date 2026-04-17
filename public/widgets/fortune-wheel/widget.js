/**
 * Виджет "Колесо Фортуны"
 * Интегрирован с системой конфигурации Blade (settings.button, settings.wheel, settings.form, settings.limits)
 */
window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets) {
        super(settings, id, assets);

        // 1. Инициализация дефолтов (согласно configuration.blade.php)
        this.defaults = {
            button: {
                position: 'bottom-right',
                text: 'Крутить колесо',
                icon: '🎡',
                bg_color: '#6366f1',
                text_color: '#ffffff',
                auto_open_delay: 0
            },
            wheel: {
                size: 300,
                rotation_speed: 8,
                text_color: '#333333',
                pointer_color: '#ff4444',
                font_size: 13,
                segments: [] // Загружается из БД
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
                fields: [],
                button_text: 'Получить приз',
                success_message: 'Ваш купон: {CODE}'
            },
            limits: {
                spins_per_user: 1,
                frequency: 'once_session'
            },
            messages: {
                spin_limit_reached: 'Вы уже использовали все попытки'
            }
        };

        // Глубокое слияние настроек
        this.settings = this.mergeDefaults(settings, this.defaults);

        this.storageKey = `sm_fortune_${this.id}`;
        this.activeClass = 'sp-active';
        this.isSpinning = false;
        this.currentRotation = 0;
        this.wonSegment = null;
    }

    /**
     * Точка входа (вызывается из widget-core.js)
     */
    init() {
        if (this.shouldHide()) return;

        // Обработка триггеров из конфига
        const delay = (this.settings.button.auto_open_delay || 0) * 1000;

        if (this.settings.trigger_type === 'scroll') {
            this.initScrollTrigger(this.settings.scroll_percent || 50, delay);
        } else {
            // Стандартный монтаж кнопки
            this.mount();
            // Если есть задержка автооткрытия
            if (delay > 0) {
                setTimeout(() => this.openModal(), delay);
            }
        }
    }

    mount() {
        // Подготовка динамических стилей (переменные из конфига)
        const styleVars = `
            :root {
                --sfw-btn-bg: ${this.settings.button.bg_color};
                --sfw-btn-text: ${this.settings.button.text_color};
                --sfw-accent: ${this.settings.design.accent_color};
                --sfw-modal-bg: ${this.settings.design.modal_bg_color};
                --sfw-modal-text: ${this.settings.design.modal_text_color};
                --sfw-pointer: ${this.settings.wheel.pointer_color};
            }
        `;
        this.injectStyles(styleVars + this.assets.css);

        // Рендерим шаблон с переменными
        let html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{title}/g, this.escapeHtml(this.settings.design.title))
            .replace(/{description}/g, this.escapeHtml(this.settings.design.description))
            .replace(/{button_icon}/g, this.settings.button.icon)
            .replace(/{btn_text}/g, this.settings.button.text)
            .replace(/{position}/g, this.settings.button.position);

        this.createContainer(html, 'sfw-root');

        if (this.container) {
            this.bindEvents();
            this.drawWheel();
        }
    }

    bindEvents() {
        const toggleBtn = this.container.querySelector('[data-sp-toggle]');
        const closeEls = this.container.querySelectorAll('[data-sp-close]');
        const spinBtn = this.container.querySelector('.sfw-spin-trigger');

        if (toggleBtn) toggleBtn.onclick = () => this.openModal();

        closeEls.forEach(el => {
            el.onclick = (e) => {
                e.stopPropagation();
                this.closeModal();
            };
        });

        if (spinBtn) {
            spinBtn.onclick = () => this.startSpin();
        }
    }
// МЕТОД, КОТОРЫЙ ВЫЗЫВАЛ ОШИБКУ
    isLimitReached() {
        const frequency = this.settings.limits?.frequency || 'once_session';
        const stored = localStorage.getItem(this.storageKey) || sessionStorage.getItem(this.storageKey);

        if (!stored) return false;

        if (frequency === 'once_session' || frequency === 'once_forever') return true;

        // Для once_day (проверка 24 часа)
        if (frequency === 'once_day') {
            const lastSpin = parseInt(stored);
            return (Date.now() - lastSpin) < 86400000;
        }

        return false;
    }
    drawWheel() {
        const canvas = document.getElementById(`sfw-canvas-${this.id}`);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const segments = this.settings.wheel.segments || [];
        const size = this.settings.wheel.size || 300;

        canvas.width = size;
        canvas.height = size;
        const radius = size / 2;
        const arc = (2 * Math.PI) / segments.length;

        segments.forEach((seg, i) => {
            const angle = i * arc;
            ctx.beginPath();
            ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            ctx.moveTo(radius, radius);
            ctx.arc(radius, radius, radius - 10, angle, angle + arc);
            ctx.fill();

            // Текст сегмента
            ctx.save();
            ctx.translate(radius, radius);
            ctx.rotate(angle + arc / 2);
            ctx.textAlign = "right";
            ctx.fillStyle = this.settings.wheel.text_color;
            ctx.font = `bold ${this.settings.wheel.font_size}px sans-serif`;
            ctx.fillText(seg.label, radius - 35, 5);
            ctx.restore();
        });
    }

    startSpin() {
        if (this.isSpinning) return;

        // Проверка лимита перед вращением
        if (this.isLimitReached()) {
            alert(this.settings.messages.spin_limit_reached);
            return;
        }

        this.isSpinning = true;
        const canvas = document.getElementById(`sfw-canvas-${this.id}`);
        const segments = this.settings.wheel.segments;

        // Расчет случайного выигрыша
        const winIndex = Math.floor(Math.random() * segments.length);
        this.wonSegment = segments[winIndex];

        // Расчет угла: полные обороты + доворот до нужного сектора
        // (360 / кол-во сектора * индекс)
        const segmentDeg = 360 / segments.length;
        const rotationNeeded = (360 - (winIndex * segmentDeg)) - (segmentDeg / 2);
        const totalRotation = 1800 + rotationNeeded; // 5 оборотов + цель

        this.currentRotation += totalRotation;

        canvas.style.transition = `transform ${this.settings.wheel.rotation_speed || 4}s cubic-bezier(0.15, 0, 0.15, 1)`;
        canvas.style.transform = `rotate(${this.currentRotation}deg)`;

        this.track('spin_start');

        setTimeout(() => this.onSpinEnd(), (this.settings.wheel.rotation_speed || 4) * 1000);
    }

    onSpinEnd() {
        this.isSpinning = false;
        this.track('spin_win');

        // Записываем факт использования в storage
        this.saveSpinFactor();

        const formFields = document.getElementById(`sfw-form-fields-${this.id}`);
        if (formFields) {
            this.renderSuccessForm(formFields);
        }
    }

    renderSuccessForm(container) {
        if (!this.settings.form.enabled) {
            container.innerHTML = `<div class="sfw-win-msg">${this.wonSegment.label}</div>`;
            return;
        }

        // Рендерим заголовок и поля из конфига (settings.form.fields)
        let html = `<h4>${this.settings.form.title}</h4>`;
        html += `<p>Ваш приз: <strong>${this.wonSegment.label}</strong></p>`;

        this.settings.form.fields.forEach(field => {
            html += `<input type="${field.type}" name="${field.name}" placeholder="${field.placeholder}" required class="sfw-input">`;
        });

        html += `<button class="sfw-submit-btn">${this.settings.form.button_text}</button>`;
        container.innerHTML = html;

        // Событие отправки данных
        container.querySelector('.sfw-submit-btn').onclick = () => {
            this.submitLead(container);
        };
    }

    submitLead(container) {
        this.track('form_submit');
        // Подстановка кода купона в финальное сообщение
        const couponCode = this.wonSegment.value || 'PROMO';
        const msg = this.settings.form.success_message.replace('{CODE}', couponCode);
        container.innerHTML = `<div class="sfw-success-final">${msg}</div>`;
    }

    /**
     * Утилиты
     */
    openModal() {
        this.container.classList.add(this.activeClass);
        this.track('open');
    }

    closeModal() {
        this.container.classList.remove(this.activeClass);
    }

    saveSpinFactor() {
        const data = { timestamp: Date.now(), count: 1 };
        localStorage.setItem(this.storageKey, JSON.stringify(data));
    }

    shouldHide() {
        const stored = localStorage.getItem(this.storageKey);
        if (!stored) return false;

        const data = JSON.parse(stored);
        if (this.settings.limits.frequency === 'once_session') return true;
        // Можно добавить логику для once_day и т.д.
        return false;
    }

    mergeDefaults(settings, defaults) {
        let merged = { ...defaults };
        for (let key in settings) {
            if (typeof settings[key] === 'object' && !Array.isArray(settings[key])) {
                merged[key] = { ...defaults[key], ...settings[key] };
            } else {
                merged[key] = settings[key];
            }
        }
        return merged;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    injectStyles(css) {
        const styleId = `sm-style-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = css;
            document.head.appendChild(style);
        }
    }
};

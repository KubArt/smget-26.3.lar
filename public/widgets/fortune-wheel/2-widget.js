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
                rotation_speed: 4,
                text_color: '#333333',
                pointer_color: '#ff4444',
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

        this.activeClass = 'sp-active';
        this.isSpinning = false;
        this.wasSpun = false;


        // Глубокое слияние настроек
        this.settings = this.mergeDefaults(settings, this.defaults);
//
        this.storageKey = `sm_fortune_${this.id}`;
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
        // this.injectStyles();
        const design = this.settings.design || {};

        // Инжекция стилей
        const styleId = `sp-style-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                :root {
                    --bg-color: ${design.bg_color || '#FFFFFF'};
                    --text-color: ${design.text_color || '#1F2937'};
                    --accent-color: ${design.accent_color || '#3B82F6'};
                    --btn-color: ${design.btn_color || '#22C55E'};
                    --btn-text-color: ${design.btn_text_color || '#FFFFFF'};
                    --border-radius: ${design.border_radius || '16'};
                    --overlay-color: ${this.settings.overlay_color || 'rgba(0,0,0,0.7)'};
                }
                ${this.assets.css}
            `;
            document.head.appendChild(style);
        }

        // Подготовка данных для шаблона
        let html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{position}/g, this.settings.button?.position || 'right')
            .replace(/{title}/g, this.escapeHtml(this.settings.design?.title))
            .replace(/{description}/g, this.escapeHtml(this.settings.design?.description));

        this.container = this.createContainer(html, `sfw-root sp-position-${this.settings.button?.position || 'right'}`);
        document.body.appendChild(this.container);

        this.initCanvas();
        this.bindEvents();

        // Авто-открытие (если настроено)
        if (this.settings.button?.auto_open_delay > 0) {
            setTimeout(() => this.openModal(), this.settings.button.auto_open_delay * 1000);
        }
    }
    initCanvas() {
        this.canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.drawWheel(0);
    }

    bindEvents() {
        const trigger = this.container.querySelector('[data-sp-toggle]');
        const closeBtn = this.container.querySelector('[data-sp-close]');
        const spinBtn = this.container.querySelector('.sfw-spin-trigger');

        trigger?.addEventListener('click', () => this.toggle());
        closeBtn?.addEventListener('click', () => this.close());
        spinBtn?.addEventListener('click', () => this.startSpin());

        // Закрытие по оверлею
        this.container.querySelector('.sfw-overlay')?.addEventListener('click', (e) => {
            if (e.target.classList.contains('sfw-overlay')) this.close();
        });

        /*
        const spinBtn = this.container.querySelector('.sfw-spin-trigger');
        if (spinBtn) {
            const newSpin = spinBtn.cloneNode(true);
            spinBtn.parentNode.replaceChild(newSpin, spinBtn);
            newSpin.addEventListener('click', (e) => {
                e.preventDefault();
                this.startSpin();
            });
        }
        //*/

    }

    toggle() {
        this.container.classList.toggle(this.activeClass);
        if (this.container.classList.contains(this.activeClass)) {
            this.track('open_wheel');
            // Перерисовка при открытии для точности Canvas
            setTimeout(() => this.drawWheel(0), 100);
        }
    }

    close() {
        this.container.classList.remove(this.activeClass);
    }

    DEL_bindEvents() {
        // Кнопка открытия
        const toggleBtn = this.container.querySelector('[data-sp-toggle]');
        if (toggleBtn) {
            // Убираем старый обработчик, чтобы не было дублирования
            const newToggle = toggleBtn.cloneNode(true);
            toggleBtn.parentNode.replaceChild(newToggle, toggleBtn);
            newToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.openModal();
            });
        }

        // Кнопки закрытия (оверлей и крестик)
        const closeBtns = this.container.querySelectorAll('[data-sp-close]');
        closeBtns.forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeModal();
            });
        });

        // Кнопка Spin
        const spinBtn = this.container.querySelector('.sfw-spin-trigger');
        if (spinBtn) {
            const newSpin = spinBtn.cloneNode(true);
            spinBtn.parentNode.replaceChild(newSpin, spinBtn);
            newSpin.addEventListener('click', (e) => {
                e.preventDefault();
                this.startSpin();
            });
        }
    }

    isLimitReached() {
        const frequency = this.settings.limits?.frequency || 'once_session';
        const stored = localStorage.getItem(this.storageKey);

        if (!stored) return false;

        try {
            const data = JSON.parse(stored);
            if (frequency === 'once_forever') return true;
            if (frequency === 'once_session') return true;
            if (frequency === 'once_day') {
                return (Date.now() - data.timestamp) < 86400000;
            }
        } catch(e) {
            return false;
        }

        return false;
    }

    drawWheel() {
        const canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const segments = this.settings.wheel.segments || [];
        const size = this.settings.wheel.size || 300;

        if (segments.length === 0) return;

        canvas.width = size;
        canvas.height = size;
        const radius = size / 2;
        const centerX = radius;
        const centerY = radius;
        const arc = (2 * Math.PI) / segments.length;

        ctx.clearRect(0, 0, size, size);

        segments.forEach((seg, i) => {
            const startAngle = i * arc;
            const endAngle = startAngle + arc;

            // Рисуем сегмент
            ctx.beginPath();
            ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            ctx.fill();

            // Рисуем границу
            ctx.beginPath();
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 2;
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            ctx.lineTo(centerX, centerY);
            ctx.stroke();

            // Рисуем текст
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

        // Рисуем центральный круг
        ctx.beginPath();
        ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
        ctx.fillStyle = '#fff';
        ctx.fill();

        // Сохраняем canvas для вращения
        this.canvas = canvas;
    }

    startSpin() {
        if (this.isSpinning) return;

        // Проверка лимита перед вращением
        if (this.isLimitReached()) {
            alert(this.settings.messages.spin_limit_reached);
            return;
        }

        this.isSpinning = true;
        const segments = this.settings.wheel.segments;

        if (!segments || segments.length === 0) return;

        // Расчет случайного выигрыша
        const winIndex = Math.floor(Math.random() * segments.length);
        this.wonSegment = segments[winIndex];

        // Расчет угла: полные обороты + доворот до нужного сектора
        const segmentDeg = 360 / segments.length;
        const rotationNeeded = (360 - (winIndex * segmentDeg)) - (segmentDeg / 2);
        const totalRotation = 1440 + rotationNeeded; // 4 полных оборота

        this.currentRotation += totalRotation;

        this.canvas.style.transition = `transform ${this.settings.wheel.rotation_speed || 4}s cubic-bezier(0.25, 0.1, 0.15, 1)`;
        this.canvas.style.transform = `rotate(${this.currentRotation}deg)`;

        this.track('spin_start');

        setTimeout(() => this.onSpinEnd(), (this.settings.wheel.rotation_speed || 4) * 1000);
    }

    onSpinEnd() {
        this.isSpinning = false;
        this.track('spin_win');

        // Записываем факт использования в storage
        this.saveSpinFactor();

        const formContainer = this.container.querySelector(`#sfw-form-fields-${this.id}`);
        if (formContainer) {
            this.renderSuccessForm(formContainer);
        }
    }

    renderSuccessForm(container) {
        const wonSegment = this.wonSegment;

        if (!this.settings.form.enabled) {
            container.innerHTML = `<div class="sfw-win-msg">🎉 Вы выиграли: <strong>${this.escapeHtml(wonSegment.label)}</strong> 🎉</div>`;
            return;
        }

        // Рендерим заголовок и поля из конфига
        let html = `<h4 style="margin: 0 0 10px 0; font-size: 20px;">${this.escapeHtml(this.settings.form.title)}</h4>`;
        html += `<p style="margin-bottom: 20px;">Ваш приз: <strong>${this.escapeHtml(wonSegment.label)}</strong></p>`;

        (this.settings.form.fields || []).forEach(field => {
            html += `<input type="${field.type}" name="${field.name || 'field_' + Date.now()}" placeholder="${this.escapeHtml(field.placeholder || field.label)}" required class="sfw-input">`;
        });

        html += `<button class="sfw-submit-btn">${this.escapeHtml(this.settings.form.button_text)}</button>`;
        container.innerHTML = html;

        // Событие отправки данных
        const submitBtn = container.querySelector('.sfw-submit-btn');
        if (submitBtn) {
            const newSubmit = submitBtn.cloneNode(true);
            submitBtn.parentNode.replaceChild(newSubmit, submitBtn);
            newSubmit.addEventListener('click', () => {
                this.submitLead(container);
            });
        }
    }

    submitLead(container) {
        this.track('form_submit');
        // Подстановка кода купона в финальное сообщение
        const couponCode = this.wonSegment.value || 'PROMO2024';
        const msg = (this.settings.form.success_message || 'Ваш купон: {CODE}').replace('{CODE}', couponCode);
        container.innerHTML = `<div class="sfw-success-final">🎁 ${msg} 🎁</div>`;

        // Закрываем модалку через 2 секунды
        setTimeout(() => {
            this.closeModal();
            // Сбрасываем вращение для следующего раза
            setTimeout(() => this.resetWheel(), 300);
        }, 2000);
    }

    resetWheel() {
        this.currentRotation = 0;
        if (this.canvas) {
            this.canvas.style.transform = 'rotate(0deg)';
            this.canvas.style.transition = 'none';
        }

        // Восстанавливаем кнопку Spin
        const formContainer = this.container.querySelector(`#sfw-form-fields-${this.id}`);
        if (formContainer) {
            formContainer.innerHTML = `<button class="sfw-spin-trigger">${this.escapeHtml(this.settings.button.text || 'Крутить колесо')}</button>`;
            const newSpin = formContainer.querySelector('.sfw-spin-trigger');
            if (newSpin) {
                newSpin.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.startSpin();
                });
            }
        }
    }

    /**
     * Утилиты
     */
    openModal() {
        if (this.container) {
            this.container.classList.add(this.activeClass);
            this.track('open');
        }
    }

    closeModal() {
        if (this.container) {
            this.container.classList.remove(this.activeClass);
        }
    }

    saveSpinFactor() {
        const data = { timestamp: Date.now(), count: 1 };
        localStorage.setItem(this.storageKey, JSON.stringify(data));
    }

    shouldHide() {
        const stored = localStorage.getItem(this.storageKey);
        if (!stored) return false;

        try {
            const data = JSON.parse(stored);
            const frequency = this.settings.limits?.frequency || 'once_session';

            if (frequency === 'once_forever') return true;
            if (frequency === 'once_session') return true;
            if (frequency === 'once_day') {
                return (Date.now() - data.timestamp) < 86400000;
            }
        } catch(e) {
            return false;
        }

        return false;
    }

    mergeDefaults(settings, defaults) {
        let merged = JSON.parse(JSON.stringify(defaults));

        const deepMerge = (target, source) => {
            for (let key in source) {
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

window.SmWidget_lidup = class extends SmWidget {
    constructor(settings, id, assets) {
        super(settings, id, assets);
        this.storageKey = `sm_lidup_${this.id}_closed`;
        this.formSubmitted = false;
        this.timerInterval = null;
        this.exitIntentTriggered = false;
    }

    init() {
        // Проверка частоты показа
        if (this.shouldHide()) return;

        // Настройка триггера показа
        const trigger = this.settings.trigger_type || 'time';

        if (trigger === 'time') {
            setTimeout(() => this.mount(), (this.settings.delay || 3) * 1000);
        } else if (trigger === 'scroll') {
            this.initScrollTrigger();
        } else if (trigger === 'exit') {
            this.initExitIntent();
        } else if (trigger === 'click') {
            this.initClickTrigger();
        }
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

    initScrollTrigger() {
        const percent = this.settings.scroll_percent || 50;
        let triggered = false;

        const checkScroll = () => {
            if (triggered) return;
            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent >= percent) {
                triggered = true;
                this.mount();
                window.removeEventListener('scroll', checkScroll);
            }
        };

        window.addEventListener('scroll', checkScroll);
        setTimeout(checkScroll, 500);
    }

    initExitIntent() {
        if (!this.settings.exit_intent) return;

        document.addEventListener('mouseleave', (e) => {
            if (this.exitIntentTriggered) return;
            if (e.clientY <= 0) {
                this.exitIntentTriggered = true;
                this.mount();
            }
        });
    }

    initClickTrigger() {
        const selector = this.settings.click_selector || '#open-popup';
        const element = document.querySelector(selector);
        if (element) {
            element.addEventListener('click', (e) => {
                e.preventDefault();
                this.mount();
            });
        }
    }

    mount() {
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

        // Генерация полей формы
        const formFieldsHtml = this.generateFormFields();

        // Генерация изображения
        const imageHtml = this.settings.has_image && this.settings.image
            ? `<img src="${this.settings.image}" class="sp-lidup-image" alt="${this.settings.title}">`
            : '';

        // Таймер
        const hasTimer = this.settings.has_timer && this.settings.timer_target_date;
        const timerDisplay = hasTimer ? 'block' : 'none';

        // Подстановка в HTML
        let html = this.assets.html
            .replace(/\{title\}/g, this.escapeHtml(this.settings.title || ''))
            .replace(/\{description\}/g, this.escapeHtml(this.settings.description || ''))
            .replace(/\{image_html\}/g, imageHtml)
            .replace(/\{image_position\}/g, this.settings.image_position || 'left')
            .replace(/\{btn_text\}/g, this.escapeHtml(this.settings.btn_text || 'Отправить'))
            .replace(/\{timer_display\}/g, timerDisplay)
            .replace(/\{timer_title\}/g, this.escapeHtml(this.settings.timer_title || 'До конца акции осталось:'))
            .replace(/\{timer_days_text\}/g, this.escapeHtml(this.settings.timer_days_text || 'дней'))
            .replace(/\{timer_hours_text\}/g, this.escapeHtml(this.settings.timer_hours_text || 'часов'))
            .replace(/\{timer_minutes_text\}/g, this.escapeHtml(this.settings.timer_minutes_text || 'минут'))
            .replace(/\{timer_seconds_text\}/g, this.escapeHtml(this.settings.timer_seconds_text || 'секунд'))
            .replace(/\{size\}/g, this.settings.size || 'medium')
            .replace(/\{position\}/g, this.settings.position || 'center')
            .replace(/\{animation_in\}/g, this.settings.animation_in || 'fadeIn');

        this.createContainer(html);

        if (this.container) {
            // Вставляем поля формы
            const formFieldsContainer = this.container.querySelector('#sp-form-fields');
            if (formFieldsContainer) {
                formFieldsContainer.innerHTML = formFieldsHtml;
            }

            // Запускаем таймер
            if (hasTimer) {
                this.startTimer();
            }

            // Настраиваем обработчики
            this.bindEvents();

            // Показываем с анимацией
            setTimeout(() => {
                this.container.classList.add('sp-active');
            }, 50);

            // Авто-закрытие
            if (this.settings.auto_close > 0) {
                setTimeout(() => this.close(), this.settings.auto_close * 1000);
            }

            this.track('view');
        }
    }

    generateFormFields() {
        const fields = this.settings.form_fields || [];
        return fields.map(field => {
            const required = field.required ? 'required' : '';
            return `
                <input type="${field.type}"
                       name="${field.name}"
                       placeholder="${this.escapeHtml(field.placeholder || field.label)}"
                       ${required}
                       data-sp-field>
            `;
        }).join('');
    }

    startTimer() {
        const targetDate = new Date(this.settings.timer_target_date).getTime();
        if (isNaN(targetDate)) return;

        const daysEl = this.container.querySelector('.sp-timer-days');
        const hoursEl = this.container.querySelector('.sp-timer-hours');
        const minutesEl = this.container.querySelector('.sp-timer-minutes');
        const secondsEl = this.container.querySelector('.sp-timer-seconds');

        if (!daysEl) return;

        this.timerInterval = setInterval(() => {
            const now = Date.now();
            const diff = targetDate - now;

            if (diff <= 0) {
                clearInterval(this.timerInterval);
                daysEl.textContent = '00';
                hoursEl.textContent = '00';
                minutesEl.textContent = '00';
                secondsEl.textContent = '00';
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (86400000)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (3600000)) / (1000 * 60));
            const seconds = Math.floor((diff % (60000)) / 1000);

            daysEl.textContent = days.toString().padStart(2, '0');
            hoursEl.textContent = hours.toString().padStart(2, '0');
            minutesEl.textContent = minutes.toString().padStart(2, '0');
            secondsEl.textContent = seconds.toString().padStart(2, '0');
        }, 1000);
    }

    bindEvents() {
        // Закрытие
        const closeBtn = this.container.querySelector('[data-sp-close]');
        const overlay = this.container.querySelector('[data-sp-overlay]');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }
        if (overlay) {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) this.close();
            });
        }

        // Отправка формы
        const form = this.container.querySelector('[data-sp-form]');
        if (form) {
            form.addEventListener('submit', (e) => this.submitForm(e));
        }
    }

    async submitForm(e) {
        e.preventDefault();
        if (this.formSubmitted) return;

        const form = e.target;
        const fields = form.querySelectorAll('[data-sp-field]');
        const formData = {};

        fields.forEach(field => {
            formData[field.name] = field.value;
        });

        // Добавляем метаданные
        formData.widget_id = this.id;
        formData.url = window.location.href;

        const submitBtn = form.querySelector('.sp-lidup-submit');
        const messageEl = form.querySelector('.sp-lidup-message');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка...';

        try {
            const webhookUrl = this.settings.webhook_url;
            if (webhookUrl) {
                await fetch(webhookUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
            }

            this.track('submit');
            this.formSubmitted = true;

            messageEl.textContent = this.settings.success_message || 'Спасибо! Мы свяжемся с вами.';
            messageEl.className = 'sp-lidup-message success';
            messageEl.style.display = 'block';

            form.innerHTML = messageEl.outerHTML;

            setTimeout(() => this.close(), 2000);

        } catch (error) {
            console.error('Submit error:', error);
            messageEl.textContent = this.settings.error_message || 'Ошибка. Попробуйте позже.';
            messageEl.className = 'sp-lidup-message error';
            messageEl.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = this.settings.btn_text || 'Отправить';
        }
    }

    close() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }

        const timestamp = Date.now();
        const closeBehavior = this.settings.close_behavior || 'hide_session';

        if (closeBehavior === 'hide_forever') {
            localStorage.setItem(this.storageKey, timestamp.toString());
        } else {
            sessionStorage.setItem(this.storageKey, timestamp.toString());
        }

        this.track('close');

        if (this.container) {
            this.container.classList.remove('sp-active');
            setTimeout(() => {
                if (this.container) this.container.remove();
            }, 300);
        }
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

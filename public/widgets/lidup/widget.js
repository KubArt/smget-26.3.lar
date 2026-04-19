window.SmWidget_lidup = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);
        this.storageKey = `sm_lidup_${this.id}_closed`;
        this.formSubmitted = false;
        this.exitIntentTriggered = false;
    }

    init() {
        super._init();
        /*
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
        //*/
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
            ? `<img src="${this.settings.image}" class="sp-lidup-image" alt="${this.escapeHtml(this.settings.title)}">`
            : '';

        // Подстановка в HTML
        let html = this.assets.html
            .replace(/\{title\}/g, this.escapeHtml(this.settings.title || ''))
            .replace(/\{description\}/g, this.escapeHtml(this.settings.description || ''))
            .replace(/\{image_html\}/g, imageHtml)
            .replace(/\{image_position\}/g, this.settings.image_position || 'left')
            .replace(/\{btn_text\}/g, this.escapeHtml(this.settings.btn_text || 'Отправить'))
            .replace(/\{position\}/g, this.settings.position || 'center')
            .replace(/\{animation_in\}/g, this.settings.animation_in || 'fadeIn');

        this.createContainer(html);

        if (this.container) {
            // Вставляем поля формы
            const formFieldsContainer = this.container.querySelector('#sp-form-fields');
            if (formFieldsContainer) {
                formFieldsContainer.innerHTML = formFieldsHtml;
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
            const name = field.name || field.type + '_' + Date.now() + '_' + Math.random();
            const placeholder = this.escapeHtml(field.placeholder || field.label || '');
            const defaultValue = this.escapeHtml(field.default_value || '');

            // Скрытое поле
            if (field.type === 'hidden') {
                return `<input type="hidden" name="${name}" value="${defaultValue}">`;
            }

            // Текстовая область
            if (field.type === 'textarea') {
                return `<textarea name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field"></textarea>`;
            }

            // Обычное поле
            return `<input type="${field.type}" name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field">`;
        }).join('');
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
        const fields = form.querySelectorAll('input, textarea, select');
        const formData = {};

        fields.forEach(field => {
            if (field.name) {
                formData[field.name] = field.value;
            }
        });

        // Добавляем метаданные
        formData.widget_id = this.id;
        formData.url = window.location.href;
        formData.timestamp = new Date().toISOString();

        const submitBtn = form.querySelector('.sp-lidup-submit');
        const messageEl = form.querySelector('.sp-lidup-message');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Отправка...';

        try {
            // Отправка данных через fetch на ваш API endpoint
            const apiUrl = this.settings.api_url || '/api/widgets/lidup/submit';

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            });

            this.track('submit');
            this.formSubmitted = true;

            const result = await response.json();

            messageEl.textContent = result.message || this.settings.success_message || 'Спасибо! Мы свяжемся с вами.';
            messageEl.className = 'sp-lidup-message success';
            messageEl.style.display = 'block';

            // Очищаем форму и показываем сообщение
            form.innerHTML = '';
            form.appendChild(messageEl);

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

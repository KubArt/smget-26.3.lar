/**
 * Виджет "LidUp" - Оптимизированная версия
 */
window.SmWidget_lidup = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        const design = settings.design || {};

        // 1. Централизованная конфигурация
        this.config = {
            title: settings.title || '',
            description: settings.description || '',
            btnText: settings.btn_text || 'Отправить',
            image: settings.image || '',
            hasImage: !!(settings.has_image && settings.image),
            imgPos: settings.image_position || 'left',
            position: settings.position || 'center',
            animIn: settings.animation_in || 'fadeIn',
            apiUrl: settings.api_url || '/api/widgets/lidup/submit',
            fields: settings.form_fields || [],
            // Дизайн
            colors: {
                bg: design.bg_color || '#FFFFFF',
                text: design.text_color || '#1F2937',
                accent: design.accent_color || '#3B82F6',
                btn: design.btn_color || '#22C55E',
                btnText: design.btn_text_color || '#FFFFFF',
                overlay: settings.overlay_color || 'rgba(0,0,0,0.7)',
                radius: design.border_radius || '16'
            }
        };

        this.formSubmitted = false;
    }

    init() {
        super._init();
    }

    mount() {
        this.injectStyles();

        // 2. Подготовка контента
        const imageHtml = this.config.hasImage
            ? `<img src="${this.config.image}" class="sp-lidup-image" alt="image">`
            : '';

        const replacements = {
            '{title}': this.escapeHtml(this.config.title),
            '{description}': this.escapeHtml(this.config.description),
            '{image_html}': imageHtml,
            '{image_position}': this.config.imgPos,
            '{btn_text}': this.escapeHtml(this.config.btnText),
            '{position}': this.config.position,
            '{animation_in}': this.config.animIn
        };

        let html = this.assets.html;
        for (const [key, val] of Object.entries(replacements)) {
            html = html.split(key).join(val);
        }

        // 3. Создание и рендер
        this.createContainer(html);

        if (this.container) {
            const formFieldsContainer = this.container.querySelector('#sp-form-fields');
            if (formFieldsContainer) {
                formFieldsContainer.innerHTML = this.renderFields();
            }

            this.bindEvents();

            // Плавное появление
            requestAnimationFrame(() => {
                this.container.classList.add('sp-active');
            });
        }
    }

    renderFields() {
        return this.config.fields.map(field => {
            const req = field.required ? 'required' : '';
            const name = field.name || `${field.type}_${Date.now()}`;
            const placeholder = this.escapeHtml(field.placeholder || field.label || '');
            const val = this.escapeHtml(field.default_value || '');

            if (field.type === 'hidden') return `<input type="hidden" name="${name}" value="${val}">`;

            if (field.type === 'textarea') {
                return `<textarea name="${name}" placeholder="${placeholder}" ${req} class="sp-lidup-field"></textarea>`;
            }

            return `<input type="${field.type}" name="${name}" placeholder="${placeholder}" ${req} class="sp-lidup-field">`;
        }).join('');
    }

    injectStyles() {
        const c = this.config.colors;
        const fullCss = `
        :root {
                --bg-color: ${c.bg};
                --text-color: ${c.text};
                --accent-color: ${c.accent};
                --btn-color: ${c.btn};
                --btn-text-color: ${c.btnText};
                --border-radius: ${c.radius}px;
                --overlay-color: ${c.overlay};
            }
            ${this.assets.css}
        `;
        // 2. Вызываем метод ядра, который сам проверит ID и добавит стиль в head
        this.injectCustomStyles(fullCss);
    }

    bindEvents() {
        this.container.onclick = (e) => {
            if (e.target.hasAttribute('data-sp-close') || e.target.hasAttribute('data-sp-overlay')) {
                this.close();
            }
        };

        const form = this.container.querySelector('[data-sp-form]');
        if (form) {
            form.onsubmit = (e) => this.handleSubmit(e);
        }
    }

    async handleSubmit(e) {
        e.preventDefault();
        if (this.formSubmitted) return;

        const form = e.target;
        const submitBtn = form.querySelector('.sp-lidup-submit');
        const messageEl = form.querySelector('.sp-lidup-message');

        // Сбор данных через FormData (более современно)
        const formData = Object.fromEntries(new FormData(form));
        formData.widget_id = this.id;
        formData.url = window.location.href;

        submitBtn.disabled = true;
        const originalBtnText = submitBtn.textContent;
        submitBtn.textContent = 'Отправка...';

        try {
            const response = await fetch(this.config.apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(formData)
            });

            if (!response.ok) throw new Error();

            this.formSubmitted = true;
            this.track('submit');

            const result = await response.json();
            messageEl.textContent = result.message || this.settings.success_message || 'Спасибо!';
            messageEl.className = 'sp-lidup-message success';
            messageEl.style.display = 'block';

            form.style.opacity = '0';
            setTimeout(() => {
                form.innerHTML = '';
                form.appendChild(messageEl);
                form.style.opacity = '1';
            }, 300);

            setTimeout(() => this.close(), 2500);

        } catch (err) {
            messageEl.textContent = this.settings.error_message || 'Ошибка. Попробуйте позже.';
            messageEl.className = 'sp-lidup-message error';
            messageEl.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    }

    close() {
        this.saveClosed();
        this.track('close');

        if (this.container) {
            this.container.classList.remove('sp-active');
            setTimeout(() => {
                if (this.container) this.container.remove();
                this.container = null;
            }, 300);
        }
    }
};

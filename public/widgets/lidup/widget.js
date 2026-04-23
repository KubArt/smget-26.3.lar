/**
 * http://smget-26.3.lar
 *
 * Виджет "LidUp" - Оптимизированная версия
 */

/**
 * Виджет "LidUp" - Оптимизированная версия
 */
window.SmWidget_lidup = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        const design = settings.design || {};
        const bonus = settings.bonus || {};

        this.config = {
            title: settings.title || '',
            description: settings.description || '',
            btnText: settings.btn_text || 'Отправить',
            successMessage: settings.success_message || 'Спасибо! Мы свяжемся с вами.',
            errorMessage: settings.error_message || 'Ошибка. Попробуйте позже.',
            image: settings.image || '',
            hasImage: !!(settings.has_image && settings.image),
            imgPos: settings.image_position || 'left',
            position: settings.position || 'center',
            animIn: settings.animation_in || 'fadeIn',
            apiUrl: settings.api_url || 'http://smget-26.3.lar/api/v1/capture/lidup',
            fields: settings.form_fields || [],
            bonus: {
                enabled: bonus.enabled || false,
            },
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

        this.createContainer(html);

        if (this.container) {
            const formFieldsContainer = this.container.querySelector('#sp-form-fields');
            if (formFieldsContainer) {
                formFieldsContainer.innerHTML = this.renderFields();
            }

            this.bindEvents();

            requestAnimationFrame(() => {
                this.container.classList.add('sp-active');
            });
        }
    }

    renderFields() {
        return this.config.fields.map(field => {
            const required = field.required ? 'required' : '';
            const name = field.name || `${field.type}_${Date.now()}`;
            const placeholder = this.escapeHtml(field.placeholder || field.label || '');
            const value = this.escapeHtml(field.default_value || '');

            if (field.type === 'hidden') {
                return `<input type="hidden" name="${name}" value="${value}">`;
            }
            if (field.type === 'textarea') {
                return `<textarea name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field"></textarea>`;
            }
            return `<input type="${field.type}" name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field">`;
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
        this.injectCustomStyles(fullCss);
    }

    setState(state) {
        const states = ['form', 'success', 'bonus'];
        states.forEach(s => {
            const el = this.container?.querySelector(`.sp-lidup-state-${s}`);
            if (el) el.style.display = s === state ? 'block' : 'none';
        });
    }

    setButtonLoading(isLoading) {
        const submitBtn = this.container?.querySelector('.sp-lidup-submit');
        if (!submitBtn) return;

        if (isLoading) {
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<span class="spinner"></span> Отправка...';
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = this.escapeHtml(this.config.btnText);
        }
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

        const isValid = this.validateForm(form);
        if (!isValid) {
            this.showFieldError(form);
            return;
        }

        const formData = new FormData(form);
        const payload = {
            widget_id: this.id,
            page_url: window.location.href,
            utm_source: this.getCookie('utm_source'),
            utm_medium: this.getCookie('utm_medium'),
            utm_campaign: this.getCookie('utm_campaign'),
            utm_term: this.getCookie('utm_term'),
            utm_content: this.getCookie('utm_content'),
        };

        for (let [key, value] of formData.entries()) {
            payload[key] = value;
        }

        this.formSubmitted = true;
        this.setButtonLoading(true);

        try {
            const response = await fetch(this.config.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Api-Key': 'widget',
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Ошибка отправки');
            }

            this.track('submit');

            // Скрываем заголовок и описание при успехе
            const titleEl = this.container?.querySelector('.sp-lidup-title');
            const descEl = this.container?.querySelector('.sp-lidup-description');
            if (titleEl) titleEl.style.display = 'none';
            if (descEl) descEl.style.display = 'none';

            // Проверяем, есть ли приз (prize с code)
            if (data.prize && data.prize.code) {
                this.showBonus(data);
            } else {
                this.showSuccess(data.message || this.config.successMessage);
            }

        } catch (err) {
            console.error('Submit error:', err);
            this.formSubmitted = false;
            this.setButtonLoading(false);
            this.showFormError(err.message || this.config.errorMessage);
        }
    }

    showSuccess(message) {
        const successBlock = this.container?.querySelector('.sp-lidup-state-success');
        const textEl = successBlock?.querySelector('.success-text');
        if (textEl) textEl.textContent = message;
        this.setState('success');
    }

    showBonus(data) {
        const prize = data.prize;
        const bonusMessage = data.message || prize.user_message;
        const expiresDate = prize.expires_at ? new Date(prize.expires_at).toLocaleDateString() : null;

        const bonusBlock = this.container?.querySelector('.sp-lidup-state-bonus');

        const titleEl = bonusBlock?.querySelector('.bonus-title');
        const codeEl = bonusBlock?.querySelector('.code-value');
        const descEl = bonusBlock?.querySelector('.bonus-description');
        const expiresEl = bonusBlock?.querySelector('.bonus-expires');
        const messageEl = bonusBlock?.querySelector('.bonus-message');

        if (titleEl) titleEl.textContent = prize.name || 'Поздравляем!';
        if (codeEl) codeEl.textContent = prize.code;
        if (descEl && prize.description) descEl.textContent = prize.description;
        if (expiresEl && expiresDate) expiresEl.textContent = `🎫 Действителен до: ${expiresDate}`;
        if (messageEl && bonusMessage) messageEl.textContent = `✨ ${bonusMessage}`;

        this.setState('bonus');
    }

    showFormError(message) {
        const messageEl = this.container?.querySelector('.sp-lidup-message');
        if (messageEl) {
            messageEl.textContent = message;
            messageEl.className = 'sp-lidup-message error';
            messageEl.style.display = 'block';
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 3000);
        }
    }

    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('sp-error');
                isValid = false;
            } else {
                field.classList.remove('sp-error');
            }

            if (field.type === 'email' && field.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value.trim())) {
                    field.classList.add('sp-error');
                    isValid = false;
                }
            }
        });

        return isValid;
    }

    showFieldError(form) {
        const firstError = form.querySelector('.sp-error');
        if (firstError) {
            firstError.focus();
            firstError.style.animation = 'shake 0.3s ease';
            setTimeout(() => {
                firstError.style.animation = '';
            }, 300);
        }

        this.showFormError('Пожалуйста, заполните все обязательные поля корректно');
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    injectCustomStyles(css) {
        const styleId = `sm-style-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = css;
            document.head.appendChild(style);
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

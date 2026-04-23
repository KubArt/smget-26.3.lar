        @push('js')
            <script>

                function lidupEditor(config) {
                    return {
                        slug: config.slug,
                        settings: config.settings,
                        skins: config.skins,
                        rawTemplate: '',
                        rawCss: '',
                        shadowRoot: null,
                        widgetRoot: null,

                        async init() {
                            // Инициализация настроек по умолчанию
                            if (!this.settings) this.settings = {};

                            // Дефолтные поля формы (имя + телефон)
                            if (!this.settings.form_fields || this.settings.form_fields.length === 0) {
                                this.settings.form_fields = [
                                    { type: 'text', name: 'name', label: 'Ваше имя', placeholder: 'Иван Иванов', required: true },
                                    { type: 'tel', name: 'phone', label: 'Телефон', placeholder: '+7 (999) 123-45-67', required: true }
                                ];
                            }

                            if (!this.settings.design) {
                                this.settings.design = {
                                    bg_color: '#FFFFFF',
                                    text_color: '#1F2937',
                                    accent_color: '#3B82F6',
                                    btn_color: '#22C55E',
                                    btn_text_color: '#FFFFFF',
                                    border_radius: '16'
                                };
                            }

                            // Базовые настройки
                            if (!this.settings.trigger_type) this.settings.trigger_type = 'time';
                            if (!this.settings.delay) this.settings.delay = 3;
                            if (!this.settings.scroll_percent) this.settings.scroll_percent = 50;
                            if (!this.settings.frequency) this.settings.frequency = 'once_session';
                            if (!this.settings.close_behavior) this.settings.close_behavior = 'hide_session';
                            if (!this.settings.auto_close) this.settings.auto_close = 0;
                            if (!this.settings.position) this.settings.position = 'center';
                            if (!this.settings.animation_in) this.settings.animation_in = 'fadeIn';
                            if (!this.settings.overlay_color) this.settings.overlay_color = 'rgba(0,0,0,0.7)';
                            if (!this.settings.btn_text) this.settings.btn_text = 'Отправить заявку';
                            if (!this.settings.success_message) this.settings.success_message = 'Спасибо! Мы свяжемся с вами.';
                            if (!this.settings.title) this.settings.title = 'Получите скидку 20%';
                            if (!this.settings.template) this.settings.template = Object.keys(this.skins)[0] || 'default';

                            await this.loadSkin(this.settings.template);

                            // Отдельные watchers для каждого поля (чтобы не обновлять весь preview)
                            this.$watch('settings.title', () => this.updateContent());
                            this.$watch('settings.description', () => this.updateContent());
                            this.$watch('settings.has_image', () => this.updateContent());
                            this.$watch('settings.image', () => this.updateContent());
                            this.$watch('settings.image_position', () => this.updateContent());
                            this.$watch('settings.btn_text', () => this.updateContent());
                            this.$watch('settings.form_fields', () => this.updateFormFields(), { deep: true });
                            this.$watch('settings.position', () => this.updatePosition());
                            this.$watch('settings.animation_in', () => this.updateAnimation());
                            this.$watch('settings.design', () => this.updateColors(), { deep: true });
                            this.$watch('settings.overlay_color', () => this.updateColors());
                        },


                        switchTab(tabId) {
                            this.activeTab = tabId;
                            // Скрыть все панели
                            document.querySelectorAll('[data-pane]').forEach(pane => {
                                pane.style.display = 'none';
                            });
                            // Показать выбранную
                            const activePane = document.querySelector(`[data-pane="${tabId}"]`);
                            if (activePane) activePane.style.display = 'block';
                            // Обновить активный класс вкладок
                            document.querySelectorAll('[data-tab]').forEach(tab => {
                                tab.classList.remove('active');
                            });
                            const activeTab = document.querySelector(`[data-tab="${tabId}"]`);
                            if (activeTab) activeTab.classList.add('active');
                        },

                        generateBonusCode() {
                            const prefixes = ['PROMO', 'BONUS', 'GIFT', 'WELCOME', 'SPECIAL'];
                            const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
                            const suffix = Math.random().toString(36).substring(2, 8).toUpperCase();
                            this.settings.bonus.code = prefix + '_' + suffix;
                        },

                        async loadSkin(skinId) {
                            try {
                                const baseUrl = `/widgets/${this.slug}/skins/${skinId}`;
                                const [htmlRes, cssRes] = await Promise.all([
                                    fetch(`${baseUrl}/template.html`),
                                    fetch(`${baseUrl}/style.css`)
                                ]);
                                this.rawTemplate = await htmlRes.text();
                                this.rawCss = await cssRes.text();
                                this.initPreview();
                                this.updatePreview();
                            } catch (e) {
                                console.error('Error loading skin:', e);
                            }
                        },

                        initPreview() {
                            const container = document.getElementById('preview-host');
                            if (!container) return;

                            this.shadowRoot = container.shadowRoot || container.attachShadow({ mode: 'open' });

                            let css = this.rawCss;
                            css = css.replace(/position:\s*fixed/g, 'position: absolute');
                            css = css.replace(/position:fixed/g, 'position: absolute');
                            css = css.replace(/100vh/g, '100%');
                            css = css.replace(/100vw/g, '100%');

                            this.shadowRoot.innerHTML = `
                                <style>
                                    :host {
                                        display: block;
                                        position: absolute;
                                        top: 0;
                                        left: 0;
                                        right: 0;
                                        bottom: 0;
                                    }
                                    ${css}
                                </style>
                                <div id="widget-root"></div>
                            `;
                            this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                        },

                        updatePreview() {
                            if (!this.widgetRoot || !this.rawTemplate) return;

                            // Генерация изображения
                            const imageHtml = this.settings.has_image && this.settings.image
                                ? `<img src="${this.settings.image}" class="sp-lidup-image" alt="${this.escapeHtml(this.settings.title)}">`
                                : '';

                            let html = this.rawTemplate
                                .replace(/\{title\}/g, this.escapeHtml(this.settings.title || ''))
                                .replace(/\{description\}/g, this.escapeHtml(this.settings.description || ''))
                                .replace(/\{image_html\}/g, imageHtml)
                                .replace(/\{image_position\}/g, this.settings.image_position || 'left')
                                .replace(/\{btn_text\}/g, this.escapeHtml(this.settings.btn_text || 'Отправить'))
                                .replace(/\{position\}/g, this.settings.position || 'center')
                                .replace(/\{animation_in\}/g, this.settings.animation_in || 'fadeIn');

                            this.widgetRoot.innerHTML = html;

                            const widget = this.widgetRoot.firstElementChild;
                            if (widget) {
                                this.applyColors(widget);
                                this.updateFormFields();

                                // Показываем попап в предпросмотре
                                setTimeout(() => widget.classList.add('sp-active'), 100);
                            }
                        },

                        updateContent() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            // Обновляем заголовок и описание
                            const titleEl = widget.querySelector('.sp-lidup-title');
                            const descEl = widget.querySelector('.sp-lidup-description');
                            const btnEl = widget.querySelector('.sp-lidup-submit');
                            const imageContainer = widget.querySelector('.sp-lidup-content');

                            if (titleEl) titleEl.textContent = this.settings.title || '';
                            if (descEl) descEl.textContent = this.settings.description || '';
                            if (btnEl) btnEl.textContent = this.settings.btn_text || 'Отправить';

                            // Обновляем изображение
                            if (imageContainer) {
                                const oldImage = imageContainer.querySelector('.sp-lidup-image');
                                if (oldImage) oldImage.remove();

                                if (this.settings.has_image && this.settings.image) {
                                    const img = document.createElement('img');
                                    img.src = this.settings.image;
                                    img.className = 'sp-lidup-image';
                                    img.alt = this.settings.title || '';
                                    imageContainer.insertBefore(img, imageContainer.firstChild);
                                }

                                // Обновляем класс позиции изображения
                                imageContainer.classList.remove('sp-image-left', 'sp-image-right', 'sp-image-top', 'sp-image-bottom');
                                imageContainer.classList.add(`sp-image-${this.settings.image_position || 'left'}`);
                            }
                        },

                        updateFormFields() {
                            if (!this.widgetRoot) return;
                            const formFieldsContainer = this.widgetRoot.querySelector('#sp-form-fields');
                            if (!formFieldsContainer) return;

                            const fields = this.settings.form_fields || [];
                            const fieldsHtml = fields.map(field => {
                                const required = field.required ? 'required' : '';
                                const placeholder = this.escapeHtml(field.placeholder || field.label || '');
                                const name = field.name || field.type + '_' + Date.now() + '_' + Math.random();

                                if (field.type === 'hidden') {
                                    return `<input type="hidden" name="${name}" value="${this.escapeHtml(field.default_value || '')}">`;
                                }
                                if (field.type === 'textarea') {
                                    return `<textarea name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field"></textarea>`;
                                }
                                return `<input type="${field.type}" name="${name}" placeholder="${placeholder}" ${required} class="sp-lidup-field">`;
                            }).join('');

                            formFieldsContainer.innerHTML = fieldsHtml;
                        },

                        updatePosition() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            const popup = widget.querySelector('.sp-lidup-popup');
                            if (popup) {
                                popup.classList.remove('sp-position-center', 'sp-position-top', 'sp-position-bottom');
                                popup.classList.add(`sp-position-${this.settings.position || 'center'}`);
                            }
                        },

                        updateAnimation() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            widget.classList.remove('sp-fadeIn', 'sp-slideInUp', 'sp-slideInDown', 'sp-zoomIn');
                            widget.classList.add(`sp-${this.settings.animation_in || 'fadeIn'}`);
                        },

                        applyColors(widget) {
                            const design = this.settings.design || {};
                            widget.style.setProperty('--bg-color', design.bg_color || '#FFFFFF');
                            widget.style.setProperty('--text-color', design.text_color || '#1F2937');
                            widget.style.setProperty('--accent-color', design.accent_color || '#3B82F6');
                            widget.style.setProperty('--btn-color', design.btn_color || '#22C55E');
                            widget.style.setProperty('--btn-text-color', design.btn_text_color || '#FFFFFF');
                            widget.style.setProperty('--border-radius', design.border_radius || '16');
                            widget.style.setProperty('--overlay-color', this.settings.overlay_color || 'rgba(0,0,0,0.7)');
                        },

                        updateColors() {
                            if (!this.widgetRoot) return;
                            const widget = this.widgetRoot.firstElementChild;
                            if (widget) this.applyColors(widget);
                        },

                        async applyTemplate(skinId) {
                            if (this.settings.template === skinId) return;
                            this.settings.template = skinId;
                            await this.loadSkin(skinId);
                        },

                        addFormField() {
                            if (!this.settings.form_fields) this.settings.form_fields = [];
                            this.settings.form_fields.push({
                                type: 'text',
                                name: 'field_' + Date.now(),
                                label: 'Новое поле',
                                placeholder: 'Введите значение',
                                required: false
                            });
                        },

                        removeFormField(index) {
                            this.settings.form_fields.splice(index, 1);
                        },

                        escapeHtml(str) {
                            if (!str) return '';
                            const div = document.createElement('div');
                            div.textContent = str;
                            return div.innerHTML;
                        },

                        saveConfig() {
                            const btn = event.currentTarget;
                            const original = btn.innerHTML;
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                            axios.post(window.location.href, { settings: this.settings })
                                .then(response => {
                                    if (response.data.status === 'success') {
                                        if (typeof showNotification === 'function') {
                                            showNotification(response.data.message, 'success');
                                        } else {
                                            alert(response.data.message);
                                        }
                                    }
                                })
                                .catch(error => {
                                    const msg = error.response?.data?.message || 'Ошибка при сохранении';
                                    if (typeof showNotification === 'function') {
                                        showNotification(msg, 'danger');
                                    } else {
                                        alert(msg);
                                    }
                                })
                                .finally(() => {
                                    btn.disabled = false;
                                    btn.innerHTML = original;
                                });
                        }
                    };
                }
            </script>
    @endpush

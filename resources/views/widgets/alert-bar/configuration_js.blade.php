    @push('js')
        <script>
            function alertBarEditor(config) {
                return {
                    slug: config.slug,
                    settings: config.settings,
                    skins: config.skins,
                    rawTemplate: '',
                    rawCss: '',
                    shadowRoot: null,
                    widgetRoot: null,
                    mediaObserver: null,

                    async init() {
                        // Инициализация структуры контента
                        if (!this.settings) {
                            this.settings = {
                                text: 'Скидка -20% на первичный прием до конца недели!',
                                link: 'https://smile-center.ru/promo',
                                btn_text: 'Узнать больше',
                                has_button: true,
                                position: 'top',
                                design: {
                                    bg_color: '#E63946',
                                    text_color: '#FFFFFF',
                                    btn_color: '#1D3557'
                                },
                                delay: 0,
                                auto_hide: 0,
                                scroll_trigger: 0,
                                frequency: 'once_session',
                                close_behavior: 'hide_session'
                            };
                        }

                        if (!this.settings.design) {
                            this.settings.design = {
                                bg_color: '#E63946',
                                text_color: '#FFFFFF',
                                btn_color: '#1D3557'
                            };
                        }

                        // Загружаем скин (по умолчанию 'default')
                        await this.loadSkin('default');

                        // Watchers - все вызывают updatePreview()
                        this.$watch('settings.text', () => this.updatePreview());
                        this.$watch('settings.btn_text', () => this.updatePreview());
                        this.$watch('settings.link', () => this.updatePreview());
                        this.$watch('settings.has_button', () => this.updatePreview());
                        this.$watch('settings.position', () => this.updatePreview());
                        this.$watch('settings.design.bg_color', () => this.updateColors());
                        this.$watch('settings.design.text_color', () => this.updateColors());
                        this.$watch('settings.design.btn_color', () => this.updateColors());
                    },

                    async loadSkin(skinId) {
                        const skinUrl = `/widgets/${this.slug}/skins/${skinId}`;
                        const baseUrl = `/widgets/${this.slug}`;

                        try {
                            let tplResponse, cssResponse;

                            // Сначала пробуем загрузить из папки скина
                            tplResponse = await fetch(`${skinUrl}/template.html`);
                            cssResponse = await fetch(`${skinUrl}/style.css`);

                            // Если файлы скина не найдены, пробуем из корня виджета
                            if (!tplResponse.ok) {
                                console.warn(`Skin template not found in ${skinUrl}, trying root...`);
                                tplResponse = await fetch(`${baseUrl}/template.html`);
                            }

                            if (!cssResponse.ok) {
                                console.warn(`Skin CSS not found in ${skinUrl}, trying root...`);
                                cssResponse = await fetch(`${baseUrl}/style.css`);
                            }

                            if (!tplResponse.ok) {
                                throw new Error(`Template not found: ${tplResponse.url} (${tplResponse.status})`);
                            }
                            if (!cssResponse.ok) {
                                throw new Error(`CSS not found: ${cssResponse.url} (${cssResponse.status})`);
                            }

                            this.rawTemplate = await tplResponse.text();
                            this.rawCss = await cssResponse.text();

                            if (!this.rawTemplate.trim()) {
                                throw new Error('Template file is empty');
                            }

                            this.setupShadowDOM();
                            this.updatePreview();

                        } catch (e) {
                            console.error('Error loading skin:', e);
                            this.showError(`Не удалось загрузить шаблон "${skinId}": ${e.message}`);
                        }
                    },

                    setupShadowDOM() {
                        const container = document.getElementById('preview-host');
                        if (!container) return;

                        this.shadowRoot = container.shadowRoot || container.attachShadow({mode: 'open'});

                        let modifiedCSS = this.rawCss;
                        modifiedCSS = modifiedCSS.replace(/position:\s*fixed/g, 'position: absolute');
                        modifiedCSS = modifiedCSS.replace(/position:fixed/g, 'position: absolute');

                        // Добавляем принудительно стиль для hidden-btn
                        const hiddenBtnStyle = `
                                .hidden-btn {
                                    display: none !important;
                                }
                            `;

                        this.shadowRoot.innerHTML = `
                                <style>
                                    :host {
                                        all: initial;
                                        display: block;
                                        position: absolute;
                                        top: 0;
                                        left: 0;
                                        right: 0;
                                        bottom: 0;
                                        width: 100%;
                                        height: 100%;
                                        pointer-events: none;
                                    }
                                    #widget-root {
                                        pointer-events: auto;
                                        width: 100%;
                                        position: absolute;
                                        left: 0;
                                        transition: transform 0.3s ease;
                                    }
                                    .sp-position-top {
                                        top: 0;
                                    }
                                    .sp-position-bottom {
                                        bottom: 0;
                                    }
                                    ${hiddenBtnStyle}
                                    ${modifiedCSS}
                                </style>
                                <div id="widget-root"></div>
                            `;

                        this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                        this.setupMediaQueryEmulation();
                        this.updateColors();
                    },

                    setupMediaQueryEmulation() {
                        const viewport = document.getElementById('browser-viewport');
                        if (!viewport) return;

                        const mediaStyles = document.createElement('style');
                        mediaStyles.setAttribute('data-media-emulation', 'true');
                        this.shadowRoot.appendChild(mediaStyles);

                        const updateMediaStyles = () => {
                            const width = viewport.clientWidth;
                            let styles = '';

                            if (width <= 480) {
                                styles = `
                                    .sp-alert-container {
                                        flex-direction: column !important;
                                        padding: 12px 16px !important;
                                        gap: 12px !important;
                                        text-align: center !important;
                                    }
                                    .sp-alert-actions {
                                        width: 100% !important;
                                        justify-content: center !important;
                                    }
                                    .sp-alert-btn {
                                        width: auto !important;
                                        min-width: 120px !important;
                                    }
                                `;
                            } else if (width <= 768) {
                                styles = `
                                    .sp-alert-container {
                                        padding: 10px 20px !important;
                                        gap: 16px !important;
                                    }
                                `;
                            }
                            mediaStyles.textContent = styles;
                        };

                        updateMediaStyles();
                        const resizeObserver = new ResizeObserver(() => updateMediaStyles());
                        resizeObserver.observe(viewport);
                        this.mediaObserver = resizeObserver;
                    },

                    // Главный метод обновления превью
                    updatePreview() {
                        if (!this.widgetRoot) return;

                        console.log('Update preview called');
                        console.log('has_button:', this.settings.has_button);
                        console.log('link:', this.settings.link);

                        let html = this.rawTemplate;

                        const placeholders = {
                            '{text}': this.settings.text || '',
                            '{link}': this.settings.link || '#',
                            '{btn_text}': this.settings.btn_text || 'Подробнее',
                            '{position}': this.settings.position || 'top'
                        };

                        Object.keys(placeholders).forEach(key => {
                            html = html.replaceAll(new RegExp(key.replace(/[{}]/g, '\\$&'), 'g'), placeholders[key]);
                        });

                        this.widgetRoot.innerHTML = html;

                        // Обновляем позицию
                        const position = this.settings.position || 'top';
                        this.widgetRoot.classList.remove('sp-position-top', 'sp-position-bottom');
                        this.widgetRoot.classList.add(`sp-position-${position}`);

                        // Обновляем видимость кнопки - используем style.display
                        const actionBtn = this.widgetRoot.querySelector('#sp-action-btn');
                        if (actionBtn) {
                            // Проверяем, нужно ли показывать кнопку
                            //console.log(this.settings.has_button);
                            const shouldShow = this.settings.has_button === true;

                            if (!shouldShow) {
                                actionBtn.style.display = 'none';
                            } else {
                                actionBtn.style.display = '';
                            }
                        }
                    },

                    updateColors() {
                        if (!this.shadowRoot) return;

                        const oldStyle = this.shadowRoot.querySelector('style[data-preview-vars]');
                        if (oldStyle) oldStyle.remove();

                        const style = document.createElement('style');
                        style.setAttribute('data-preview-vars', 'true');

                        const bgColor = this.settings.design?.bg_color || '#E63946';
                        const textColor = this.settings.design?.text_color || '#FFFFFF';
                        const btnColor = this.settings.design?.btn_color || '#1D3557';

                        const btnRgb = this.hexToRgb(btnColor);
                        const textRgb = this.hexToRgb(textColor);

                        style.textContent = `
                            :host {
                                --bg-color: ${bgColor};
                                --text-color: ${textColor};
                                --btn-color: ${btnColor};
                                --btn-color-rgb: ${btnRgb.r}, ${btnRgb.g}, ${btnRgb.b};
                                --text-color-rgb: ${textRgb.r}, ${textRgb.g}, ${textRgb.b};
                            }
                        `;

                        this.shadowRoot.appendChild(style);
                    },

                    hexToRgb(hex) {
                        hex = hex.replace(/^#/, '');
                        if (hex.length === 3) {
                            hex = hex.split('').map(c => c + c).join('');
                        }
                        const intVal = parseInt(hex, 16);
                        return {
                            r: (intVal >> 16) & 255,
                            g: (intVal >> 8) & 255,
                            b: intVal & 255
                        };
                    },

                    showError(message) {
                        const container = document.getElementById('preview-host');
                        if (container) {
                            if (container.shadowRoot) {
                                container.shadowRoot.innerHTML = '';
                            } else {
                                container.attachShadow({mode: 'open'});
                            }

                            container.shadowRoot.innerHTML = `
                                <style>
                                    :host {
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        width: 100%;
                                        height: 100%;
                                        background: #f8d7da;
                                        color: #721c24;
                                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                                        font-size: 14px;
                                    }
                                    .error-container {
                                        padding: 20px;
                                        margin: 20px;
                                        border: 1px solid #f5c6cb;
                                        border-radius: 8px;
                                        background: #fff;
                                        text-align: center;
                                        max-width: 400px;
                                    }
                                    .error-title {
                                        font-weight: bold;
                                        margin-bottom: 8px;
                                    }
                                    .error-message {
                                        font-size: 12px;
                                        font-family: monospace;
                                        word-break: break-all;
                                    }
                                </style>
                                <div class="error-container">
                                    <div class="error-title">⚠️ Ошибка загрузки виджета</div>
                                    <div class="error-message">${message}</div>
                                </div>
                            `;
                        }
                    },

                    saveConfig() {
                        const btn = event.currentTarget;
                        const originalContent = btn.innerHTML;

                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                        axios.post('{{ route("cabinet.sites.widgets.design.update", [$site, $widget]) }}', {
                            settings: this.settings
                        })
                            .then(response => {
                                if (response.data.status === 'success') {
                                    if (typeof showNotification === 'function') {
                                        showNotification(response.data.message, 'success', 'fa fa-check me-1');
                                    } else {
                                        alert(response.data.message);
                                    }
                                }
                            })
                            .catch(error => {
                                let errorMessage = 'Ошибка при сохранении';
                                if (error.response && error.response.data.message) {
                                    errorMessage = error.response.data.message;
                                }
                                if (typeof showNotification === 'function') {
                                    showNotification(errorMessage, 'danger', 'fa fa-times me-1');
                                } else {
                                    alert(errorMessage);
                                }
                                console.error('Save Error:', error);
                            })
                            .finally(() => {
                                btn.disabled = false;
                                btn.innerHTML = originalContent;
                            });
                    }
                };
            }
        </script>
    @endpush

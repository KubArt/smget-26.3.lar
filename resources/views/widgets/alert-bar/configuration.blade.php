@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="alertBarEditor({{ json_encode($config) }})">
        <div class="row">
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Конструктор: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form action="{{ route('cabinet.sites.widgets.design.update', [$site, $widget]) }}" method="POST">
                            @csrf

                            {{-- 1. Выбор позиции --}}
                            <div class="mb-4">
                                <label class="form-label text-primary fw-bold">Расположение</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                :class="settings.position === 'top' ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                @click="settings.position = 'top'; updatePreview()">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-circle me-2 fs-xs" :class="settings.position === 'top' ? 'text-primary' : 'text-muted'"></i>
                                                <span class="small fw-semibold">Сверху страницы</span>
                                            </div>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                :class="settings.position === 'bottom' ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                @click="settings.position = 'bottom'; updatePreview()">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-circle me-2 fs-xs" :class="settings.position === 'bottom' ? 'text-primary' : 'text-muted'"></i>
                                                <span class="small fw-semibold">Снизу страницы</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="position" :value="settings.position">
                            </div>

                            <div class="mb-3" x-show="settings.position === 'top'">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="fixed_on_scroll"
                                           x-model="settings.fixed_on_scroll" @change="updatePreview()">
                                    <label class="form-check-label small">
                                        Фиксировать при прокрутке (всегда вверху экрана)
                                    </label>
                                    <small class="text-muted d-block fs-xs">Если отключено - полоса в потоке документа и прокручивается со страницей</small>
                                </div>
                            </div>

                            <hr>

                            {{-- 2. Редактирование контента --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    Текст и кнопка
                                </label>

                                <div class="mb-3">
                                    <label class="small text-muted">Текст объявления</label>
                                    <textarea name="text" class="form-control form-control-sm" rows="2"
                                              x-model="settings.text" @input="updatePreview()"></textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 mb-2">
                                        <label class="small text-muted">Текст кнопки</label>
                                        <input type="text" name="btn_text" class="form-control form-control-sm"
                                               x-model="settings.btn_text" @input="updatePreview()">
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small text-muted mb-0">Ссылка кнопки (URL)</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="has_button"
                                                       x-model="settings.has_button" @change="updatePreview()">
                                                <label class="small ms-1">Показывать кнопку</label>
                                            </div>
                                        </div>
                                        <input type="text" name="link" class="form-control form-control-sm"
                                               x-show="settings.has_button"
                                               x-model="settings.link" @input="updatePreview()"
                                               placeholder="https://example.com">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- 3. Цвета --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Цветовая схема</label>
                                <div class="row g-2">
                                    <div class="col-4 text-center">
                                        <label class="form-label small">Фон</label>
                                        <input type="color" name="design[bg_color]" class="form-control form-control-color w-100"
                                               x-model="settings.design.bg_color" @change="updateColors(); updatePreview()">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="form-label small">Текст</label>
                                        <input type="color" name="design[text_color]" class="form-control form-control-color w-100"
                                               x-model="settings.design.text_color" @change="updateColors(); updatePreview()">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="form-label small">Кнопка</label>
                                        <input type="color" name="design[btn_color]" class="form-control form-control-color w-100"
                                               x-model="settings.design.btn_color" @change="updateColors(); updatePreview()">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- 4. Поведение (маркетинговые инструменты) --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Поведение</label>

                                <div class="mb-3">
                                    <label class="small text-muted">Задержка появления (сек)</label>
                                    <input type="number" name="delay" class="form-control form-control-sm"
                                           x-model="settings.delay" min="0" max="30">
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Авто-скрытие (сек)</label>
                                    <input type="number" name="auto_hide" class="form-control form-control-sm"
                                           x-model="settings.auto_hide" min="0" max="60">
                                    <small class="text-muted fs-xs">0 — не скрывать автоматически</small>
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Показать при прокрутке (%)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="range" name="scroll_trigger" class="form-range flex-grow-1"
                                               x-model="settings.scroll_trigger" min="0" max="100"
                                               @input="updatePreview()">
                                        <span class="badge bg-secondary" x-text="settings.scroll_trigger + '%'"></span>
                                    </div>
                                    <small class="text-muted fs-xs">0 — показывать сразу</small>
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Частота показа</label>
                                    <select name="frequency" class="form-select form-select-sm"
                                            x-model="settings.frequency">
                                        <option value="always">На каждой странице</option>
                                        <option value="once_session">Один раз за сессию</option>
                                        <option value="once_day">Один раз в сутки</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">При закрытии пользователем</label>
                                    <select name="close_behavior" class="form-select form-select-sm"
                                            x-model="settings.close_behavior">
                                        <option value="hide_session">Не показывать до конца сессии</option>
                                        <option value="hide_forever">Больше никогда не показывать</option>
                                    </select>
                                </div>
                            </div>

                            <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig()">
                                <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Предпросмотр --}}
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }" x-init="$watch('previewMode', () => $dispatch('preview-mode-changed', previewMode))">
                @include("widgets.configuration.preview")
            </div>
        </div>
    </div>



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
@endsection

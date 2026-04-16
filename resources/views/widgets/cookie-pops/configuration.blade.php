@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="cookiePopsEditor({{ json_encode($config) }})">
        <div class="row">
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Конструктор: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form action="{{ route('cabinet.sites.widgets.design.update', [$site, $widget]) }}" method="POST">
                            @csrf

                            {{-- 1. Выбор макета --}}
                            <div class="mb-4">
                                <label class="form-label text-primary fw-bold">Выберите макет</label>
                                <div class="row g-2">
                                    <template x-for="skin in skins" :key="skin.slug">
                                        <div class="col-6">
                                            <button type="button"
                                                    class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                    :class="settings.template === skin.slug ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                    @click="applyTemplate(skin.slug)">
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-circle me-2 fs-xs" :class="settings.template === skin.slug ? 'text-primary' : 'text-muted'"></i>
                                                    <span class="small fw-semibold" x-text="skin.name"></span>
                                                </div>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <input type="hidden" name="template" :value="settings.template">
                            </div>

                            <hr>

                            {{-- 2. Редактирование контента --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    Тексты и кнопки
                                </label>

                                <div class="mb-3">
                                    <label class="small text-muted">Основное сообщение</label>
                                    <textarea name="content[text]" class="form-control form-control-sm" rows="3"
                                              x-model="settings.content.text" @input="updatePreview()"></textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 mb-2">
                                        <label class="small text-muted">Кнопка "Принять"</label>
                                        <input type="text" name="content[btn_accept_text]" class="form-control form-control-sm"
                                               x-model="settings.content.btn_accept_text" @input="updatePreview()">
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small text-muted mb-0">Кнопка "Уйти"</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="content[show_leave_btn]"
                                                       x-model="settings.content.show_leave_btn" @change="updatePreview()">
                                                <label class="small ms-1">Показывать</label>
                                            </div>
                                        </div>
                                        <input type="text" name="content[btn_leave_text]" class="form-control form-control-sm"
                                               x-show="settings.content.show_leave_btn"
                                               x-model="settings.content.btn_leave_text" @input="updatePreview()">
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small text-muted">Текст ссылки политики</label>
                                        <input type="text" name="content[policy_text]" class="form-control form-control-sm"
                                               x-model="settings.content.policy_text" @input="updatePreview()">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">URL ссылки</label>
                                        <input type="text" name="content[policy_url]" class="form-control form-control-sm"
                                               x-model="settings.content.policy_url" @input="updatePreview()">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- 3. Цвета --}}
                            <div class="row mb-4">
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

                            <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig()">
                                <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7" x-data="{ previewMode: 'desktop' }" x-init="$watch('previewMode', () => $dispatch('preview-mode-changed', previewMode))">
                @include("widgets.configuration.preview")
            </div>
        </div>
    </div>


        @push('js')
        <script>
            function cookiePopsEditor(config) {
                return {
                    slug: config.slug,
                    settings: config.settings,
                    skins: config.skins,
                    rawTemplate: '',
                    rawCss: '',
                    shadowRoot: null,
                    widgetRoot: null,
                    previewMode: 'desktop', // Добавляем режим превью

                    async init() {
                        // Инициализация структуры контента
                        if (!this.settings.content) {
                            this.settings.content = {
                                text: 'Мы используем cookies для улучшения работы сайта.',
                                btn_accept_text: 'Принять',
                                btn_leave_text: 'Покинуть сайт',
                                show_leave_btn: true,
                                policy_text: 'Политика конфиденциальности',
                                policy_url: '/privacy'
                            };
                        }

                        if (this.settings.content.show_leave_btn === undefined) {
                            this.settings.content.show_leave_btn = true;
                        }

                        if (!this.settings.template) {
                            this.settings.template = Object.keys(this.skins)[0];
                        }

                        if (!this.settings.design) {
                            this.settings.design = {
                                bg_color: '#ffffff',
                                text_color: '#2d3436',
                                btn_color: '#0665d0'
                            };
                        }

                        await this.loadSkin(this.settings.template);

                        // Оптимизированные watchers - обновляем только то, что нужно
                        this.$watch('settings.content.text', () => this.updateContent());
                        this.$watch('settings.content.btn_accept_text', () => this.updateContent());
                        this.$watch('settings.content.btn_leave_text', () => this.updateContent());
                        this.$watch('settings.content.policy_text', () => this.updateContent());
                        this.$watch('settings.content.policy_url', () => this.updateContent());
                        this.$watch('settings.content.show_leave_btn', () => this.toggleLeaveButton());

                        // Обновление цветов без перерисовки
                        this.$watch('settings.design.bg_color', () => this.updateColors());
                        this.$watch('settings.design.text_color', () => this.updateColors());
                        this.$watch('settings.design.btn_color', () => this.updateColors());
                    },

                    async loadSkin(skinId) {
                        const baseUrl = `/widgets/${this.slug}/skins/${skinId}`;
                        try {
                            const [tplRes, cssRes] = await Promise.all([
                                fetch(`${baseUrl}/template.html`),
                                fetch(`${baseUrl}/style.css`)
                            ]);
                            this.rawTemplate = await tplRes.text();
                            this.rawCss = await cssRes.text();
                            this.setupShadowDOM();
                            this.updateContent(); // Обновляем только контент после загрузки
                        } catch (e) {
                            console.error('Error loading skin:', e);
                        }
                    },

                    setupShadowDOM() {
                        const container = document.getElementById('preview-host');
                        if (!container) return;

                        this.shadowRoot = container.shadowRoot || container.attachShadow({mode: 'open'});

                        // Модифицируем CSS для превью
                        let modifiedCSS = this.rawCss;
                        modifiedCSS = modifiedCSS.replace(/position:\s*fixed/g, 'position: absolute');
                        modifiedCSS = modifiedCSS.replace(/position:fixed/g, 'position: absolute');
                        modifiedCSS = modifiedCSS.replace(/100vh/g, '100%');
                        modifiedCSS = modifiedCSS.replace(/100vw/g, '100%');

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
                                        height: 100%;
                                        position: relative;
                                    }
                                    .hidden-btn {
                                        display: none !important;
                                    }

                                    /* Базовые стили */
                                    ${modifiedCSS}
                                </style>
                                <div id="widget-root"></div>
                            `;

                        this.widgetRoot = this.shadowRoot.getElementById('widget-root');

                        // Добавляем эмуляцию медиа-запросов
                        this.setupMediaQueryEmulation();

                        this.updateColors();
                    },

                    setupMediaQueryEmulation() {
                        const viewport = document.getElementById('browser-viewport');
                        if (!viewport) return;

                        // Создаем элемент для эмуляции медиа-запросов
                        const mediaStyles = document.createElement('style');
                        mediaStyles.setAttribute('data-media-emulation', 'true');
                        this.shadowRoot.appendChild(mediaStyles);

                        // Функция обновления эмулируемых медиа-запросов
                        const updateMediaStyles = () => {
                            const width = viewport.clientWidth;

                            let mobileStyles = '';
                            let tabletStyles = '';

                            if (width <= 480) {
                                // Мобильные стили
                                mobileStyles = `
                                    /* Эмуляция @media (max-width: 480px) */
                                    .sp-glass-container {
                                        flex-direction: column !important;
                                        padding: 16px !important;
                                        text-align: center !important;
                                        gap: 16px !important;
                                    }
                                    .sp-glass-actions {
                                        width: 100% !important;
                                        flex-direction: column !important;
                                        gap: 8px !important;
                                    }
                                    .sp-glass-btn {
                                        width: 100% !important;
                                    }
                                    .sp-glass-text {
                                        font-size: 13px !important;
                                    }

                                    /* Для corner-popup */
                                    .sp-corner-popup {
                                        padding: 16px !important;
                                        gap: 12px !important;
                                    }
                                    .sp-popup-footer {
                                        flex-direction: column !important;
                                    }
                                    .sp-popup-btn-accept,
                                    .sp-popup-btn-leave {
                                        width: 100% !important;
                                    }

                                    /* Для floating-pill */
                                    .sp-floating-pill {
                                        flex-direction: column !important;
                                        border-radius: 20px !important;
                                        padding: 16px !important;
                                        white-space: normal !important;
                                    }
                                    .sp-floating-actions {
                                        width: 100% !important;
                                        flex-direction: column !important;
                                    }
                                    .sp-floating-btn-accept {
                                        width: 100% !important;
                                    }
                                `;
                                                } else if (width <= 768) {
                                                    // Планшетные стили
                                                    tabletStyles = `
                                    /* Эмуляция @media (max-width: 768px) */
                                    .sp-glass-container {
                                        flex-direction: column !important;
                                        padding: 20px !important;
                                        text-align: center !important;
                                        gap: 20px !important;
                                    }
                                    .sp-glass-actions {
                                        width: 100% !important;
                                        flex-direction: column !important;
                                        gap: 10px !important;
                                    }
                                    .sp-glass-btn {
                                        width: 100% !important;
                                    }

                                    .sp-corner-popup {
                                        max-width: none !important;
                                        width: auto !important;
                                    }

                                    .sp-floating-pill {
                                        gap: 16px !important;
                                    }
                                `;
                            }

                            mediaStyles.textContent = mobileStyles + tabletStyles;
                        };

                        // Запускаем при изменении размера
                        updateMediaStyles();

                        // Используем ResizeObserver для отслеживания
                        const resizeObserver = new ResizeObserver(() => {
                            updateMediaStyles();
                        });
                        resizeObserver.observe(viewport);

                        // Сохраняем observer для очистки
                        this.mediaObserver = resizeObserver;
                    },

                    // Обновляем только контент (текст, ссылки, кнопки)
                    updateContent() {
                        if (!this.widgetRoot) return;

                        // Получаем HTML шаблон
                        let html = this.rawTemplate;

                        // Заменяем переменные
                        const placeholders = {
                            '{text}': this.settings.content.text || '',
                            '{policy_text}': this.settings.content.policy_text || '',
                            '{policy_url}': this.settings.content.policy_url || '#',
                            '{btn_accept_text}': this.settings.content.btn_accept_text || 'Принимаю',
                            '{btn_leave_text}': this.settings.content.btn_leave_text || 'Покинуть сайт'
                        };

                        Object.keys(placeholders).forEach(key => {
                            html = html.replaceAll(new RegExp(key.replace(/[{}]/g, '\\$&'), 'g'), placeholders[key]);
                        });

                        // Сохраняем состояние кнопки "Уйти" перед обновлением
                        const wasHidden = this.widgetRoot.querySelector('#sp-leave')?.classList.contains('hidden-btn');

                        this.widgetRoot.innerHTML = html;

                        // Восстанавливаем состояние кнопки "Уйти"
                        const leaveBtn = this.widgetRoot.querySelector('#sp-leave');
                        if (leaveBtn) {
                            if (!this.settings.content.show_leave_btn || wasHidden) {
                                leaveBtn.classList.add('hidden-btn');
                            } else {
                                leaveBtn.classList.remove('hidden-btn');
                            }
                        }
                    },

                    // Переключаем только кнопку "Уйти" без перерисовки всего виджета
                    toggleLeaveButton() {
                        if (!this.widgetRoot) return;
                        const leaveBtn = this.widgetRoot.querySelector('#sp-leave');
                        if (leaveBtn) {
                            if (!this.settings.content.show_leave_btn) {
                                leaveBtn.classList.add('hidden-btn');
                            } else {
                                leaveBtn.classList.remove('hidden-btn');
                            }
                        }
                    },

                    // Обновляем только CSS переменные без перерисовки DOM
                    updateColors() {
                        if (!this.shadowRoot) return;

                        // Удаляем старый style с переменными
                        const oldStyle = this.shadowRoot.querySelector('style[data-preview-vars]');
                        if (oldStyle) oldStyle.remove();

                        // Добавляем новые переменные
                        const style = document.createElement('style');
                        style.setAttribute('data-preview-vars', 'true');

                        const bgColor = this.settings.design?.bg_color || '#ffffff';
                        const textColor = this.settings.design?.text_color || '#2d3436';
                        const btnColor = this.settings.design?.btn_color || '#0665d0';

                        const btnRgb = this.hexToRgb(btnColor);
                        const textRgb = this.hexToRgb(textColor);

                        style.textContent = `
                :host {
                    --sm-bg-color: ${bgColor};
                    --sm-text-color: ${textColor};
                    --sm-btn-color: ${btnColor};
                    --sm-btn-color-rgb: ${btnRgb.r}, ${btnRgb.g}, ${btnRgb.b};
                    --sm-text-color-rgb: ${textRgb.r}, ${textRgb.g}, ${textRgb.b};
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

                    async applyTemplate(id) {
                        if (this.settings.template === id) return; // Предотвращаем повторную загрузку
                        this.settings.template = id;
                        await this.loadSkin(id);
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
                }
            }
        </script>
    @endpush
@endsection

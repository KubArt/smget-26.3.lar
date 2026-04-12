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
                                    <input type="color" name="design[bg_color]" class="form-control form-control-color w-100" x-model="settings.design.bg_color">
                                </div>
                                <div class="col-4 text-center">
                                    <label class="form-label small">Текст</label>
                                    <input type="color" name="design[text_color]" class="form-control form-control-color w-100" x-model="settings.design.text_color">
                                </div>
                                <div class="col-4 text-center">
                                    <label class="form-label small">Кнопка</label>
                                    <input type="color" name="design[btn_color]" class="form-control form-control-color w-100" x-model="settings.design.btn_color">
                                </div>
                            </div>

                            <button type="button" class="btn btn-alt-primary" @click="saveConfig()">
                                <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            <div class="col-md-7" x-data="{ previewMode: 'desktop' }">
                <div class="block block-rounded sticky-top" style="top: 20px;">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Предпросмотр</h3>
                        <div class="block-options">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-alt-secondary"
                                        :class="previewMode === 'desktop' ? 'active' : ''"
                                        @click="previewMode = 'desktop'">
                                    <i class="fa fa-desktop me-1"></i> ПК
                                </button>
                                <button type="button" class="btn btn-alt-secondary"
                                        :class="previewMode === 'mobile' ? 'active' : ''"
                                        @click="previewMode = 'mobile'">
                                    <i class="fa fa-mobile-alt me-1"></i> Мобильный
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="block-content p-3 bg-body-dark">
                        {{-- Имитация окна браузера --}}
                        <div class="browser-mockup" :class="previewMode">
                            <div class="browser-header">
                                <div class="d-flex gap-1">
                                    <span class="dot red"></span><span class="dot yellow"></span><span class="dot green"></span>
                                </div>
                                <div class="address-bar">
                                    <i class="fa fa-lock me-1 text-success"></i> your-website.com
                                </div>
                            </div>

                            {{-- Область просмотра контента --}}
                            <div class="browser-viewport">
                                {{-- Точка монтирования Shadow DOM --}}
                                <div id="preview-host"></div>

                                {{-- Заглушка контента сайта --}}
                                <div class="site-placeholder">
                                    <div class="hero-rect"></div>
                                    <div class="p-3">
                                        <div class="row g-3">
                                            <div class="col-4"><div class="line"></div></div>
                                            <div class="col-8"><div class="line w-75"></div></div>
                                            <div class="col-12"><div class="line"></div></div>
                                            <div class="col-12"><div class="line w-50"></div></div>
                                            <div class="col-6"><div class="line"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Индикатор разрешения --}}
                        <div class="text-center mt-2">
                            <small class="text-muted" x-text="previewMode === 'desktop' ? '100% x 500px' : '375px x 500px'"></small>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                /* Стили для имитации браузера */
                .browser-mockup {
                    border: 1px solid #d1d1d1;
                    border-radius: 8px;
                    background: #fff;
                    overflow: hidden;
                    height: 500px;
                    display: flex;
                    flex-direction: column;
                    margin: 0 auto;
                    transition: width 0.3s ease, height 0.3s ease;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                }

                /* Режимы ширины */
                .browser-mockup.desktop { width: 100%; }
                .browser-mockup.mobile { width: 375px; }

                .browser-header {
                    background: #f1f1f1;
                    padding: 8px 12px;
                    display: flex;
                    align-items: center;
                    border-bottom: 1px solid #e1e1e1;
                }

                .browser-header .dot { height: 10px; width: 10px; border-radius: 50%; }
                .dot.red { background: #ff5f56; }
                .dot.yellow { background: #ffbd2e; }
                .dot.green { background: #27c93f; }

                .browser-header .address-bar {
                    background: #fff;
                    margin: 0 auto;
                    width: 60%;
                    border-radius: 4px;
                    font-size: 11px;
                    padding: 3px 10px;
                    color: #666;
                    text-align: center;
                    border: 1px solid #e1e1e1;
                }

                /* Самое важное: область, где живет виджет */
                .browser-viewport {
                    position: relative;
                    flex-grow: 1;
                    background: #fff;
                    overflow-y: auto; /* Позволяет прокручивать сайт-заглушку */
                    overflow-x: hidden;
                }

                /* Заглушка контента сайта */
                .site-placeholder { padding: 0; pointer-events: none; }
                .hero-rect { height: 160px; background: #f0f2f5; margin-bottom: 10px; width: 100%; }
                .line { height: 12px; background: #f0f2f5; border-radius: 6px; margin-bottom: 15px; width: 100%; }

                /* Чтобы Shadow DOM контент правильно позиционировался относительно viewport */
                #preview-host {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 100;
                    pointer-events: none; /* Чтобы можно было скроллить placeholder */
                }
            </style>


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

                    async init() {
                        // Инициализация структуры контента
                        if (!this.settings.content) {
                            this.settings.content = {
                                text: 'Мы используем cookies для улучшения работы сайта.',
                                btn_accept_text: 'Принять',
                                btn_leave_text: 'Покинуть сайт',
                                show_leave_btn: true, // По умолчанию включена
                                policy_text: 'Политика конфиденциальности',
                                policy_url: '/privacy'
                            };
                        }

                        // Проверка на наличие поля show_leave_btn при загрузке существующих настроек
                        if (this.settings.content.show_leave_btn === undefined) {
                            this.settings.content.show_leave_btn = true;
                        }

                        if (!this.settings.template) this.settings.template = Object.keys(this.skins)[0];
                        if (!this.settings.design) {
                            this.settings.design = { bg_color: '#ffffff', text_color: '#2d3436', btn_color: '#0665d0' };
                        }

                        await this.loadSkin(this.settings.template);
                        this.$watch('settings.design', () => this.updateColors());
                    },

                    async loadSkin(skinId) {
                        const baseUrl = `/widgets/${this.slug}/skins/${skinId}`;
                        try {
                            const [tplRes, cssRes] = await Promise.all([
                                fetch(`${baseUrl}/template.html`),
                                fetch(`${baseUrl}/style.css`)
                            ]);
                            this.rawTemplate = await tplRes.text();
                            this.setupShadowDOM(await cssRes.text(), skinId);
                            this.updatePreview();
                        } catch (e) { console.error(e); }
                    },

                    setupShadowDOM(css, skinId) {
                        const container = document.getElementById('preview-host');
                        let shadow = container.shadowRoot || container.attachShadow({mode: 'open'});

                        // Модифицируем CSS для превью
                        // 1. Убираем fixed позиционирование, заменяем на absolute
                        // 2. Ограничиваем область видимости
                        const previewStyles = css
                            .replace(/position:\s*fixed/g, 'position: absolute')
                            .replace(/width:\s*100vw/g, 'width: 100%');

                        shadow.innerHTML = `
                            <style>
                                :host {
                                    all: initial; /* Сброс всех внешних стилей темы админки */
                                    display: block;
                                    position: absolute;
                                    top: 0; left: 0; right: 0; bottom: 0;
                                    width: 100%; height: 100%;
                                    pointer-events: none; /* Чтобы можно было кликать сквозь пустые области */
                                }
                                #widget-root {
                                    pointer-events: auto; /* Включаем клики обратно только для виджета */
                                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                                }
                                ${previewStyles}

                                /* Фикс для кнопок, если они пропадают */
                                .hidden-btn { display: none !important; }
                            </style>
                            <div id="widget-root" class="sp-skin-${skinId}"></div>
                        `;
                    },

                    updatePreview() {
                        const container = document.getElementById('preview-host');
                        if (!container.shadowRoot) return;
                        const root = container.shadowRoot.getElementById('widget-root');

                        let html = this.rawTemplate;

                        // 1. Заменяем текстовые переменные
                        Object.keys(this.settings.content).forEach(key => {
                            const value = this.settings.content[key];
                            if (typeof value === 'string') {
                                html = html.replace(new RegExp(`{${key}}`, 'g'), value || '');
                            }
                        });

                        root.innerHTML = html;

                        // 2. Логика скрытия кнопки "Покинуть" в DOM превью
                        // Ищем кнопку по ID (id="sp-leave" был в вашем шаблоне) или по классу
                        const leaveBtn = root.querySelector('#sp-leave') || root.querySelector('.sp-side-btn-leave') || root.querySelector('.sp-overlay-btn-leave');
                        if (leaveBtn) {
                            if (!this.settings.content.show_leave_btn) {
                                leaveBtn.classList.add('hidden-btn');
                            } else {
                                leaveBtn.classList.remove('hidden-btn');
                            }
                        }

                        this.updateColors();
                    },

                    // Добавьте это в ваш x-data внутри конфигурации виджета
                    saveConfig() {
                        const btn = event.currentTarget;
                        const originalContent = btn.innerHTML;

                        // 1. Индикация загрузки (отключаем кнопку и меняем текст)
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                        axios.post('{{ route("cabinet.sites.widgets.design.update", [$site, $widget]) }}', {
                            settings: this.settings
                        })
                            .then(response => {
                                // 2. Успех: вызываем ваш стандартный хелпер
                                if (response.data.status === 'success') {
                                    showNotification(response.data.message, 'success', 'fa fa-check me-1');
                                }
                            })
                            .catch(error => {
                                // 3. Ошибка: обрабатываем текст ошибки из Laravel или выводим дефолт
                                let errorMessage = 'Ошибка при сохранении';
                                if (error.response && error.response.data.message) {
                                    errorMessage = error.response.data.message;
                                }

                                showNotification(errorMessage, 'danger', 'fa fa-times me-1');
                                console.error('Save Error:', error);
                            })
                            .finally(() => {
                                // 4. Завершение: возвращаем кнопку в исходное состояние
                                btn.disabled = false;
                                btn.innerHTML = originalContent;
                            });
                    },

                    // Остальные методы...
                    applyTemplate(id) { this.settings.template = id; this.loadSkin(id); },
                    updateColors() {
                        const container = document.getElementById('preview-host');
                        if (container && this.settings.design) {
                            Object.keys(this.settings.design).forEach(key => {
                                container.style.setProperty(`--${key.replace('_', '-')}`, this.settings.design[key]);
                            });
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection

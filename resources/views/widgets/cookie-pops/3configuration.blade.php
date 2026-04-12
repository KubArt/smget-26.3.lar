<div class="content" x-data="cookiePopsEditor()">
    <div class="row">
        <div class="col-md-5">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Конструктор Cookie Pops</h3>
                </div>
                <div class="block-content pb-4">
                    <form action="" method="POST">
                        @csrf @method('PUT')
                        <input type="hidden" name="settings" :value="JSON.stringify(settings)">

                        <div class="mb-4">
                            <label class="form-label text-primary">Выберите макет</label>
                            <div class="row g-2">
                                <template x-for="(tpl, id) in templates" :key="id">
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                :class="settings.template === id ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                @click="applyTemplate(id)">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-circle me-2 fs-xs" :class="settings.template === id ? 'text-primary' : 'text-muted'"></i>
                                                <span class="small fw-semibold" x-text="tpl.name"></span>
                                            </div>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Текст сообщения</label>
                            <textarea class="form-control" x-model="settings.content.text"></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label">Кнопка 1 (Принять)</label>
                                <input type="text" class="form-control" x-model="settings.content.btn_accept_text">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Кнопка 2 (Уйти)</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input class="form-check-input" type="checkbox" x-model="settings.content.show_leave_btn">
                                    </div>
                                    <input type="text" class="form-control" x-model="settings.content.btn_leave_text" :disabled="!settings.content.show_leave_btn">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Ссылка на политику</label>
                            <input type="text" class="form-control" x-model="settings.content.policy_url" placeholder="https://...">
                        </div>

                        <div class="row mb-4">
                            <div class="col-4">
                                <label class="form-label">Фон</label>
                                <input type="color" class="form-control form-control-color w-100" x-model="settings.design.bg_color">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Текст</label>
                                <input type="color" class="form-control form-control-color w-100" x-model="settings.design.text_color">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Акцент</label>
                                <input type="color" class="form-control form-control-color w-100" x-model="settings.design.btn_color">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Сохранить виджет</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="block block-rounded sticky-top" style="top: 20px;">

                <div class="block-content block-content-full bg-gray-light d-flex align-items-center justify-content-center" style="height: 400px; position: relative;">
                    <div class="p-4 text-muted opacity-25 w-100 text-center">
                        <h4>Ваш сайт</h4>
                        <div class="bg-gray-darker mb-2" style="height: 10px; width: 100%"></div>
                        <div class="bg-gray-darker mb-2" style="height: 10px; width: 80%"></div>
                        <div class="bg-gray-darker" style="height: 10px; width: 90%"></div>
                    </div>

                    <div id="preview-host"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script>


        function cookiePopsEditor() {
            return {
                // РЕШЕНИЕ: Переносим templates сюда

                templates: {templates: {
                        'corner-modern': { name: 'Современный угол' },
                        'top-bar-classic': { name: 'Классическая полоса' },
                        'floating-pill': { name: 'Плавающая пилюля' },
                        'modal-center-minimal': { name: 'Центральное окно' },
                        'bottom-glass': { name: 'Стеклянная полоса' },
                        'side-panel-right': { name: 'Боковая панель (Право)' },
                        'side-panel-left': { name: 'Боковая панель (Лево)' },
                        'top-floating-card': { name: 'Карточка сверху' },
                        'fullscreen-overlay': { name: 'Весь экран' },
                        'floating-bottom-center': { name: 'Пузырь снизу' },
                        'bottom-bar-compact': { name: 'Компактная полоса' },
                        'side-bar-slim': { name: 'Тонкая панель (Право)' }
                },

                settings: {
                    template: 'corner-modern',
                    content: {
                        text: 'Мы используем файлы cookie.',
                        btn_accept_text: 'Принять',
                        btn_leave_text: 'Уйти',
                        show_leave_btn: true,
                        policy_text: 'Политика конфиденциальности',
                        policy_url: '/privacy'
                    },
                    design: {
                        bg_color: '#ffffff',
                        text_color: '#2d3436',
                        btn_color: '#0665d0'
                    }
                },

                rawTemplate: '',

                async init() {
                    // Загружаем скин по умолчанию сразу при инициализации Alpine
                    await this.loadSkin(this.settings.template);

                    // Настройка реактивности для цветов
                    this.$watch('settings.design', () => this.updateColors());
                },

                async applyTemplate(id) {
                    this.settings.template = id;
                    await this.loadSkin(id);
                },

                async loadSkin(skinId) {
                    try {
                        const response = await fetch(`/widgets/cookie-pops/skins/${skinId}/template.html`);
                        this.rawTemplate = await response.text();

                        const styleResponse = await fetch(`/widgets/cookie-pops/skins/${skinId}/style.css`);
                        const css = await styleResponse.text();

                        this.setupShadowDOM(css);
                        this.updatePreview();
                    } catch (e) {
                        console.error("Ошибка загрузки скина:", e);
                    }
                },

                    setupShadowDOM(css, skinId) {
                        const container = document.getElementById('preview-host');
                        let shadow = container.shadowRoot || container.attachShadow({mode: 'open'});

                        shadow.innerHTML = `
                            <style>
                                /* Хак для превью: заставляем фиксированные элементы вести себя как абсолютные внутри окна */
                                .sp-skin-${skinId} {
                                    position: absolute !important;
                                    height: 100% !important;
                                    max-height: 100% !important;
                                }
                                ${css}
                            </style>
                            <div id="widget-root" class="sp-skin-${skinId}"></div>
                        `;
                    },

                updatePreview() {
                    const container = document.getElementById('preview-host');
                    if (!container || !container.shadowRoot) return;

                    const root = container.shadowRoot.getElementById('widget-root');
                    let html = this.rawTemplate;

                    // Заменяем переменные
                    Object.keys(this.settings.content).forEach(key => {
                        html = html.replace(new RegExp(`{${key}}`, 'g'), this.settings.content[key]);
                    });

                    root.innerHTML = html;
                    this.updateColors();
                },

                updateColors() {
                    const container = document.getElementById('preview-host');
                    if (container) {
                        container.style.setProperty('--bg-color', this.settings.design.bg_color);
                        container.style.setProperty('--text-color', this.settings.design.text_color);
                        container.style.setProperty('--btn-color', this.settings.design.btn_color);
                    }
                }
            }
        }
    </script>
@endpush

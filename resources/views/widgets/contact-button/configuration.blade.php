@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="contactButtonEditor({{ json_encode($config) }})">
        <div class="row">
            {{-- КОЛОНКА НАСТРОЕК --}}
            <div class="col-md-7">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка Мультикнопки</h3>
                        <div class="block-options">
                            <button type="button" class="btn btn-sm btn-alt-primary" @click="saveWidget">
                                <i class="fa fa-save me-1"></i> Сохранить
                            </button>
                        </div>
                    </div>
                    <div class="block-content pb-4">
                        {{-- 1. КАНАЛЫ СВЯЗИ --}}
                        <div class="mb-4">
                            <label class="form-label d-flex justify-content-between">
                                Каналы связи
                                <button type="button" class="btn btn-xs btn-primary" @click="addChannel">
                                    <i class="fa fa-plus me-1"></i> Добавить
                                </button>
                            </label>

                            <div class="channels-list border rounded p-2 bg-light">
                                <template x-for="(channel, index) in settings.channels" :key="channel.id">
                                    <div class="channel-item bg-white border rounded p-3 mb-2 shadow-sm">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-auto">
                                                <select class="form-select form-select-sm" x-model="channel.type">
                                                    <option value="whatsapp">WhatsApp</option>
                                                    <option value="telegram">Telegram</option>
                                                    <option value="phone">Телефон</option>
                                                    <option value="vk">ВКонтакте</option>
                                                    <option value="custom">Свой выбор</option>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-sm" placeholder="Название" x-model="channel.label">
                                            </div>
                                            <div class="col-auto">
                                                <button type="button" class="btn btn-sm btn-alt-danger" @click="removeChannel(index)">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row g-2 mt-2">
                                            <div class="col-md-4">
                                                <select class="form-select form-select-sm" x-model="channel.action_type">
                                                    <option value="link">Открыть ссылку</option>
                                                    <option value="lead_form">Лид-форма</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="text" class="form-control form-control-sm"
                                                       :placeholder="channel.action_type === 'link' ? 'https://... или номер' : 'ID формы'"
                                                       x-model="channel.action_value">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- 2. ВИЗУАЛ И АНИМАЦИЯ --}}
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Тип анимации</label>
                                <select class="form-select" x-model="settings.animation.type">
                                    <option value="wave">Волна (Круги)</option>
                                    <option value="pulse">Пульсация</option>
                                    <option value="shake">Тряска</option>
                                    <option value="jelly">Желе</option>
                                    <option value="none">Без анимации</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Расположение</label>
                                <select class="form-select" x-model="settings.position">
                                    <option value="bottom-right">Справа внизу</option>
                                    <option value="bottom-left">Слева внизу</option>
                                    <option value="top-right">Справа вверху</option>
                                    <option value="top-left">Слева вверху</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Размер</label>
                                <select class="form-select" x-model="settings.design.size">
                                    <option value="small">Маленькая</option>
                                    <option value="medium">Средняя</option>
                                    <option value="large">Большая</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Прозрачность</label>
                                <input type="range" class="form-range" min="0.1" max="1" step="0.1" x-model="settings.design.opacity">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Цвет кнопки</label>
                                <input type="color" class="form-control form-control-color w-100" x-model="settings.design.main_btn_color">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- КОЛОНКА ПРЕВЬЮ --}}
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm sticky-top" style="top: 20px;">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Предпросмотр</h3>
                    </div>
                    <div class="block-content bg-body-dark p-0 overflow-hidden" style="height: 500px; position: relative;">
                        <div id="preview-viewport" style="width: 100%; height: 100%; position: relative;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            function contactButtonEditor(config) {
                console.log(config);
                return {
                    slug: config.slug,
                    settings: config.settings,
                    skins: config.skins,
                    rawTemplate: '',
                    rawCss: '',
                    widgetRoot: null,
                    shadowRoot: null,

                    async init() {
                        // Инициализация структуры
                        if (!this.settings) {
                            this.settings = {
                                position: 'bottom-right',
                                delay: 2,
                                channels: [],
                                design: {
                                    main_btn_color: '#007bff',
                                    main_icon_color: '#ffffff',
                                    size: 'medium',
                                    opacity: 1,
                                    hover_effect: 'lift'
                                },
                                animation: {
                                    type: 'wave'
                                }
                            };
                        }

                        if (!this.settings.design) {
                            this.settings.design = {
                                main_btn_color: '#007bff',
                                main_icon_color: '#ffffff',
                                size: 'medium',
                                opacity: 1,
                                hover_effect: 'lift'
                            };
                        }

                        if (!this.settings.animation) {
                            this.settings.animation = { type: 'wave' };
                        }

                        if (!this.settings.channels || this.settings.channels.length === 0) {
                            this.settings.channels = [{
                                id: 'ch_1',
                                type: 'whatsapp',
                                label: 'Написать в WhatsApp',
                                action_type: 'link',
                                action_value: '79001234567',
                                bg_color: '#25D366',
                                icon_color: '#ffffff',
                                is_active: true
                            }];
                        }

                        await this.loadSkin('default');
                        this.setupWatchers();
                    },

                    async loadSkin(skinId) {
                        const skinUrl = `/widgets/${this.slug}/skins/${skinId}`;
                        const baseUrl = `/widgets/${this.slug}`;

                        try {
                            let tplResponse = await fetch(`${skinUrl}/template.html`);
                            let cssResponse = await fetch(`${skinUrl}/style.css`);

                            if (!tplResponse.ok) {
                                tplResponse = await fetch(`${baseUrl}/template.html`);
                            }
                            if (!cssResponse.ok) {
                                cssResponse = await fetch(`${baseUrl}/style.css`);
                            }

                            if (!tplResponse.ok) {
                                throw new Error('Template not found');
                            }
                            if (!cssResponse.ok) {
                                throw new Error('CSS not found');
                            }

                            this.rawTemplate = await tplResponse.text();
                            this.rawCss = await cssResponse.text();

                            this.setupShadowDOM();
                            this.updatePreview();

                        } catch (e) {
                            console.error('Error loading skin:', e);
                            this.showError('Не удалось загрузить шаблон виджета');
                        }
                    },

                    setupShadowDOM() {
                        const container = document.getElementById('preview-viewport');
                        if (!container) return;

                        this.shadowRoot = container.shadowRoot || container.attachShadow({mode: 'open'});

                        // Заменяем фиксированное позиционирование на абсолютное для превью
                        let modifiedCSS = this.rawCss.replace(/position:\s*fixed/g, 'position: absolute');

                        this.shadowRoot.innerHTML = `
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                        <style>
                            :host { display: block; width: 100%; height: 100%; position: relative; background: #f4f6f8; }
                            #widget-root { width: 100%; height: 100%; position: relative; overflow: hidden; }
                            ${modifiedCSS}
                        </style>
                        <div id="widget-root"></div>
                        `;

                        this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                    },

                    setupWatchers() {
                        this.$watch('settings.position', () => this.updatePreview());
                        this.$watch('settings.design.size', () => this.updatePreview());
                        this.$watch('settings.design.opacity', () => this.updatePreview());
                        this.$watch('settings.design.main_btn_color', () => this.updatePreview());
                        this.$watch('settings.animation.type', () => this.updatePreview());
                        this.$watch('settings.channels', () => this.updatePreview(), { deep: true });
                    },

                    // В методе updatePreview()
                    updatePreview() {
                        if (!this.widgetRoot || !this.shadowRoot) return;

                        // 1. Генерируем HTML каналов
                        const channelsHtml = (this.settings.channels || []).filter(c => c.is_active).map(channel => {
                            const colors = { whatsapp: '#25D366', telegram: '#0088cc', phone: '#34b7f1', vk: '#4c75a3' };
                            const bgColor = channel.bg_color || colors[channel.type] || '#555';

                            return `
        <div class="sp-channel-item">
            <span class="sp-channel-label">${this.escapeHtml(channel.label)}</span>
            <div class="sp-channel-icon" style="background-color: ${bgColor}">
                <i class="fab ${this.getIconClass(channel.type)}"></i>
            </div>
        </div>`;
                        }).join('');

                        // 2. Рендерим шаблон
                        let html = this.rawTemplate
                            .replace('{channels_html}', channelsHtml)
                            .replace('{position}', this.settings.position)
                            .replace('{pulse_class}', `sp-anim-${this.settings.animation.type}`);

                        this.widgetRoot.innerHTML = html;

                        // 3. Находим обертку и применяем CSS переменные
                        const wrapper = this.widgetRoot.querySelector('.sp-contact-wrapper');
                        if (wrapper) {
                            const scaleMap = { small: '0.8', medium: '1', large: '1.2' };
                            wrapper.style.setProperty('--main-color', this.settings.design.main_btn_color);
                            wrapper.style.setProperty('--icon-color', this.settings.design.main_icon_color);
                            wrapper.style.setProperty('--btn-opacity', this.settings.design.opacity);
                            wrapper.style.setProperty('--scale-factor', scaleMap[this.settings.design.size] || '1');

                            // Включаем интерактивность для клика в превью
                            const mainBtn = wrapper.querySelector('#sp-main-btn');
                            if (mainBtn) {
                                mainBtn.onclick = (e) => {
                                    e.preventDefault();
                                    wrapper.classList.toggle('is-active');
                                };
                            }
                        }
                    },

                    getIconClass(type) {
                        const icons = {
                            whatsapp: 'fa-whatsapp',
                            telegram: 'fa-telegram',
                            phone: 'fa-phone',
                            vk: 'fa-vk',
                            custom: 'fa-share-alt'
                        };
                        return icons[type] || 'fa-share-alt';
                    },

                    escapeHtml(str) {
                        if (!str) return '';
                        return str.replace(/[&<>]/g, function(m) {
                            if (m === '&') return '&amp;';
                            if (m === '<') return '&lt;';
                            if (m === '>') return '&gt;';
                            return m;
                        });
                    },

                    addChannel() {
                        this.settings.channels.push({
                            id: 'ch_' + Date.now(),
                            type: 'whatsapp',
                            label: 'Новый канал',
                            action_type: 'link',
                            action_value: '',
                            bg_color: '#25D366',
                            icon_color: '#ffffff',
                            is_active: true
                        });
                    },

                    removeChannel(index) {
                        this.settings.channels.splice(index, 1);
                    },

                    showError(message) {
                        const container = document.getElementById('preview-viewport');
                        if (container) {
                            if (container.shadowRoot) {
                                container.shadowRoot.innerHTML = `
                                <style>
                                    :host {
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        background: #f8d7da;
                                        color: #721c24;
                                        padding: 20px;
                                    }
                                </style>
                                <div style="padding: 20px; text-align: center;">
                                    <strong>⚠️ ${message}</strong>
                                </div>
                            `;
                            }
                        }
                    },

                    async saveWidget() {
                        const btn = event?.currentTarget;
                        const originalHtml = btn?.innerHTML || 'Сохранить';

                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохраняем...';
                        }

                        try {
                            const response = await axios.post(window.location.href, {
                                settings: this.settings
                            });

                            if (typeof showNotification === 'function') {
                                showNotification('Настройки мультикнопки сохранены', 'success');
                            } else {
                                alert('Настройки сохранены');
                            }
                        } catch (error) {
                            console.error('Save error:', error);
                            if (typeof showNotification === 'function') {
                                showNotification('Ошибка при сохранении', 'danger');
                            } else {
                                alert('Ошибка при сохранении');
                            }
                        } finally {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = originalHtml;
                            }
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection

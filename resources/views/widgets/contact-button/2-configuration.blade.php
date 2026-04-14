@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="contactButtonEditor({{ json_encode($config) }})">
        <div class="row">
            {{-- КОЛОНКА НАСТРОЕК --}}
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка Мультикнопки: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form action="{{ route('cabinet.sites.widgets.design.update', [$site, $widget]) }}" method="POST" id="saveForm">
                            @csrf

                            {{-- Выбор скина --}}
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

                            {{-- Основные параметры --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Основные параметры</label>

                                <div class="mb-3">
                                    <label class="small">Позиция</label>
                                    <select class="form-select" x-model="settings.position">
                                        <option value="right">Справа внизу</option>
                                        <option value="left">Слева внизу</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Текст подсказки</label>
                                    <input type="text" class="form-control" placeholder="Например: Свяжитесь с нами"
                                           x-model="settings.main_tooltip">
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Задержка появления (сек)</label>
                                    <input type="number" class="form-control" min="0" max="10" step="0.5"
                                           x-model="settings.delay">
                                </div>
                            </div>

                            <hr>

                            {{-- Дизайн --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Внешний вид</label>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="small text-muted">Размер кнопки</label>
                                        <select class="form-select" x-model="settings.design.size">
                                            <option value="small">Маленькая</option>
                                            <option value="medium">Средняя</option>
                                            <option value="large">Большая</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Эффект при наведении</label>
                                        <select class="form-select" x-model="settings.design.hover_effect">
                                            <option value="lift">🚀 Приподнимание</option>
                                            <option value="scale">📏 Увеличение</option>
                                            <option value="glow">✨ Свечение</option>
                                            <option value="rotate">🔄 Поворот</option>
                                            <option value="pulse">💓 Пульсация</option>
                                            <option value="shake">📳 Тряска</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Анимация кнопки</label>
                                    <select class="form-select" x-model="settings.animation.type">
                                        <option value="none">⭕ Нет анимации</option>
                                        <option value="wave">🌊 Волна (расходящиеся круги)</option>
                                        <option value="pulse">💓 Пульсация (изменение размера)</option>
                                        <option value="shake">📳 Тряска (привлечение внимания)</option>
                                        <option value="ring">🔔 Звонок (поворот туда-сюда)</option>
                                        <option value="bounce">🏀 Подпрыгивание</option>
                                        <option value="glow">✨ Пульсирующее свечение</option>
                                        <option value="spin">🔄 Вращение иконки</option>
                                        <option value="heartbeat">💗 Сердцебиение</option>
                                        <option value="flash">⚡ Вспышка</option>
                                        <option value="swing">🎯 Раскачивание</option>
                                        <option value="wobble">🎪 Шаткий эффект</option>
                                        <option value="fade">🌫️ Плавное исчезновение</option>
                                        <option value="rotate">🔄 Медленный поворот</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Прозрачность ({{ number_format($config['settings']['design']['opacity'] ?? 1, 1) }})</label>
                                    <input type="range" class="form-range" min="0.1" max="1" step="0.1"
                                           x-model="settings.design.opacity">
                                </div>
                            </div>

                            <hr>

                            {{-- Цвета --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Цветовая схема</label>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small text-muted">Цвет кнопки</label>
                                        <input type="color" class="form-control form-control-color"
                                               x-model="settings.design.main_color">
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted">Цвет иконки</label>
                                        <input type="color" class="form-control form-control-color"
                                               x-model="settings.design.icon_color">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- Каналы связи с сортировкой --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    Каналы связи
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fa fa-plus me-1"></i> Добавить
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end shadow-lg">
                                            <template x-for="(info, type) in availableTypes" :key="type">
                                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" @click="addChannel(type)">
                                                    <i :class="'fa-fw me-2 ' + info.icon" :style="'color:' + info.color"></i>
                                                    <span x-text="info.name"></span>
                                                </a>
                                            </template>
                                        </div>
                                    </div>
                                </label>

                                <div class="channels-list border rounded p-3 bg-light"
                                     x-ref="channelsList"
                                     style="max-height: 500px; overflow-y: auto;">
                                    <template x-for="(channel, index) in settings.channels" :key="channel.id">
                                        <div class="channel-item bg-white border rounded p-3 mb-3 shadow-sm"
                                             draggable="true"
                                             @dragstart="dragStart($event, index)"
                                             @dragover="dragOver($event, index)"
                                             @dragend="dragEnd($event)">
                                            <div class="row g-2 align-items-start">
                                                <div class="col-auto">
                                                    <div class="drag-handle me-2" style="cursor: grab;">
                                                        <i class="fa fa-grip-vertical text-muted"></i>
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                         :style="`width: 40px; height: 40px; background: ${channel.bg_color}; color: ${channel.icon_color || '#fff'}; cursor: pointer;`"
                                                         @click="$refs['chanColor'+index]?.click()">
                                                        <i :class="getIconClass(channel.type)"></i>
                                                    </div>
                                                    <input type="color" class="d-none" :x-ref="'chanColor'+index"
                                                           x-model="channel.bg_color">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-sm fw-bold mb-2"
                                                           placeholder="Название" x-model="channel.label">
                                                    <input type="text" class="form-control form-control-sm"
                                                           :placeholder="getActionPlaceholder(channel.type)"
                                                           x-model="channel.action_value">
                                                </div>
                                                <div class="col-auto">
                                                    <div class="mb-2">
                                                        <input type="color" class="form-control form-control-color form-control-sm"
                                                               style="width: 40px; height: 40px; padding: 0;"
                                                               x-model="channel.icon_color"
                                                               title="Цвет иконки">
                                                    </div>
                                                    <div class="form-check form-switch mb-2">
                                                        <input class="form-check-input" type="checkbox"
                                                               x-model="channel.is_active">
                                                        <label class="small">Активен</label>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-link text-danger w-100"
                                                            @click="removeChannel(index)">
                                                        <i class="fa fa-trash-can"></i> Удалить
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="settings.channels.length === 0" class="text-center text-muted py-4">
                                        <i class="fa fa-comments fa-2x mb-2 opacity-25"></i>
                                        <p class="small mb-0">Нет добавленных каналов<br>Нажмите "Добавить" чтобы начать</p>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig()">
                                <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ПРЕДПРОСМОТР --}}
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }" x-init="$watch('previewMode', () => $dispatch('preview-mode-changed', previewMode))">
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
                        <div class="browser-mockup" :class="previewMode" id="browser-mockup">
                            <div class="browser-header">
                                <div class="d-flex gap-1">
                                    <span class="dot red"></span>
                                    <span class="dot yellow"></span>
                                    <span class="dot green"></span>
                                </div>
                                <div class="address-bar">
                                    <i class="fa fa-lock me-1 text-success"></i> your-website.com
                                </div>
                                <div class="browser-controls">
                                    <span class="badge bg-secondary" x-text="previewMode === 'desktop' ? '1200px' : '375px'"></span>
                                </div>
                            </div>
                            <div class="browser-viewport" id="browser-viewport" style="min-height: 500px; position: relative; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div id="preview-host"></div>
                                <div class="site-content-placeholder" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; padding: 20px;">
                                    <div class="preview-content" style="color: white; text-align: center; padding-top: 100px;">
                                        <i class="fa fa-globe fa-3x mb-3 opacity-50"></i>
                                        <h4>Пример содержимого сайта</h4>
                                        <p class="small opacity-75">Виджет будет отображаться поверх контента</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .browser-mockup {
                border: 1px solid #d1d1d1;
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
                margin: 0 auto;
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }

            .browser-mockup.desktop { width: 100%; }
            .browser-mockup.mobile { width: 375px; }

            .browser-header {
                background: #f1f1f1;
                padding: 10px 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid #e1e1e1;
            }

            .browser-header .dot {
                height: 12px;
                width: 12px;
                border-radius: 50%;
            }
            .dot.red { background: #ff5f56; }
            .dot.yellow { background: #ffbd2e; }
            .dot.green { background: #27c93f; }

            .browser-header .address-bar {
                background: #fff;
                flex: 1;
                max-width: 400px;
                margin: 0 12px;
                border-radius: 8px;
                font-size: 12px;
                padding: 6px 12px;
                color: #666;
                text-align: center;
                border: 1px solid #e1e1e1;
            }

            .browser-viewport {
                position: relative;
                min-height: 500px;
                background: #f5f5f5;
                overflow: hidden;
            }

            #preview-host {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 10;
            }

            .site-content-placeholder {
                z-index: 1;
            }

            .channel-item {
                border-left: 3px solid #3b82f6;
                transition: all 0.2s ease;
            }

            .channel-item:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }

            .channel-item.dragging {
                opacity: 0.5;
                cursor: grabbing;
            }

            .drag-over {
                border-top: 2px solid #3b82f6;
                margin-top: -1px;
            }
        </style>
    </div>

    @push('js')
        <script>
            function contactButtonEditor(config) {
                return {
                    slug: config.slug,
                    settings: config.settings,
                    skins: config.skins,
                    rawTemplate: '',
                    rawCss: '',
                    shadowRoot: null,
                    widgetRoot: null,
                    dragStartIndex: null,

                    availableTypes: {
                        whatsapp: { name: 'WhatsApp', icon: 'fab fa-whatsapp', color: '#25D366', placeholder: '79001234567' },
                        telegram: { name: 'Telegram', icon: 'fab fa-telegram-plane', color: '#0088cc', placeholder: 'username' },
                        phone: { name: 'Телефон', icon: 'fas fa-phone', color: '#34b7f1', placeholder: '+79001234567' },
                        email: { name: 'Email', icon: 'fas fa-envelope', color: '#ea4335', placeholder: 'mail@example.com' },
                        vk: { name: 'VK', icon: 'fab fa-vk', color: '#0077ff', placeholder: 'club123' },
                        custom: { name: 'Своя ссылка', icon: 'fas fa-link', color: '#6c757d', placeholder: 'https://...' }
                    },

                    async init() {
                        // Инициализация структуры данных
                        if (!this.settings.channels || !Array.isArray(this.settings.channels)) {
                            this.settings.channels = [];
                        }

                        // В функции init() обновите default settings для design
                        if (!this.settings.design) {
                            this.settings.design = {
                                main_color: '#3b82f6',
                                icon_color: '#ffffff',
                                size: 'medium',
                                opacity: 1,
                                hover_effect: 'lift'  // по умолчанию
                            };
                        }

                        if (!this.settings.animation) {
                            this.settings.animation = {
                                type: 'wave',
                                enabled: true
                            };
                        }

                        if (!this.settings.position) {
                            this.settings.position = 'right';
                        }

                        if (!this.settings.main_tooltip) {
                            this.settings.main_tooltip = 'Свяжитесь с нами';
                        }

                        if (!this.settings.delay) {
                            this.settings.delay = 1;
                        }

                        if (!this.settings.template) {
                            this.settings.template = Object.keys(this.skins)[0] || 'default';
                        }

                        await this.loadSkin(this.settings.template);

                        // Watchers для обновления preview
                        this.$watch('settings', () => {
                            this.updatePreview();
                        }, { deep: true });
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

                            // После установки Shadow DOM, применяем текущие настройки
                            this.$nextTick(() => {
                                this.updatePreview();
                            });
                        } catch (e) {
                            console.error('Error loading skin:', e);
                        }
                    },

                    setupShadowDOM() {
                        const container = document.getElementById('preview-host');
                        if (!container) return;

                        this.shadowRoot = container.shadowRoot || container.attachShadow({mode: 'open'});

                        // Просто модифицируем CSS для превью (fixed → absolute)
                        let modifiedCSS = this.rawCss;
                        modifiedCSS = modifiedCSS.replace(/position:\s*fixed/g, 'position: absolute');
                        modifiedCSS = modifiedCSS.replace(/position:fixed/g, 'position: absolute');
                        modifiedCSS = modifiedCSS.replace(/100vh/g, '100%');
                        modifiedCSS = modifiedCSS.replace(/100vw/g, '100%');

                        // Вставляем CSS и HTML как есть, без дополнительных стилей
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
                                    }
                                    #widget-root {
                                        width: 100%;
                                        height: 100%;
                                        position: relative;
                                    }
                                    ${modifiedCSS}
                                </style>
                                <div id="widget-root"></div>
                            `;

                        this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                    },

                    updatePreview() {
                        if (!this.widgetRoot || !this.rawTemplate) return;

                        // Генерация HTML каналов (единая для всех скинов)
                        const channelsHtml = (this.settings.channels || [])
                            .filter(c => c.is_active !== false && c.action_value)
                            .map(c => {
                                let url = '#';
                                const value = c.action_value || '';

                                if (c.type === 'whatsapp') {
                                    url = `https://wa.me/${value.replace(/\D/g, '')}`;
                                } else if (c.type === 'telegram') {
                                    url = `https://t.me/${value.replace('@', '')}`;
                                } else if (c.type === 'phone') {
                                    url = `tel:${value.replace(/\D/g, '')}`;
                                } else if (c.type === 'email') {
                                    url = `mailto:${value}`;
                                } else if (value) {
                                    url = value;
                                }

                                const iconMap = {
                                    whatsapp: '<i class="fab fa-whatsapp"></i>',
                                    telegram: '<i class="fab fa-telegram-plane"></i>',
                                    phone: '<i class="fas fa-phone"></i>',
                                    email: '<i class="fas fa-envelope"></i>',
                                    vk: '<i class="fab fa-vk"></i>',
                                    custom: '<i class="fas fa-link"></i>'
                                };

                                return `
                                    <a href="${url}" class="sp-channel-item" target="_blank">
                                        <div class="sp-channel-icon" style="background-color: ${c.bg_color}; color: ${c.icon_color || '#fff'}">
                                            ${iconMap[c.type] || iconMap.custom}
                                        </div>
                                        <span class="sp-channel-label">${this.escapeHtml(c.label || c.type)}</span>
                                    </a>
                                `;
                            }).join('');

                        // Подставляем значения в шаблон скина
                        let html = this.rawTemplate
                            .replace(/\{position\}/g, this.settings.position)
                            .replace(/\{channels_html\}/g, channelsHtml || '')
                            .replace(/\{main_tooltip\}/g, this.escapeHtml(this.settings.main_tooltip || ''))
                            .replace(/\{widget_id\}/g, 'preview');

                        this.widgetRoot.innerHTML = html;

                        // Применяем CSS переменные (единые для всех скинов)
                        const widgetElement = this.widgetRoot.firstElementChild;
                        if (widgetElement) {
                            const mainColor = this.settings.design.main_color;
                            const rgb = this.hexToRgb(mainColor);

                            widgetElement.style.setProperty('--main-color', mainColor);
                            widgetElement.style.setProperty('--main-color-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
                            widgetElement.style.setProperty('--icon-color', this.settings.design.icon_color);
                            widgetElement.style.setProperty('--scale-factor', this.getScaleFactor(this.settings.design.size));
                            widgetElement.style.setProperty('--btn-opacity', this.settings.design.opacity);

                            // Добавляем классы для CSS (если скин их использует)
                            widgetElement.classList.add(`sp-size-${this.settings.design.size}`);

                            if (this.settings.design.hover_effect && this.settings.design.hover_effect !== 'none') {
                                widgetElement.classList.add(`sp-hover-${this.settings.design.hover_effect}`);
                            }

                            if (this.settings.animation.type && this.settings.animation.type !== 'none') {
                                widgetElement.classList.add(`sp-animation-${this.settings.animation.type}`);
                            }

                            // Вся логика интерактивности в самом скине через data-атрибуты
                            this.attachSkinEvents(widgetElement);
                        }
                    },

                    attachSkinEvents(widgetElement) {
                        // Универсальная логика: ищем элементы с data-атрибутами
                        const toggleBtn = widgetElement.querySelector('[data-sp-toggle]');
                        const closeBtn = widgetElement.querySelector('[data-sp-close]');
                        const overlay = widgetElement.querySelector('[data-sp-overlay]');

                        if (toggleBtn) {
                            const newBtn = toggleBtn.cloneNode(true);
                            toggleBtn.parentNode.replaceChild(newBtn, toggleBtn);
                            newBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                widgetElement.classList.toggle('sp-active');
                            });
                        }

                        if (closeBtn) {
                            const newBtn = closeBtn.cloneNode(true);
                            closeBtn.parentNode.replaceChild(newBtn, closeBtn);
                            newBtn.addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                widgetElement.classList.remove('sp-active');
                            });
                        }

                        if (overlay) {
                            const newOverlay = overlay.cloneNode(true);
                            overlay.parentNode.replaceChild(newOverlay, overlay);
                            newOverlay.addEventListener('click', (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                widgetElement.classList.remove('sp-active');
                            });
                        }

                        // Автоматическая демонстрация для preview
                        setTimeout(() => {
                            widgetElement.classList.add('sp-active');
                            setTimeout(() => widgetElement.classList.remove('sp-active'), 3000);
                        }, 500);
                    },

                    async applyTemplate(id) {
                        if (this.settings.template === id) return;
                        this.settings.template = id;
                        await this.loadSkin(id);
                    },

                    addChannel(type) {
                        const proto = this.availableTypes[type];
                        if (!Array.isArray(this.settings.channels)) {
                            this.settings.channels = [];
                        }
                        this.settings.channels.push({
                            id: 'ch_' + Date.now(),
                            type: type,
                            label: proto.name,
                            action_value: '',
                            bg_color: proto.color,
                            icon_color: '#ffffff',
                            is_active: true
                        });
                    },

                    removeChannel(index) {
                        this.settings.channels.splice(index, 1);
                    },

                    getIconClass(type) {
                        return this.availableTypes[type]?.icon || 'fas fa-link';
                    },

                    getActionPlaceholder(type) {
                        return this.availableTypes[type]?.placeholder || 'Введите значение';
                    },

                    getScaleFactor(size) {
                        const sizes = { small: '0.8', medium: '1', large: '1.2' };
                        return sizes[size] || '1';
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

                    escapeHtml(text) {
                        if (!text) return '';
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    },

                    // Drag and drop сортировка
                    dragStart(event, index) {
                        this.dragStartIndex = index;
                        event.dataTransfer.effectAllowed = 'move';
                        event.target.classList.add('dragging');
                    },

                    dragOver(event, index) {
                        event.preventDefault();
                        if (this.dragStartIndex === null) return;

                        const dragOverIndex = index;
                        if (dragOverIndex === this.dragStartIndex) return;

                        // Перемещаем элемент
                        const channels = [...this.settings.channels];
                        const draggedItem = channels[this.dragStartIndex];
                        channels.splice(this.dragStartIndex, 1);
                        channels.splice(dragOverIndex, 0, draggedItem);
                        this.settings.channels = channels;
                        this.dragStartIndex = dragOverIndex;
                    },

                    dragEnd(event) {
                        this.dragStartIndex = null;
                        event.target.classList.remove('dragging');
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

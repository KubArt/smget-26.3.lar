@extends('cabinet.widgets.design')

@section('widget_editor')
    <div x-data="widgetEditor" x-init="init" class="content">
        <div class="row">
            <!-- Настройки -->
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form id="saveForm" method="POST">
                        @csrf

                        <!-- Выбор скина -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Макет</label>
                                <div class="row g-2">
                                    <template x-for="skin in skins" :key="skin.slug">
                                        <div class="col-6">
                                            <button type="button"
                                                    class="btn btn-sm w-100 border p-2 text-start"
                                                    :class="selectedSkin === skin.slug ? 'btn-primary' : 'btn-light'"
                                                    @click="selectSkin(skin.slug)">
                                                <i class="fa fa-paint-brush me-2"></i>
                                                <span x-text="skin.name"></span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <hr>

                            <!-- Основные параметры -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Параметры</label>

                                <div class="mb-2">
                                    <label class="small">Позиция</label>
                                    <select class="form-select" x-model="settings.position">
                                        <option value="bottom-right">Справа внизу</option>
                                        <option value="bottom-left">Слева внизу</option>
                                        <option value="top-right">Справа вверху</option>
                                        <option value="top-left">Слева вверху</option>
                                    </select>
                                </div>

                                <div class="mb-2">
                                    <label class="small">Подсказка</label>
                                    <input type="text" class="form-control" x-model="settings.main_tooltip">
                                </div>

                                <div class="mb-2">
                                    <label class="small">Задержка (сек)</label>
                                    <input type="number" class="form-control" min="0" max="10" step="0.5" x-model="settings.delay">
                                </div>
                            </div>

                            <hr>

                            <!-- Дизайн -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Дизайн</label>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small">Размер</label>
                                        <select class="form-select" x-model="settings.design.size">
                                            <option value="small">Маленький</option>
                                            <option value="medium">Средний</option>
                                            <option value="large">Большой</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="small">Эффект при наведении</label>
                                        <select class="form-select" x-model="settings.design.hover_effect">
                                            <option value="lift">Подъем</option>
                                            <option value="scale">Увеличение</option>
                                            <option value="glow">Свечение</option>
                                            <option value="rotate">Поворот</option>
                                            <option value="pulse">Пульсация</option>
                                            <option value="shake">Тряска</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="small">Анимация</label>
                                    <select class="form-select" x-model="settings.animation.type">
                                        <option value="none">Нет</option>
                                        <option value="wave">Волна</option>
                                        <option value="pulse">Пульсация</option>
                                        <option value="shake">Тряска</option>
                                        <option value="ring">Звонок</option>
                                        <option value="bounce">Подпрыгивание</option>
                                        <option value="glow">Свечение</option>
                                        <option value="spin">Вращение</option>
                                        <option value="heartbeat">Сердцебиение</option>
                                        <option value="flash">Вспышка</option>
                                        <option value="swing">Раскачивание</option>
                                        <option value="wobble">Шаткий</option>
                                        <option value="fade">Исчезание</option>
                                        <option value="rotate">Вращение</option>
                                    </select>
                                </div>

                                <div class="mt-2">
                                    <label class="small">Прозрачность: <span x-text="settings.design.opacity"></span></label>
                                    <input type="range" class="form-range" min="0.1" max="1" step="0.1" x-model="settings.design.opacity">
                                </div>
                            </div>

                            <hr>

                            <!-- Цвета -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Цвета</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="small">Основной</label>
                                        <input type="color" class="form-control" x-model="settings.design.main_color">
                                    </div>
                                    <div class="col-6">
                                        <label class="small">Иконки</label>
                                        <input type="color" class="form-control" x-model="settings.design.icon_color">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Каналы связи -->
                            <div class="mb-4">
                                <label class="form-label fw-bold d-flex justify-content-between">
                                    Каналы связи
                                    <button type="button" class="btn btn-sm btn-primary" @click="addChannel">
                                        <i class="fa fa-plus"></i> Добавить
                                    </button>
                                </label>

                                <div class="channels-list" x-sortable="settings.channels">
                                    <template x-for="(channel, index) in settings.channels" :key="channel.id">
                                        <div class="border rounded p-2 mb-2 bg-white" x-sortable-item>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-auto">
                                                    <i class="fa fa-grip-vertical text-muted cursor-grab" x-sortable-handle></i>
                                                </div>
                                                <div class="col-auto">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width: 40px; height: 40px; cursor: pointer"
                                                         :style="`background: ${channel.bg_color}`"
                                                         @click="$refs['color'+index].click()">
                                                        <i :class="getIcon(channel.type)"></i>
                                                    </div>
                                                    <input type="color" class="d-none" :x-ref="'color'+index" x-model="channel.bg_color">
                                                </div>
                                                <div class="col">
                                                    <input type="text" class="form-control form-control-sm mb-1" placeholder="Название" x-model="channel.label">
                                                    <input type="text" class="form-control form-control-sm" :placeholder="getPlaceholder(channel.type)" x-model="channel.action_value">
                                                </div>
                                                <div class="col-auto">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" x-model="channel.is_active">
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-link text-danger mt-1" @click="removeChannel(index)">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="settings.channels.length === 0" class="text-center text-muted py-4">
                                    <i class="fa fa-comments fa-2x mb-2"></i>
                                    <p class="small">Нет каналов. Нажмите "Добавить"</p>
                                </div>
                            </div>

                            <hr>

                            <button type="button" class="btn btn-primary w-100" @click="save">
                                <i class="fa fa-save me-1"></i> Сохранить
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Предпросмотр -->
            <div class="col-md-7">
                <div class="block block-rounded sticky-top" style="top: 20px;">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Предпросмотр</h3>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'desktop' ? 'active' : ''" @click="previewMode = 'desktop'">
                                <i class="fa fa-desktop"></i> ПК
                            </button>
                            <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'mobile' ? 'active' : ''" @click="previewMode = 'mobile'">
                                <i class="fa fa-mobile-alt"></i> Моб
                            </button>
                        </div>
                    </div>
                    <div class="block-content p-3 bg-dark">
                        <div class="browser-mockup" :class="previewMode">
                            <div class="browser-header">
                                <div class="d-flex gap-1">
                                    <span class="dot red"></span>
                                    <span class="dot yellow"></span>
                                    <span class="dot green"></span>
                                </div>
                                <div class="address-bar">your-website.com</div>
                            </div>
                            <div class="browser-viewport" id="preview-container" style="min-height: 500px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div id="preview-root"></div>
                                <div class="preview-overlay">
                                    <i class="fa fa-globe fa-3x mb-2 opacity-25"></i>
                                    <p class="small">Пример содержимого сайта</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .browser-mockup {
                border-radius: 12px;
                background: #fff;
                overflow: hidden;
                transition: width 0.3s;
                margin: 0 auto;
            }
            .browser-mockup.desktop { width: 100%; }
            .browser-mockup.mobile { width: 375px; }
            .browser-header {
                background: #f1f1f1;
                padding: 10px 16px;
                display: flex;
                gap: 12px;
                align-items: center;
            }
            .browser-header .dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
            }
            .dot.red { background: #ff5f56; }
            .dot.yellow { background: #ffbd2e; }
            .dot.green { background: #27c93f; }
            .address-bar {
                background: #fff;
                flex: 1;
                border-radius: 8px;
                font-size: 12px;
                padding: 6px 12px;
                text-align: center;
                color: #666;
            }
            .browser-viewport {
                position: relative;
                min-height: 500px;
                overflow: hidden;
            }
            #preview-root {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 10;
            }
            .preview-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: white;
                pointer-events: none;
                z-index: 1;
            }
            [x-sortable-item] {
                cursor: default;
            }
            [x-sortable-handle] {
                cursor: grab;
            }
            [x-sortable-handle]:active {
                cursor: grabbing;
            }
        </style>

        @push('js')
            <script>
                function widgetEditor() {
                    return {
                        // Состояние
                        skins: {{ json_encode($config['skins']) }},
                        selectedSkin: '{{ $config['settings']['template'] ?? 'default' }}',
                        settings: {{ json_encode($config['settings']) }},
                        previewMode: 'desktop',

                        // Внутреннее состояние
                        currentSkinHtml: '',
                        currentSkinCss: '',
                        shadowRoot: null,
                        previewRoot: null,

                        // Типы каналов
                        channelTypes: {
                            whatsapp: { name: 'WhatsApp', icon: 'fab fa-whatsapp', color: '#25D366', placeholder: '79001234567' },
                            telegram: { name: 'Telegram', icon: 'fab fa-telegram-plane', color: '#0088cc', placeholder: 'username' },
                            phone: { name: 'Телефон', icon: 'fas fa-phone', color: '#34b7f1', placeholder: '+79001234567' },
                            email: { name: 'Email', icon: 'fas fa-envelope', color: '#ea4335', placeholder: 'mail@example.com' },
                            vk: { name: 'VK', icon: 'fab fa-vk', color: '#0077ff', placeholder: 'club123' },
                            custom: { name: 'Ссылка', icon: 'fas fa-link', color: '#6c757d', placeholder: 'https://...' }
                        },

                        async init() {
                            // Инициализация дефолтных значений
                            if (!this.settings.channels) this.settings.channels = [];
                            if (!this.settings.design) {
                                this.settings.design = {
                                    main_color: '#3b82f6',
                                    icon_color: '#ffffff',
                                    size: 'medium',
                                    opacity: 1,
                                    hover_effect: 'lift'
                                };
                            }
                            if (!this.settings.animation) {
                                this.settings.animation = { type: 'wave', enabled: true };
                            }
                            if (!this.settings.position) this.settings.position = 'bottom-right';
                            if (!this.settings.main_tooltip) this.settings.main_tooltip = 'Свяжитесь с нами';
                            if (!this.settings.delay) this.settings.delay = 1;

                            // Загружаем скин
                            await this.loadSkin(this.selectedSkin);

                            // Наблюдаем за изменениями
                            this.$watch('settings', () => this.updatePreview(), { deep: true });
                            this.$watch('selectedSkin', (skin) => this.loadSkin(skin));
                        },

                        async loadSkin(skinId) {
                            try {
                                const baseUrl = `/widgets/contact-button/skins/${skinId}`;
                                const [htmlRes, cssRes] = await Promise.all([
                                    fetch(`${baseUrl}/template.html`),
                                    fetch(`${baseUrl}/style.css`)
                                ]);
                                this.currentSkinHtml = await htmlRes.text();
                                this.currentSkinCss = await cssRes.text();
                                this.initPreview();
                                this.updatePreview();
                            } catch (e) {
                                console.error('Error loading skin:', e);
                            }
                        },

                        initPreview() {
                            const container = document.getElementById('preview-root');
                            if (!container) return;

                            this.shadowRoot = container.shadowRoot || container.attachShadow({ mode: 'open' });

                            // Модифицируем CSS для preview
                            let css = this.currentSkinCss;
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

                            this.previewRoot = this.shadowRoot.getElementById('widget-root');
                        },

                        updatePreview() {
                            if (!this.previewRoot || !this.currentSkinHtml) return;

                            // Генерируем HTML каналов
                            const channelsHtml = this.settings.channels
                                .filter(c => c.is_active !== false && c.action_value)
                                .map(c => {
                                    let url = '#';
                                    const value = c.action_value || '';

                                    if (c.type === 'whatsapp') url = `https://wa.me/${value.replace(/\D/g, '')}`;
                                    else if (c.type === 'telegram') url = `https://t.me/${value.replace('@', '')}`;
                                    else if (c.type === 'phone') url = `tel:${value.replace(/\D/g, '')}`;
                                    else if (c.type === 'email') url = `mailto:${value}`;
                                    else if (value) url = value;

                                    return `
                                <a href="${url}" class="sp-channel-item" target="_blank">
                                    <div class="sp-channel-icon" style="background: ${c.bg_color}; color: ${c.icon_color || '#fff'}">
                                        <i class="${this.getIcon(c.type)}"></i>
                                    </div>
                                    <span class="sp-channel-label">${this.escapeHtml(c.label || c.type)}</span>
                                </a>
                            `;
                                }).join('');

                            // Подставляем в шаблон
                            let html = this.currentSkinHtml
                                .replace(/\{position\}/g, this.settings.position)
                                .replace(/\{channels_html\}/g, channelsHtml)
                                .replace(/\{main_tooltip\}/g, this.escapeHtml(this.settings.main_tooltip))
                                .replace(/\{widget_id\}/g, 'preview');

                            this.previewRoot.innerHTML = html;

                            // Применяем CSS переменные
                            const widget = this.previewRoot.firstElementChild;
                            if (widget) {
                                const rgb = this.hexToRgb(this.settings.design.main_color);
                                widget.style.setProperty('--main-color', this.settings.design.main_color);
                                widget.style.setProperty('--main-color-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
                                widget.style.setProperty('--icon-color', this.settings.design.icon_color);
                                widget.style.setProperty('--scale-factor', this.getScaleFactor(this.settings.design.size));
                                widget.style.setProperty('--btn-opacity', this.settings.design.opacity);

                                // Добавляем классы
                                widget.classList.add(`sp-size-${this.settings.design.size}`);
                                if (this.settings.design.hover_effect !== 'none') {
                                    widget.classList.add(`sp-hover-${this.settings.design.hover_effect}`);
                                }
                                if (this.settings.animation.type !== 'none') {
                                    widget.classList.add(`sp-animation-${this.settings.animation.type}`);
                                }

                                // Навешиваем события
                                this.attachEvents(widget);
                            }
                        },

                        attachEvents(widget) {
                            // Универсальные события через data-атрибуты
                            const toggle = widget.querySelector('[data-sp-toggle]');
                            const close = widget.querySelector('[data-sp-close]');
                            const overlay = widget.querySelector('[data-sp-overlay]');

                            if (toggle) {
                                const clone = toggle.cloneNode(true);
                                toggle.parentNode.replaceChild(clone, toggle);
                                clone.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    widget.classList.toggle('sp-active');
                                });
                            }

                            if (close) {
                                const clone = close.cloneNode(true);
                                close.parentNode.replaceChild(clone, close);
                                clone.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    widget.classList.remove('sp-active');
                                });
                            }

                            if (overlay) {
                                const clone = overlay.cloneNode(true);
                                overlay.parentNode.replaceChild(clone, overlay);
                                clone.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    widget.classList.remove('sp-active');
                                });
                            }

                            // Автопоказ для preview
                            setTimeout(() => {
                                widget.classList.add('sp-active');
                                setTimeout(() => widget.classList.remove('sp-active'), 3000);
                            }, 500);
                        },

                        selectSkin(skinId) {
                            this.selectedSkin = skinId;
                            this.settings.template = skinId;
                        },

                        addChannel() {
                            const types = Object.keys(this.channelTypes);
                            const firstType = types[0];
                            const proto = this.channelTypes[firstType];
                            this.settings.channels.push({
                                id: Date.now(),
                                type: firstType,
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

                        getIcon(type) {
                            return this.channelTypes[type]?.icon || 'fas fa-link';
                        },

                        getPlaceholder(type) {
                            return this.channelTypes[type]?.placeholder || 'Введите значение';
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
                            const int = parseInt(hex, 16);
                            return { r: (int >> 16) & 255, g: (int >> 8) & 255, b: int & 255 };
                        },

                        escapeHtml(str) {
                            if (!str) return '';
                            const div = document.createElement('div');
                            div.textContent = str;
                            return div.innerHTML;
                        },

                        async save() {
                            const btn = event.currentTarget;
                            const original = btn.innerHTML;
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Сохранение...';

                            try {
                                await axios.post(window.location.href, { settings: this.settings });
                                if (typeof showNotification === 'function') {
                                    showNotification('Сохранено', 'success');
                                } else {
                                    alert('Сохранено!');
                                }
                            } catch (e) {
                                alert('Ошибка сохранения');
                            } finally {
                                btn.disabled = false;
                                btn.innerHTML = original;
                            }
                        }
                    };
                }

                // Alpine.js sortable plugin
                document.addEventListener('alpine:init', () => {
                    Alpine.directive('sortable', (el, { value, expression }, { evaluate }) => {
                        let items = evaluate(expression);
                        let dragIndex = null;

                        el.querySelectorAll('[x-sortable-item]').forEach(item => {
                            item.setAttribute('draggable', 'true');

                            item.addEventListener('dragstart', (e) => {
                                dragIndex = parseInt(item.dataset.index);
                                e.dataTransfer.effectAllowed = 'move';
                                item.classList.add('opacity-50');
                            });

                            item.addEventListener('dragend', (e) => {
                                item.classList.remove('opacity-50');
                                dragIndex = null;
                            });

                            item.addEventListener('dragover', (e) => {
                                e.preventDefault();
                                e.dataTransfer.dropEffect = 'move';
                            });

                            item.addEventListener('drop', (e) => {
                                e.preventDefault();
                                const dropIndex = parseInt(item.dataset.index);
                                if (dragIndex !== null && dragIndex !== dropIndex) {
                                    const newItems = [...items];
                                    const [moved] = newItems.splice(dragIndex, 1);
                                    newItems.splice(dropIndex, 0, moved);
                                    evaluate(expression + ' = ' + JSON.stringify(newItems));
                                }
                            });
                        });
                    });
                });
            </script>
    @endpush
@endsection

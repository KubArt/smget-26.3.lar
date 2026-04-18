@push('js')
    <script>
        // widget-editor-base.js - Базовый класс для всех редакторов виджетов

        class WidgetEditorBase {
            constructor(config) {
                // Основные данные
                this.slug = config.slug;
                this.settings = config.settings;
                this.skins = config.skins;
                // Состояние для предпросмотра
                this.rawTemplate = '';
                this.rawCss = '';
                this.shadowRoot = null;
                this.widgetRoot = null;

                // Alpine.js reactive properties
                this.previewMode = 'desktop';

                // Дополнительные состояния
                this.isLoading = false;
                this.initialized = false;
            }

            // ============ ОБЯЗАТЕЛЬНЫЕ МЕТОДЫ ============

            /**
             * Инициализация Alpine компонента
             * Должен быть вызван из x-init
             */
            async init() {
                this.initDefaultSettings();
                await this.loadSkin(this.settings.template);
                this.setupWatchers();
                this.initialized = true;
            }
            /**
             * Инициализация настроек по умолчанию
             * ДОЛЖЕН быть переопределен в дочернем классе
             */
            initDefaultSettings() {
                throw new Error('initDefaultSettings() must be implemented in child class');
            }

            /**
             * Загрузка скина (HTML + CSS)
             */
            async loadSkin(skinId) {
                try {
                    this.isLoading = true;
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
                    this.showError('Не удалось загрузить скин');
                } finally {
                    this.isLoading = false;
                }
            }

            /**
             * Инициализация Shadow DOM для предпросмотра
             */
            initPreview() {
                const container = document.getElementById('preview-host');
                if (!container) return;

                this.shadowRoot = container.shadowRoot || container.attachShadow({ mode: 'open' });

                // Адаптация CSS для предпросмотра (fixed -> absolute)
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
                        /* Базовые сбросы для предпросмотра */
                        * {
                            box-sizing: border-box;
                        }
                        ${css}
                    </style>
                    <div id="widget-root"></div>
                `;
                this.widgetRoot = this.shadowRoot.getElementById('widget-root');
            }

            /**
             * Полное обновление предпросмотра
             * ДОЛЖЕН быть переопределен в дочернем классе
             */
            updatePreview() {
                throw new Error('updatePreview() must be implemented in child class');
            }

            /**
             * Применение выбранного скина
             */
            async applyTemplate(skinId) {
                if (this.settings.template === skinId) return;
                this.settings.template = skinId;
                await this.loadSkin(skinId);
            }

            /**
             * Сохранение конфигурации
             */
            async saveConfig(event) {
                const btn = event?.currentTarget;
                const originalHtml = btn?.innerHTML;

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';
                }

                try {
                    const response = await axios.post(window.location.href, { settings: this.settings });

                    if (response.data.status === 'success') {
                        this.showNotification(response.data.message, 'success');
                    } else {
                        throw new Error(response.data.message || 'Ошибка при сохранении');
                    }
                } catch (error) {
                    const msg = error.response?.data?.message || error.message || 'Ошибка при сохранении';
                    this.showNotification(msg, 'danger');
                } finally {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                }
            }

            /**
             * Безопасное экранирование HTML
             */
            escapeHtml(str) {
                if (!str) return '';
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            /**
             * Конвертация HEX в RGB
             */
            hexToRgb(hex) {
                hex = hex.replace(/^#/, '');
                if (hex.length === 3) {
                    hex = hex.split('').map(c => c + c).join('');
                }
                const int = parseInt(hex, 16);
                return { r: (int >> 16) & 255, g: (int >> 8) & 255, b: int & 255 };
            }

            // ============ ОПЦИОНАЛЬНЫЕ МЕТОДЫ (можно переопределить) ============

            /**
             * Настройка watchers для автоматического обновления предпросмотра
             */
            setupWatchers() {
                // Базовые watchers для всех виджетов
                this.$watch('settings.template', () => this.applyTemplate(this.settings.template));
                this.$watch('settings', () => this.updatePreview(), { deep: true });

                // Дополнительные watchers можно добавить в дочернем классе
                this.setupCustomWatchers();
            }

            /**
             * Настройка кастомных watchers
             * (переопределить при необходимости)
             */
            setupCustomWatchers() {
                // Пустая реализация по умолчанию
            }

            /**
             * Обновление только контента (без полного перестроения)
             * (переопределить при необходимости)
             */
            updateContent() {
                // Базовая реализация - полное обновление
                this.updatePreview();
            }

            /**
             * Привязка событий к DOM элементам виджета
             */
            attachEvents(widget) {
                // Базовая реализация для toggle/close кнопок
                const toggleBtn = widget.querySelector('[data-widget-toggle]');
                const closeBtn = widget.querySelector('[data-widget-close]');
                const overlay = widget.querySelector('[data-widget-overlay]');

                if (toggleBtn) {
                    const clone = toggleBtn.cloneNode(true);
                    toggleBtn.parentNode.replaceChild(clone, toggleBtn);
                    clone.addEventListener('click', (e) => {
                        e.preventDefault();
                        widget.classList.toggle('active');
                    });
                }

                if (closeBtn) {
                    const clone = closeBtn.cloneNode(true);
                    closeBtn.parentNode.replaceChild(clone, closeBtn);
                    clone.addEventListener('click', (e) => {
                        e.preventDefault();
                        widget.classList.remove('active');
                    });
                }

                if (overlay) {
                    const clone = overlay.cloneNode(true);
                    overlay.parentNode.replaceChild(clone, overlay);
                    clone.addEventListener('click', (e) => {
                        e.preventDefault();
                        widget.classList.remove('active');
                    });
                }
            }

            /**
             * Показать уведомление
             */
            showNotification(message, type = 'success') {
                if (typeof window.showNotification === 'function') {
                    window.showNotification(message, type);
                } else {
                    alert(message);
                }
            }

            /**
             * Показать ошибку
             */
            showError(message) {
                this.showNotification(message, 'danger');
            }

            /**
             * Получение размера для масштабирования
             */
            getScaleFactor(size) {
                const sizes = { small: '0.8', medium: '1', large: '1.2' };
                return sizes[size] || '1';
            }

            /**
             * Обновление режима предпросмотра (десктоп/мобильный)
             */
            updatePreviewMode(mode) {
                this.previewMode = mode;
                // Диспатчим событие для обновления стилей в preview.blade.php
                window.dispatchEvent(new CustomEvent('preview-mode-changed', { detail: mode }));
            }
        }

        // ============ АДАПТЕР ДЛЯ ALPINE.JS ============

        /**
         * Создает Alpine компонент на основе базового класса
         * @param {typeof WidgetEditorBase} EditorClass - класс редактора
         * @returns {Function} Alpine component function
         */
        function createAlpineComponent(EditorClass) {
            return function(config) {
                const editor = new EditorClass(config);

                // Обертка для Alpine.js
                const alpineWrapper = {
                    // Проксируем все свойства
                    get slug() { return editor.slug; },
                    get settings() { return editor.settings; },
                    get skins() { return editor.skins; },
                    get previewMode() { return editor.previewMode; },
                    set previewMode(value) {
                        editor.previewMode = value;
                        editor.updatePreviewMode(value);
                    },

                    // Проксируем все методы
                    init: () => editor.init(),
                    applyTemplate: (skinId) => editor.applyTemplate(skinId),
                    saveConfig: (event) => editor.saveConfig(event),
                    updatePreviewMode: (mode) => editor.updatePreviewMode(mode),

                    // Дополнительные методы, которые могут быть переопределены
                    ...editor.getCustomMethods?.() || {}
                };

                // Добавляем Alpine特有的 $watch функциональность
                alpineWrapper.$watch = function(path, callback, options) {
                    // Базовая реализация $watch для Alpine
                    // В реальном использовании Alpine.js сам обрабатывает $watch
                    if (typeof callback === 'function') {
                        // Простейшая реализация через Proxy
                        const watched = this;
                        let value = this[path.split('.')[0]];
                        setInterval(() => {
                            const newValue = this[path.split('.')[0]];
                            if (JSON.stringify(value) !== JSON.stringify(newValue)) {
                                callback(newValue, value);
                                value = newValue;
                            }
                        }, 100);
                    }
                };

                return alpineWrapper;
            };
        }

    </script>
@endpush

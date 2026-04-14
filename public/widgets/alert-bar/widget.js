/**
 * Виджет "Alert Bar" (Информационная полоса)
 * Адаптирован под базовый класс SmWidget
 */
window.SmWidget_alert_bar = class extends SmWidget {
    constructor(settings, id, assets) {
        super(settings, id, assets);
        this.storageKey = `sm_alert_${this.id}_closed`;
        this.placeholder = null;
        this.resizeObserver = null;
    }

    /**
     * Переопределяем init для проверки частоты показа
     */
    init() {
        if (this.shouldHide()) return;
        super.init(); // Вызывает логику задержки и скролла из SmWidget
    }

    /**
     * Проверка, нужно ли скрыть виджет (на основе localStorage/sessionStorage)
     */
    shouldHide() {
        const now = new Date().getTime();
        const closedData = localStorage.getItem(this.storageKey) || sessionStorage.getItem(this.storageKey);

        if (closedData) {
            const closeBehavior = this.settings.close_behavior || 'hide_forever';
            const frequency = this.settings.frequency || 'once_session';

            if (closeBehavior === 'hide_forever') return true;
            if (frequency === 'once_day') {
                if (now - parseInt(closedData) < 24 * 60 * 60 * 1000) return true;
            }
            if (frequency === 'once_session' && sessionStorage.getItem(this.storageKey)) return true;
        }
        return false;
    }

    /**
     * Основной метод отрисовки виджета
     */
    mount() {
        const design = this.settings.design || {};
        const content = this.settings.content || {};

        // 1. Инжекция стилей с CSS переменными
        const styleId = `sp-style-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;

            const bgColor = design.bg_color || '#E63946';
            const textColor = design.text_color || '#FFFFFF';
            const btnColor = design.btn_color || '#1D3557';

            style.textContent = `
                :root {
                    --bg-color: ${bgColor};
                    --text-color: ${textColor};
                    --btn-color: ${btnColor};
                }
                ${this.assets.css}
            `;
            document.head.appendChild(style);
        }

        // 2. Подготовка HTML с заменой плейсхолдеров
        let html = this.assets.html
            .replace(/\{text\}/g, this.escapeHtml(this.settings.text || ''))
            .replace(/\{link\}/g, this.settings.link || '#')
            .replace(/\{btn_text\}/g, this.escapeHtml(this.settings.btn_text || 'Подробнее'))
            .replace(/\{position\}/g, this.settings.position || 'top')
            .replace(/\{widget_id\}/g, this.id);

        // Обработка кнопки (скрываем если has_button === false)
        const hasButton = this.settings.has_button !== false;
        if (!hasButton) {
            html = html.replace('{display_button}', 'hidden-btn');
        } else {
            html = html.replace('{display_button}', '');
        }

        // 3. Создание контейнера в зависимости от позиции
        const position = this.settings.position || 'top';
        const fixedOnScroll = this.settings.fixed_on_scroll === true;

        if (position === 'bottom') {
            // Нижнее положение - всегда fixed с анимацией
            this.createBottomAlert(html);
        } else {
            // Верхнее положение
            if (fixedOnScroll) {
                this.createFixedTopAlert(html);
            } else {
                this.createStaticTopAlert(html);
            }
        }

        // 4. Настройка событий после рендера
        if (this.container) {
            this.bindEvents();
            this.handleAutoHide();
            this.track('view');
        }
    }

    /**
     * Создание нижнего виджета (fixed, анимация снизу)
     */
    createBottomAlert(html) {
        const container = document.createElement('div');
        container.id = `sm-widget-${this.id}`;
        container.innerHTML = html;
        container.style.cssText = `
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999998;
            transform: translateY(100%);
            transition: transform 0.4s ease-in-out;
            pointer-events: none;
        `;
        document.body.appendChild(container);

        this.container = container.firstElementChild;
        if (this.container) {
            this.container.style.pointerEvents = 'auto';
        }

        // Анимация появления
        setTimeout(() => {
            container.style.transform = 'translateY(0)';
        }, 100);
    }

    /**
     * Создание верхнего фиксированного виджета (с placeholder)
     */
    createFixedTopAlert(html) {
        // Создаем placeholder для сохранения места
        this.placeholder = document.createElement('div');
        this.placeholder.style.display = 'none';
        document.body.insertBefore(this.placeholder, document.body.firstChild);

        // Создаем контейнер
        const container = document.createElement('div');
        container.id = `sm-widget-${this.id}`;
        container.innerHTML = html;
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999998;
            transform: translateY(-100%);
            transition: transform 0.4s ease-in-out;
            pointer-events: none;
        `;
        document.body.appendChild(container);

        this.container = container.firstElementChild;
        if (this.container) {
            this.container.style.pointerEvents = 'auto';
        }

        // Анимация появления
        setTimeout(() => {
            container.style.transform = 'translateY(0)';
        }, 100);

        // Обновление высоты placeholder
        const updatePlaceholder = () => {
            if (!this.container) return;
            const height = this.container.offsetHeight;
            if (height > 0 && this.placeholder) {
                this.placeholder.style.display = 'block';
                this.placeholder.style.height = height + 'px';
                this.placeholder.style.width = '100%';
            }
        };

        setTimeout(updatePlaceholder, 150);
        this.resizeObserver = new ResizeObserver(updatePlaceholder);
        this.resizeObserver.observe(this.container);
    }

    /**
     * Создание верхнего статического виджета (в потоке документа)
     */
    createStaticTopAlert(html) {
        const container = document.createElement('div');
        container.id = `sm-widget-${this.id}`;
        container.innerHTML = html;
        container.style.cssText = `
            position: relative;
            width: 100%;
            z-index: 999998;
            pointer-events: none;
        `;
        document.body.insertBefore(container, document.body.firstChild);

        this.container = container.firstElementChild;
        if (this.container) {
            this.container.style.pointerEvents = 'auto';
        }
    }

    /**
     * Применение CSS переменных (если нужно переопределить)
     */
    applyDesign() {
        const design = this.settings.design || {};
        if (!this.container) return;

        this.container.style.setProperty('--bg-color', design.bg_color || '#E63946');
        this.container.style.setProperty('--text-color', design.text_color || '#FFFFFF');
        this.container.style.setProperty('--btn-color', design.btn_color || '#1D3557');
    }

    /**
     * Назначение обработчиков событий
     */
    bindEvents() {
        // Кнопка действия (ссылка)
        const actionBtn = this.container.querySelector('#sp-action-btn');
        if (actionBtn && this.settings.has_button !== false) {
            actionBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.track('click');

                const url = this.settings.link;
                if (url && url !== '#') {
                    window.open(url, '_blank');
                }
            });
        }

        // Кнопка закрытия
        const closeBtn = this.container.querySelector('#sp-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.close();
            });
        }
    }

    /**
     * Закрытие виджета с анимацией
     */
    close() {
        const timestamp = new Date().getTime();
        const closeBehavior = this.settings.close_behavior || 'hide_forever';

        if (closeBehavior === 'hide_forever') {
            localStorage.setItem(this.storageKey, timestamp.toString());
        } else {
            sessionStorage.setItem(this.storageKey, timestamp.toString());
        }

        this.track('close');

        const position = this.settings.position || 'top';
        const fixedOnScroll = this.settings.fixed_on_scroll === true;
        const container = document.getElementById(`sm-widget-${this.id}`);

        if (!container) {
            if (this.container) this.container.remove();
            if (this.placeholder) this.placeholder.remove();
            return;
        }

        // Анимация ухода
        if (position === 'bottom') {
            container.style.transform = 'translateY(100%)';
            setTimeout(() => {
                container.remove();
                if (this.placeholder) this.placeholder.remove();
            }, 400);
        } else if (position === 'top' && fixedOnScroll) {
            container.style.transform = 'translateY(-100%)';
            setTimeout(() => {
                container.remove();
                if (this.placeholder) this.placeholder.remove();
                if (this.resizeObserver) this.resizeObserver.disconnect();
            }, 400);
        } else {
            container.remove();
            if (this.placeholder) this.placeholder.remove();
        }
    }

    /**
     * Автоматическое скрытие по таймеру
     */
    handleAutoHide() {
        const autoHide = this.settings.auto_hide || 0;
        if (autoHide > 0) {
            setTimeout(() => {
                const container = document.getElementById(`sm-widget-${this.id}`);
                if (container) {
                    this.close();
                }
            }, autoHide * 1000);
        }
    }

    /**
     * Переопределяем метод track для совместимости
     */
    track(eventName) {
        if (window.SmGet && window.SmGet.trackEvent) {
            window.SmGet.trackEvent(this.id, eventName);
        }
    }

    /**
     * Экранирование HTML
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

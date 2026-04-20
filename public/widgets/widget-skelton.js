/**
 * SmartWidgets Starter Skeleton v2.0
 * Используйте этот шаблон для создания новых виджетов.
 */
window.SmWidget_type_name = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        /** ДОСТУПНЫЕ МЕТОДЫ РОДИТЕЛЬСКОГО КЛАССА (SmWidget):
         * --------------------------------------------------------------------------
         * this._init()                 - Запускает проверку триггеров (scroll, exit, delay).
         * this.fire(delay)             - Принудительно вызывает метод mount() через время.
         * this.hexToRgb(hex)           - Возвращает {r,g,b} объект из HEX-строки.
         * this.shouldHideByFrequency() - Проверяет localStorage, не пора ли скрыть виджет.
         * this.saveClosed()            - Записывает факт закрытия виджета пользователем.
         * this.track(event)            - Отправляет событие (view, click, close) в БД.
         * escapeHtml(text)             - Вспомогательный метод для защиты от XSS.
         * --------------------------------------------------------------------------
         *
         * * ДОСТУПНЫЕ МЕТОДЫ ЭТОГО КЛАССА (SmWidget_type_name):
         *
         * bindEvents() - Установка всех обработчиков кликов (Делегирование).
         * injectStyles() - Внедрение стилей с использованием CSS-переменных.
         *                            Это позволяет использовать один файл assets.css для всех цветов.
         * mount() - Основной метод отрисовки.
         *
         */

        const design = settings.design || {};

        // 1. Централизованный конфиг
        this.config = {
            title: settings.title || 'Default Title',
            text: settings.text || '',
            colors: {
                bg: design.bg_color || '#ffffff',
                accent: design.accent_color || '#3b82f6',
                text: design.text_color || '#1f2937'
            }
        };

        this.state = { isShown: false };
    }

    /**
     * Инициализация условий (триггеры, ограничения)
     */
    init() {
        super._init();
    }

    /**
     * Основная логика отрисовки
     */
    mount() {
        // 2. Внедряем стили
        this.injectStyles();

        // 3. Готовим данные для шаблона
        const templateData = {
            id: this.id,
            title: this.escapeHtml(this.config.title),
            text: this.escapeHtml(this.config.text),
            accent: this.config.colors.accent
        };

        // 4. Рендерим HTML через системный парсер
        const html = this.processTemplate(this.assets.html, templateData);

        // 5. Создаем контейнер и добавляем в DOM
        this.container = this.createContainer(html, `sp-widget-root sp-${this.type}`);

        this.bindEvents();
        this.track('view');
    }

    /**
     * Подготовка стилей (Декларативный подход)
     */
    injectStyles() {
        const c = this.config.colors;

        // Формируем только строку. Ядро само проверит ID и сделает инъекцию.
        const css = `
            :root {
                --sp-bg: ${c.bg};
                --sp-accent: ${c.accent};
                --sp-text: ${c.text};
            }
            ${this.assets.css}
        `;

        this.injectCustomStyles(css);
    }

    /**
     * События (Делегирование)
     */
    bindEvents() {
        this.container.onclick = (e) => {
            const target = e.target;

            // Авто-закрытие для любых элементов с data-sp-close
            if (target.closest('[data-sp-close]')) {
                this.close();
            }

            // Пример обработки кнопки действия
            if (target.closest('.sp-main-btn')) {
                this.track('click_main');
                this.handleAction();
            }
        };
    }

    handleAction() {
        console.log('Action triggered!');
    }

    /**
     * Унифицированное закрытие
     */
    close() {
        this.saveClosed();
        this.track('close');

        if (this.container) {
            this.container.classList.remove('sp-active'); // Анимация ухода
            setTimeout(() => this.container.remove(), 400);
        }
    }
};

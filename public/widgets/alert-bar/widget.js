/**
 * Виджет "Alert Bar" - Оптимизированная версия
 */
window.SmWidget_alert_bar = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        // 1. Централизованный конфиг
        const design = settings.design || {};
        this.config = {
            text: settings.text || '',
            link: settings.link || '#',
            btnText: settings.btn_text || 'Подробнее',
            hasButton: settings.has_button !== false,
            position: settings.position || 'top',
            isFixed: settings.fixed_on_scroll === true || settings.position === 'bottom',
            // Цвета
            bg: design.bg_color || '#E63946',
            color: design.text_color || '#FFFFFF',
            btn: design.btn_color || '#1D3557'
        };

        this.placeholder = null;
        this.resizeObserver = null;
    }

    init() {
        // Базовый _init сам проверит behavior.frequency
        super._init();
    }

    mount() {
        this.injectStyles();

        // 2. Подготовка HTML (через быстрый маппинг)
        const replacements = {
            '{text}': this.escapeHtml(this.config.text),
            '{link}': this.config.link,
            '{btn_text}': this.escapeHtml(this.config.btnText),
            '{position}': this.config.position,
            '{widget_id}': this.id,
            '{display_button}': this.config.hasButton ? '' : 'hidden-btn'
        };

        let html = this.assets.html;
        for (const [key, val] of Object.entries(replacements)) {
            html = html.split(key).join(val);
        }

        // 3. Создание контейнера
        const wrapper = document.createElement('div');
        wrapper.id = `sm-widget-${this.id}`;

        // Динамический расчет CSS в зависимости от позиции
        const isBottom = this.config.position === 'bottom';
        wrapper.style.cssText = `
            position: ${this.config.isFixed ? 'fixed' : 'relative'};
            ${isBottom ? 'bottom: 0;' : 'top: 0;'};
            left: 0; right: 0; width: 100%; z-index: 999998;
            pointer-events: none;
            transition: transform 0.4s ease-in-out;
            transform: translateY(${this.config.isFixed ? (isBottom ? '100%' : '-100%') : '0'});
        `;

        wrapper.innerHTML = html;
        this.container = wrapper.firstElementChild;
        if (this.container) this.container.style.pointerEvents = 'auto';

        // 4. Вставка в DOM
        if (this.config.position === 'top' && !this.config.isFixed) {
            document.body.insertBefore(wrapper, document.body.firstChild);
        } else {
            document.body.appendChild(wrapper);
        }

        // Логика для фиксированной шапки (placeholder)
        if (this.config.position === 'top' && this.config.isFixed) {
            this.initPlaceholder(wrapper);
        }

        // Анимация появления
        requestAnimationFrame(() => {
            wrapper.style.transform = 'translateY(0)';
        });

        this.bindEvents();
        this.track('view');
    }

    injectStyles() {
        const styleId = `sp-style-${this.id}`;
        if (document.getElementById(styleId)) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            :root {
                --bg-color: ${this.config.bg};
                --text-color: ${this.config.color};
                --btn-color: ${this.config.btn};
            }
            .hidden-btn { display: none !important; }
            ${this.assets.css}
        `;
        document.head.appendChild(style);
    }

    initPlaceholder(wrapper) {
        this.placeholder = document.createElement('div');
        this.placeholder.style.display = 'none';
        document.body.insertBefore(this.placeholder, document.body.firstChild);

        const update = () => {
            if (!this.container) return;
            this.placeholder.style.cssText = `display:block; height:${this.container.offsetHeight}px; width:100%;`;
        };

        this.resizeObserver = new ResizeObserver(update);
        this.resizeObserver.observe(this.container);
        setTimeout(update, 100);
    }

    bindEvents() {
        this.container.onclick = (e) => {
            // Кнопка действия
            if (e.target.closest('#sp-action-btn')) {
                e.preventDefault();
                this.track('click');
                if (this.config.link !== '#') window.open(this.config.link, '_blank');
            }
            // Кнопка закрытия
            if (e.target.closest('#sp-close')) {
                e.preventDefault();
                this.close();
            }
        };
    }

    close() {
        const wrapper = this.container.parentElement;
        const isBottom = this.config.position === 'bottom';

        // Сохраняем состояние закрытия (базовый метод)
        this.saveClosed();
        this.track('close');

        // Анимация ухода
        if (this.config.isFixed) {
            wrapper.style.transform = `translateY(${isBottom ? '100%' : '-100%'})`;
            setTimeout(() => this.destroy(wrapper), 400);
        } else {
            this.destroy(wrapper);
        }
    }

    destroy(wrapper) {
        if (this.resizeObserver) this.resizeObserver.disconnect();
        if (this.placeholder) this.placeholder.remove();
        wrapper.remove();
        this.container = null;
    }

};

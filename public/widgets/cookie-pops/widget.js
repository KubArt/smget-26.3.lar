/**
 * Виджет "Cookie Pops" - Оптимизированная версия
 */
window.SmWidget_cookie_pops = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        // Централизованная конфигурация контента
        const content = settings.content || {};
        this.config = {
            text: content.text || '',
            policyText: content.policy_text || '',
            policyUrl: content.policy_url || '#',
            btnAccept: content.btn_accept_text || 'Принимаю',
            btnLeave: content.btn_leave_text || 'Покинуть сайт',
            showLeave: content.show_leave_btn !== false,
            // Дизайн
            bg: settings.design?.bg_color || '#ffffff',
            color: settings.design?.text_color || '#2d3436',
            btn: settings.design?.btn_color || '#0665d0'
        };
    }

    init() {
        super._init();
    }

    mount() {
        if (localStorage.getItem(`sm_widget_${this.id}_accepted`) === 'true') return;

        this.injectStyles();

        let html = this.assets.html;
        const replacements = {
            '{text}': this.config.text,
            '{policy_text}': this.config.policyText,
            '{policy_url}': this.config.policyUrl,
            '{btn_accept_text}': this.config.btnAccept,
            '{btn_leave_text}': this.config.btnLeave
        };

        for (const [key, value] of Object.entries(replacements)) {
            html = html.split(key).join(value);
        }

        // Используем метод базового класса (если он подходит) или создаем сами
        // Здесь создаем вручную, так как вам нужна специфическая логика pointer-events
        const container = document.createElement('div');
        container.id = `sm-widget-${this.id}`;
        container.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;width:100%;height:100%;z-index:999999;pointer-events:none;';
        container.innerHTML = html;

        document.body.appendChild(container);
        this.container = container;

        // Разрешаем клики только внутри самого уведомления
        const widgetRoot = container.querySelector('[class^="sp-skin-"]');
        if (widgetRoot) widgetRoot.style.pointerEvents = 'auto';

        this.bindEvents();
        this.track('view');
    }

    injectStyles() {
        const rgb = this.hexToRgb(this.config.btn);
        const textRgb = this.hexToRgb(this.config.color);

        const style = document.createElement('style');
        // Объединяем системные переменные и модифицированный CSS скина
        let css = this.assets.css
            .replace(/var\(--bg-color\)/g, 'var(--sm-bg-color)')
            .replace(/var\(--text-color\)/g, 'var(--sm-text-color)')
            .replace(/var\(--btn-color\)/g, 'var(--sm-btn-color)');

        style.textContent = `
            :root {
                --sm-bg-color: ${this.config.bg};
                --sm-text-color: ${this.config.color};
                --sm-btn-color: ${this.config.btn};
                --sm-btn-color-rgb: ${rgb.r},${rgb.g},${rgb.b};
                --sm-text-color-rgb: ${textRgb.r},${textRgb.g},${textRgb.b};
            }
            ${!this.config.showLeave ? '.hidden-btn { display: none !important; }' : ''}
            ${css}
        `;
        document.head.appendChild(style);
    }

    bindEvents() {
        this.container.onclick = (e) => {
            // Клик по кнопке "Принять" (используем matches для проверки множества классов)
            if (e.target.matches('.sp-glass-btn, [class*="-btn-accept"]')) {
                e.preventDefault();
                this.track('click');
                localStorage.setItem(`sm_widget_${this.id}_accepted`, 'true');
                this.container.remove();
            }

            // Клик по кнопке "Покинуть"
            if (e.target.id === 'sp-leave' || e.target.closest('#sp-leave')) {
                e.preventDefault();
                if (confirm('Вы уверены, что хотите покинуть сайт?')) {
                    window.location.href = 'about:blank';
                }
            }
        };
    }
};

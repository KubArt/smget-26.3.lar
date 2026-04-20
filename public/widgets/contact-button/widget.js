/**
 * Виджет "Мультикнопка связи" - Оптимизированная версия
 */
window.SmWidget_contact_button = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        const design = settings.design || {};
        const scaleMap = { small: '0.8', medium: '1', large: '1.2' };

        // 1. Централизованный конфиг
        this.config = {
            mainColor: design.main_color || '#3b82f6',
            iconColor: design.icon_color || '#ffffff',
            opacity: design.opacity || '1',
            scale: scaleMap[design.size] || '1',
            position: settings.position || 'right',
            tooltip: settings.main_tooltip || '',
            animation: settings.animation?.type || 'none',
            hover: design.hover_effect || 'none',
            size: design.size || 'medium',
            channels: (settings.channels || []).filter(c => c.is_active !== false && c.action_value)
        };

        // Библиотека иконок SVG
        this.icons = {
            whatsapp: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.032 2.001c-5.514 0-9.996 4.48-9.996 9.991 0 1.76.457 3.484 1.328 5.003L2 22.001l5.197-1.345c1.47.87 3.138 1.33 4.835 1.33 5.513 0 9.996-4.48 9.996-9.991 0-5.511-4.483-9.991-9.996-9.991zm0 18.406c-1.5 0-2.97-.404-4.252-1.169l-.305-.18-3.085.8.824-3.007-.198-.316c-.842-1.33-1.287-2.86-1.287-4.44 0-4.656 3.795-8.447 8.458-8.447 4.663 0 8.458 3.79 8.458 8.447 0 4.656-3.795 8.447-8.458 8.447z"/><path d="M16.94 14.07c-.262-.13-1.55-.764-1.79-.851-.24-.087-.414-.13-.59.13-.175.26-.68.851-.834 1.025-.154.175-.307.197-.57.066-.262-.13-1.106-.408-2.107-1.3-.78-.695-1.306-1.553-1.458-1.816-.153-.263-.016-.405.115-.536.118-.118.262-.306.393-.46.13-.153.175-.26.262-.434.087-.173.044-.325-.022-.456-.066-.13-.59-1.42-.808-1.945-.212-.51-.427-.44-.59-.45-.153-.01-.328-.01-.503-.01-.175 0-.46.066-.7.328-.24.262-.918.897-.918 2.19 0 1.292.942 2.54 1.074 2.717.13.175 1.854 2.83 4.49 3.97.627.27 1.117.432 1.5.553.63.2 1.204.172 1.657.104.505-.076 1.55-.633 1.768-1.245.218-.612.218-1.136.152-1.245-.065-.11-.24-.175-.502-.306z"/></svg>`,
            telegram: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.07-.06-.18-.04-.26-.02-.11.03-1.8 1.14-5.09 3.36-.48.33-.92.49-1.31.48-.43-.01-1.26-.24-1.88-.44-.75-.24-1.35-.37-1.3-.78.03-.21.32-.43.88-.66 2.22-.97 3.96-1.61 5.22-1.92 2.48-.61 3-.73 3.34-.73.07 0 .19.02.28.09.12.08.16.2.17.32.01.11-.04.27-.07.38z"/></svg>`,
            phone: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>`,
            email: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>`,
            vk: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21.5 7.3c.1-.3 0-.5-.4-.5h-2.7c-.4 0-.6.2-.7.5 0 0-.8 2-2 3.3-.4.4-.5.5-.7.5-.1 0-.2-.1-.2-.5V7.3c0-.4-.1-.5-.5-.5h-4.2c-.3 0-.4.2-.4.4 0 .8.6.9.6 1.5v2.3c0 .5-.1.6-.3.6-.5 0-1.7-1.8-2.4-3.9-.1-.4-.3-.6-.7-.6H4.9c-.5 0-.6.2-.6.5 0 .8.6 4.7 2.7 7.2 1.4 1.7 3.4 2.6 5.3 2.6 1.1 0 1.2-.3 1.2-.8v-2c0-.4.1-.5.4-.5.3 0 .8.2 1.6 1.1.9 1 1.3 1.5 1.9 1.5h2.7c.5 0 .7-.3.6-.7-.2-.5-1-1.4-2-2.4-.4-.5-1-1-1.2-1.3-.2-.3-.1-.5.1-.8 0 0 1.8-2.5 2-3.3z"/></svg>`,
            youtube: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2c-.3-1-1-1.8-2-2-1.8-.5-9.5-.5-9.5-.5s-7.7 0-9.5.5c-1 .2-1.7 1-2 2-.5 1.9-.5 5.8-.5 5.8s0 3.9.5 5.8c.3 1 1 1.8 2 2 1.8.5 9.5.5 9.5.5s7.7 0 9.5-.5c1-.2 1.7-1 2-2 .5-1.9.5-5.8.5-5.8s0-3.9-.5-5.8zM9.5 14.5V9.5l6.5 2.5-6.5 2.5z"/></svg>`,
            custom: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1 0 1.71-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>`
        };

        this.activeClass = 'sp-active';
    }

    init() {
        super._init();
    }

    mount() {
        this.injectStyles();

        // 2. Генерация каналов через лаконичный map
        const channelsHtml = this.config.channels
            .map(c => this.renderChannel(c))
            .join('');

        // 3. Подготовка и вставка HTML
        let html = this.assets.html
            .split('{channels_html}').join(channelsHtml)
            .split('{position}').join(this.config.position)
            .split('{main_tooltip}').join(this.escapeHtml(this.config.tooltip))
            .split('{widget_id}').join(this.id);

        this.createContainer(html);

        if (this.container) {
            this.applyDesign();
            this.bindEvents();
        }
    }

    renderChannel(c) {
        const url = this.getChannelUrl(c);
        const icon = this.getIcon(c.type);
        const label = this.escapeHtml(c.label || c.type);

        return `
            <a href="${url}" class="sp-channel-item" target="_blank" rel="noopener"
               onclick="if(window.SmGet) window.SmGet.trackEvent(${this.id}, 'click_${c.type}')">
                <div class="sp-channel-icon" style="background-color: ${c.bg_color || '#555'}; color: ${c.icon_color || '#fff'}">
                    ${icon}
                </div>
                <span class="sp-channel-label">${label}</span>
            </a>`;
    }

    getChannelUrl(c) {
        const val = c.action_value.trim();
        const clean = val.replace(/\D/g, '');

        const protocols = {
            whatsapp: `https://wa.me/${clean}`,
            telegram: `https://t.me/${val.replace('@', '')}`,
            phone: `tel:${clean}`,
            email: `mailto:${val}`,
            vk: `https://vk.com/${val}`,
            youtube: `https://youtube.com/${val}`
        };
        return protocols[c.type] || val;
    }

    applyDesign() {
        const s = this.container.style;
        s.setProperty('--main-color', this.config.mainColor);
        s.setProperty('--icon-color', this.config.iconColor);
        s.setProperty('--scale-factor', this.config.scale);
        s.setProperty('--btn-opacity', this.config.opacity);

        // Добавляем классы одним массивом
        this.container.classList.add(
            `sp-animation-${this.config.animation}`,
            `sp-size-${this.config.size}`,
            `sp-hover-${this.config.hover}`
        );
    }

    injectStyles() {
        const styleId = `sp-style-${this.id}`;
        if (document.getElementById(styleId)) return;

        const rgb = this.hexToRgb(this.config.mainColor);
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `:root { --main-color-rgb: ${rgb.r},${rgb.g},${rgb.b}; }\n${this.assets.css}`;
        document.head.appendChild(style);
    }

    bindEvents() {
        // Используем один слушатель на весь контейнер (делегирование)
        this.container.addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-sp-toggle]');
            const close = e.target.closest('[data-sp-close], [data-sp-overlay]');

            if (toggle) {
                e.preventDefault();
                const isOpen = this.container.classList.toggle(this.activeClass);
                this.track(isOpen ? 'open_menu' : 'close_menu');
            } else if (close) {
                e.preventDefault();
                this.container.classList.remove(this.activeClass);
            }
        });

        // Закрытие при клике вне
        document.addEventListener('click', (e) => {
            if (this.container && !this.container.contains(e.target)) {
                this.container.classList.remove(this.activeClass);
            }
        });
    }

    getIcon(type) {
        // Библиотеку иконок можно оставить в конструкторе или вынести,
        // здесь она вызывается через простую проверку
        return this.icons[type] || this.icons.custom;
    }

};

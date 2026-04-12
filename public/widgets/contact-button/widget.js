window.initWidget_contact_button = function(settings, widgetId, assets) {
    // Используем задержку из настроек
    const delay = (settings.delay || 0) * 1000;

    setTimeout(() => {
        // 1. Инжекция стилей (проверяем, чтобы не дублировать)
        const styleId = `sp-style-${widgetId}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = assets.css;
            document.head.appendChild(style);
        }

        // 2. Генерация HTML каналов
        const channelsHtml = (settings.channels || [])
            .filter(c => c.is_active && (c.action_value || c.type === 'custom'))
            .map(c => {
                let url = '#';
                if (c.action_type === 'link' && c.action_value) {
                    const val = c.action_value.replace('@', '').trim();
                    if (c.type === 'whatsapp') url = `https://wa.me/${val.replace(/\D/g, '')}`;
                    else if (c.type === 'telegram') url = `https://t.me/${val}`;
                    else if (c.type === 'phone') url = `tel:${val}`;
                    else url = val;
                }

                const iconMap = {
                    whatsapp: 'fab fa-whatsapp',
                    telegram: 'fab fa-telegram-plane',
                    phone: 'fa fa-phone',
                    vk: 'fab fa-vk',
                    custom: 'fa fa-envelope'
                };

                return `
                    <a href="${url}" target="_blank" class="sp-channel-item" onclick="if(window.SmGet) window.SmGet.trackEvent(${widgetId}, 'click_${c.type}')">
                        <span class="sp-channel-label">${c.label}</span>
                        <div class="sp-channel-icon" style="background-color: ${c.bg_color || '#555'}; color: ${c.icon_color || '#fff'}">
                            <i class="${iconMap[c.type] || 'fa fa-link'}"></i>
                        </div>
                    </a>`;
            }).join('');

        // 3. Создание контейнера с уникальным ID
        const containerId = `sm-widget-container-${widgetId}`;
        if (document.getElementById(containerId)) return;

        const pulseClass = settings.pulse ? `sp-anim-${settings.animation.type}` : '';

        let html = assets.html
            .replace('{channels_html}', channelsHtml)
            .replace('{position}', settings.position)
            .replace('{pulse_class}', pulseClass);

        const div = document.createElement('div');
        div.id = containerId;
        div.innerHTML = html;
        const widgetElement = div.firstElementChild;
        document.body.appendChild(widgetElement);

        // 4. Применяем CSS переменные (дизайн)
        const scaleMap = { small: '0.8', medium: '1', large: '1.2' };
        widgetElement.style.setProperty('--main-color', settings.design.main_color);
        widgetElement.style.setProperty('--icon-color', settings.design.icon_color);
        widgetElement.style.setProperty('--scale-factor', scaleMap[settings.design.size] || '1');

        // 5. ЛОГИКА КЛИКА (Исправлено)
        const mainBtn = widgetElement.querySelector('#sp-main-btn');
        if (mainBtn) {
            mainBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                // Переключаем класс именно на корневом элементе виджета
                widgetElement.classList.toggle('is-active');

                if (window.SmGet && window.SmGet.trackEvent) {
                    window.SmGet.trackEvent(widgetId, 'click_main');
                }
            });
        }

        // Закрытие при клике вне виджета
        document.addEventListener('click', (e) => {
            if (!widgetElement.contains(e.target)) {
                widgetElement.classList.remove('is-active');
            }
        });

        if (window.SmGet && window.SmGet.trackEvent) {
            window.SmGet.trackEvent(widgetId, 'view');
        }

    }, delay);
};

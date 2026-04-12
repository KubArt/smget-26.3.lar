window.initWidget_cookie_pops = function(settings, widgetId, assets) {
    const delay = (settings.delay || 0) * 1000;

    setTimeout(() => {
        // 1. Стили (без изменений, подгружаем CSS скина)
        if (assets.css) {
            const style = document.createElement('style');
            style.textContent = assets.css;

            // Динамические переменные из настроек
            if (settings.design) {
                const d = settings.design;
                style.textContent += `
                    :root {
                        --bg-color: ${d.bg_color || '#ffffff'};
                        --text-color: ${d.text_color || '#2d3436'};
                        --btn-color: ${d.btn_color || '#0665d0'};
                    }
                `;
            }
            document.head.appendChild(style);
        }

        // 2. Подготовка HTML (замена плейсхолдеров)
        let html = assets.html;
        const placeholders = {
            '{text}': settings.content.text || '',
            '{policy_text}': settings.content.policy_text || '',
            '{policy_url}': settings.content.policy_url || '#',
            '{btn_accept_text}': settings.content.btn_accept_text || 'Принимаю',
            '{btn_leave_text}': settings.content.btn_leave_text || 'Покинуть сайт'
        };

        Object.keys(placeholders).forEach(key => {
            html = html.replaceAll(key, placeholders[key]);
        });

        // 3. Рендер с фиксированным позиционированием
        const container = document.createElement('div');
        container.id = `sm-widget-${widgetId}`;

        // Устанавливаем базовые стили через JS, чтобы они были железными
        Object.assign(container.style, {
            position: 'fixed',
            zIndex: '999999',
            left: '0',
            top: '0',
            width: '100%',
            height: '100%',
            pointerEvents: 'none' // Пропускаем клики сквозь контейнер, если он на весь экран
        });

        container.innerHTML = html;
        document.body.appendChild(container);

        // ВАЖНО: Внутри шаблонов скинов элементы должны иметь pointer-events: auto,
        // чтобы кнопки нажимались, несмотря на прозрачность родителя.
        const widgetRoot = container.firstElementChild;
        if (widgetRoot) {
            widgetRoot.style.pointerEvents = 'auto';
        }

        // 4. Обработка событий (оставляем как было)
        const acceptBtn = container.querySelector('.sp-bubble-btn-accept, .sp-compact-btn-ok, .sp-modal-btn-accept, .sp-slim-btn');
        if (acceptBtn) {
            acceptBtn.onclick = () => {
                window.SmGet.trackEvent(widgetId, 'click');
                container.remove();
                localStorage.setItem(`sm_widget_${widgetId}_accepted`, 'true');
            };
        }

        const leaveBtn = container.querySelector('#sp-leave');
        if (leaveBtn) {
            leaveBtn.onclick = () => container.remove();
        }

        window.SmGet.trackEvent(widgetId, 'view');

    }, delay);
};

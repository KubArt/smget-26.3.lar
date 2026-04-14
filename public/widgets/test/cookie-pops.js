window.initWidget_cookie_pops = function(settings, widgetId, assets) {
    const delay = (settings.delay || 0) * 1000;

    setTimeout(() => {
        // Проверяем, не был ли уже принят виджет
        if (localStorage.getItem(`sm_widget_${widgetId}_accepted`) === 'true') {
            return;
        }

        // Применяем CSS переменные
        if (settings.design) {
            const style = document.createElement('style');
            // Добавляем RGB переменные для теней
            const btnColor = settings.design.btn_color || '#0665d0';
            const rgb = hexToRgb(btnColor);
            const textColor = settings.design.text_color || '#2d3436';
            const textRgb = hexToRgb(textColor);

            style.textContent = `
                :root {
                    --sm-bg-color: ${settings.design.bg_color || '#ffffff'};
                    --sm-text-color: ${textColor};
                    --sm-btn-color: ${btnColor};
                    --sm-btn-color-rgb: ${rgb.r}, ${rgb.g}, ${rgb.b};
                    --sm-text-color-rgb: ${textRgb.r}, ${textRgb.g}, ${textRgb.b};
                }
            `;
            document.head.appendChild(style);
        }

        // Подключаем CSS скина
        if (assets.css) {
            const style = document.createElement('style');
            // Модифицируем CSS для работы через переменные
            let modifiedCSS = assets.css;
            modifiedCSS = modifiedCSS.replace(/var\(--bg-color\)/g, 'var(--sm-bg-color)');
            modifiedCSS = modifiedCSS.replace(/var\(--text-color\)/g, 'var(--sm-text-color)');
            modifiedCSS = modifiedCSS.replace(/var\(--btn-color\)/g, 'var(--sm-btn-color)');
            style.textContent = modifiedCSS;
            document.head.appendChild(style);
        }

        // Подставляем значения в HTML
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

        // Создаем контейнер с правильным позиционированием
        const container = document.createElement('div');
        container.id = `sm-widget-${widgetId}`;

        // Контейнер не должен влиять на поток документа
        container.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            z-index: 999999;
            pointer-events: none;
            display: block;
            visibility: visible;
        `;

        container.innerHTML = html;
        document.body.appendChild(container);

        // Находим корневой элемент виджета (любой элемент с классом sp-skin-*)
        const widgetRoot = container.querySelector('[class^="sp-skin-"]');
        if (widgetRoot) {
            widgetRoot.style.pointerEvents = 'auto';
        }

        // Обработчик для кнопки "Принять"
        const acceptBtn = container.querySelector('.sp-glass-btn, .sp-modern-btn-accept, .sp-popup-btn-accept, .sp-bubble-btn-accept, .sp-pill-btn-accept, .sp-overlay-btn-accept, .sp-modal-minimal-btn-accept, .sp-slim-btn-accept, .sp-side-btn-accept, .sp-top-bar-btn-accept, .sp-floating-btn-accept');
        if (acceptBtn) {
            acceptBtn.onclick = (e) => {
                e.preventDefault();
                // Отправляем событие клика на сервер
                if (window.SmGet && window.SmGet.trackEvent) {
                    window.SmGet.trackEvent(widgetId, 'click');
                }
                // Удаляем виджет
                container.remove();
                // Сохраняем в localStorage, что пользователь принял
                localStorage.setItem(`sm_widget_${widgetId}_accepted`, 'true');
            };
        }

        // Обработчик для кнопки "Покинуть сайт"
        const leaveBtn = container.querySelector('#sp-leave');
        if (leaveBtn) {
            if (settings.content.show_leave_btn !== false) {
                leaveBtn.onclick = (e) => {
                    e.preventDefault();
                    // Показываем confirm
                    const confirmLeave = confirm('Вы уверены, что хотите покинуть сайт?');
                    if (confirmLeave) {
                        // Закрываем текущую вкладку/окно
                        window.close();
                        // Если window.close() не сработал (из-за ограничений браузера)
                        // Перенаправляем на about:blank или пустую страницу
                        if (!window.closed) {
                            window.location.href = 'about:blank';
                        }
                    }
                };
            } else {
                // Скрываем кнопку, если она отключена в настройках
                leaveBtn.classList.add('hidden-btn');
            }
        }

        // Отслеживаем показ виджета
        if (window.SmGet && window.SmGet.trackEvent) {
            window.SmGet.trackEvent(widgetId, 'view');
        }

    }, delay);
};

// Вспомогательная функция для конвертации HEX в RGB
function hexToRgb(hex) {
    // Удаляем # если есть
    hex = hex.replace(/^#/, '');

    // Поддерживаем 3-символьные HEX
    if (hex.length === 3) {
        hex = hex.split('').map(c => c + c).join('');
    }

    const intVal = parseInt(hex, 16);
    return {
        r: (intVal >> 16) & 255,
        g: (intVal >> 8) & 255,
        b: intVal & 255
    };
}

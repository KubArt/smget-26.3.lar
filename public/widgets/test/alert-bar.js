window.initWidget_alert_bar = function(settings, widgetId, assets) {
    // 1. Проверка частоты показа и состояния закрытия
    const storageKey = `sm_alert_${widgetId}_closed`;
    const now = new Date().getTime();

    // Проверяем, не закрыл ли пользователь виджет ранее
    const closedData = localStorage.getItem(storageKey) || sessionStorage.getItem(storageKey);
    if (closedData) {
        if (settings.close_behavior === 'hide_forever') return;
        if (settings.frequency === 'once_day') {
            const lastClosed = parseInt(closedData);
            if (now - lastClosed < 24 * 60 * 60 * 1000) return;
        }
        if (settings.frequency === 'once_session' && sessionStorage.getItem(storageKey)) return;
    }

    let isWidgetRendered = false;
    let placeholder = null; // Для placeholder при фиксации

    // Функция отрисовки виджета
    const renderAlert = () => {
        if (isWidgetRendered) return;
        isWidgetRendered = true;

        // 2. Инъекция стилей
        if (assets.css) {
            const style = document.createElement('style');
            style.id = `sm-style-${widgetId}`;
            let css = assets.css;

            if (settings.design) {
                css += `
                    :root {
                        --bg-color: ${settings.design.bg_color || '#E63946'};
                        --text-color: ${settings.design.text_color || '#FFFFFF'};
                        --btn-color: ${settings.design.btn_color || '#1D3557'};
                    }
                `;
            }
            style.textContent = css;
            document.head.appendChild(style);
        }

        // 3. Подготовка HTML
        let html = assets.html;
        const placeholders = {
            '{text}': settings.text || '',
            '{link}': settings.link || '#',
            '{btn_text}': settings.btn_text || 'Подробнее',
            '{position}': settings.position || 'top'
        };

        Object.keys(placeholders).forEach(key => {
            html = html.replaceAll(key, placeholders[key]);
        });

        // 4. Создание контейнера
        const container = document.createElement('div');
        container.id = `sm-widget-${widgetId}`;
        container.innerHTML = html;

        const widgetRoot = container.querySelector('.sp-alert-bar');

        if (settings.position === 'bottom') {
            // Нижнее положение - всегда fixed
            container.style.cssText = `
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 999998;
                transform: translateY(100%);
                transition: transform 0.4s ease-in-out;
            `;
            document.body.appendChild(container);
            setTimeout(() => { container.style.transform = 'translateY(0)'; }, 100);

        } else {
            // Верхнее положение
            if (settings.fixed_on_scroll) {
                // Фиксированный режим: создаем placeholder
                placeholder = document.createElement('div');
                placeholder.style.display = 'none';
                document.body.insertBefore(placeholder, document.body.firstChild);

                // Вставляем виджет как fixed
                container.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    z-index: 999998;
                    transform: translateY(-100%);
                    transition: transform 0.4s ease-in-out;
                `;
                document.body.appendChild(container);
                setTimeout(() => { container.style.transform = 'translateY(0)'; }, 100);

                // Функция обновления placeholder
                const updatePlaceholder = () => {
                    if (!widgetRoot) return;
                    const height = widgetRoot.offsetHeight;
                    if (height > 0) {
                        placeholder.style.display = 'block';
                        placeholder.style.height = height + 'px';
                        placeholder.style.width = '100%';
                    }
                };

                // Обновляем при появлении и изменении размера
                setTimeout(updatePlaceholder, 150);
                const resizeObserver = new ResizeObserver(updatePlaceholder);
                resizeObserver.observe(widgetRoot);

            } else {
                // Обычный режим - в потоке документа
                container.style.cssText = `
                    position: relative;
                    width: 100%;
                    z-index: 999998;
                `;
                document.body.insertBefore(container, document.body.firstChild);
            }
        }

        // Активируем клики внутри
        if (widgetRoot) widgetRoot.style.pointerEvents = 'auto';

        // 5. Обработка событий
        if (window.SmGet && window.SmGet.trackEvent) {
            window.SmGet.trackEvent(widgetId, 'view');
        }

        // Клик по кнопке действия
        const actionBtn = container.querySelector('#sp-action-btn');
        if (actionBtn) {
            actionBtn.onclick = (e) => {
                e.preventDefault();
                if (window.SmGet && window.SmGet.trackEvent) {
                    window.SmGet.trackEvent(widgetId, 'click');
                }
                if (settings.link && settings.link !== '#') {
                    window.open(settings.link, '_blank');
                }
            };
        }

        // Закрытие
        const closeBtn = container.querySelector('#sp-close');
        if (closeBtn) {
            closeBtn.onclick = () => {
                const timestamp = new Date().getTime();
                if (settings.close_behavior === 'hide_forever') {
                    localStorage.setItem(storageKey, timestamp.toString());
                } else {
                    sessionStorage.setItem(storageKey, timestamp.toString());
                }

                // Анимация ухода
                if (settings.position === 'bottom') {
                    container.style.transform = 'translateY(100%)';
                    setTimeout(() => container.remove(), 400);
                } else if (settings.position === 'top' && settings.fixed_on_scroll) {
                    container.style.transform = 'translateY(-100%)';
                    setTimeout(() => {
                        container.remove();
                        if (placeholder) placeholder.remove();
                    }, 400);
                } else {
                    container.remove();
                }
            };
        }

        // 6. Авто-скрытие по таймеру
        if (settings.auto_hide > 0) {
            setTimeout(() => {
                if (document.getElementById(container.id)) {
                    if (settings.position === 'bottom') {
                        container.style.transform = 'translateY(100%)';
                        setTimeout(() => container.remove(), 400);
                    } else if (settings.position === 'top' && settings.fixed_on_scroll) {
                        container.style.transform = 'translateY(-100%)';
                        setTimeout(() => {
                            container.remove();
                            if (placeholder) placeholder.remove();
                        }, 400);
                    } else {
                        container.remove();
                    }
                }
            }, settings.auto_hide * 1000);
        }
    };

    // 7. Логика запуска (Delay или Scroll)
    const startLogic = () => {
        if (settings.scroll_trigger > 0) {
            let triggered = false;

            const checkScroll = () => {
                if (triggered) return;

                const winHeight = window.innerHeight;
                const docHeight = document.documentElement.scrollHeight - winHeight;
                // Защита от деления на 0
                if (docHeight <= 0) {
                    renderAlert();
                    triggered = true;
                    return;
                }

                const scrollPercent = (window.scrollY / docHeight) * 100;

                if (scrollPercent >= settings.scroll_trigger) {
                    triggered = true;
                    renderAlert();
                    window.removeEventListener('scroll', checkScroll);
                    window.removeEventListener('resize', checkScroll);
                }
            };

            window.addEventListener('scroll', checkScroll);
            window.addEventListener('resize', checkScroll);
            // Проверяем сразу при загрузке
            checkScroll();
        } else {
            renderAlert();
        }
    };

    // Запуск с учетом задержки
    if (settings.delay > 0) {
        setTimeout(startLogic, settings.delay * 1000);
    } else {
        startLogic();
    }
};

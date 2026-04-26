class SmWidget {
    constructor(settings, id, assets, behavior) {
        this.id = id;
        this.settings = settings;
        this.assets = assets;
        this.behavior = behavior || {}; // отдельно от settings
        this.container = null;
        this.isShown = false;
        this.isTriggerFired = false;
    }

    // Инициализация с учетом новых настроек поведения
    _init() {
        // Проверяем частоту показа
        if (this.shouldHideByFrequency()) {
            return;
        }

        // Проверяем закрытие пользователем
        if (this.shouldHideByClose()) {
            return;
        }

        // Получаем настройки из behavior или старых полей (обратная совместимость)
        const triggerType = this.behavior.trigger_type || this.getLegacyTriggerType();
        const delay = (this.behavior.delay ?? this.settings.delay ?? 0) * 1000;
        const scrollPercent = this.behavior.scroll_percent ?? this.settings.scroll_trigger ?? 0;
        const config = this.behavior || {};

        switch (triggerType) {
            case 'immediate':
                this.fire(0);
                break;

            case 'delay':
                this.fire(delay);
                break;

            case 'scroll':
                this.initScrollTrigger(scrollPercent);
                break;

            case 'exit':
                this.initExitTrigger();
                break;

            case 'click':
                this.initClickTrigger(this.behavior.click_selector);
                break;

            default:
                this.fire(0);
        }
    }
    // Универсальный метод активации виджета
    fire(delay) {
        if (this.isTriggerFired) return;
        this.isTriggerFired = true;

        setTimeout(() => {
            this.mount();
        }, delay);
    }


// 2. Уход со страницы (Exit Intent)
    initExitTrigger(delay) {
        const onMouseOut = (e) => {
            // e.relatedTarget === null означает, что мышь покинула окно браузера совсем
            // e.clientY <= 0 гарантирует, что уход был именно вверх
            const from = e.relatedTarget || e.toElement;
            if (!from || from.nodeName === "HTML") {
                if (e.clientY <= 5) { // 5px запас для надежности
                    document.removeEventListener('mouseout', onMouseOut);
                    this.fire(delay);
                }
            }
        };

        // Используем mouseout на document, он чувствительнее к границам
        document.addEventListener('mouseout', onMouseOut);
    }

    // 3. Клик по селектору (Click Trigger)
    initClickTrigger(selector) {
        alert(`[SMGET] Click trigger activated for selector: ${selector}`);
        if (!selector) return;

        const handleExternalClick = (e) => {
            // Ищем ближайший элемент, подходящий под селектор
            const target = e.target.closest(selector);
            if (target) {
                console.log(`[SMGET] Target clicked! Selector: ${selector}`);
                this.fire(0);
            }
        };
        // Использование true (фаза захвата) критично,
        // чтобы обойти e.stopPropagation() на сайте клиента
        document.addEventListener('click', handleExternalClick, true);
    }

    // Проверка частоты показа
    shouldHideByFrequency() {
        const frequency = this.behavior.frequency || 'always';

        if (frequency === 'always') return false;

        if (frequency === 'once_session') {
            return sessionStorage.getItem(`sm_widget_${this.id}_shown`) === '1';
        }

        const storageKey = `sm_widget_${this.id}_shown`;
        const lastShown = localStorage.getItem(storageKey);
        if (!lastShown) return false;

        const lastShownTime = parseInt(lastShown);
        const now = Date.now();
        const dayMs = 86400000;
        const weekMs = 604800000;
        const monthMs = 2592000000;

        switch (frequency) {
            case 'once_day': return (now - lastShownTime) < dayMs;
            case 'once_week': return (now - lastShownTime) < weekMs;
            case 'once_month': return (now - lastShownTime) < monthMs;
            case 'once_forever': return true;
            case 'custom_days':
                const days = this.behavior.custom_days || 7;
                return (now - lastShownTime) < (days * dayMs);
            default: return false;
        }
    }

    // Сохраняем факт показа
    saveShown() {
        const frequency = this.behavior.frequency || 'always';

        if (frequency === 'once_session') {
            sessionStorage.setItem(`sm_widget_${this.id}_shown`, '1');
        } else if (frequency !== 'always') {
            localStorage.setItem(`sm_widget_${this.id}_shown`, Date.now().toString());
        }

        this.isShown = true;
    }

    // Проверка закрытия
    shouldHideByClose() {
        const closeKey = `sm_widget_${this.id}_closed`;
        return sessionStorage.getItem(closeKey) === '1';
    }

    // Сохраняем закрытие
    saveClosed() {
        sessionStorage.setItem(`sm_widget_${this.id}_closed`, '1');
    }

    // Авто-закрытие виджета
    initAutoClose() {
        const autoClose = this.behavior.auto_close ?? this.settings.auto_hide ?? 0;
        if (autoClose > 0 && this.container) {
            setTimeout(() => this.close(), autoClose * 1000);
        }
    }

    /** * Автоматически собирает конфиг, применяет стили и создает контейнер.
     * Упрощает mount() в дочерних классах до 2-3 строк.
     */
    superRender(templateData = {}) {
        const html = this.processTemplate(this.assets.html, {
            id: this.id,
            ...this.config,
            ...templateData
        });
        return this.createContainer(html, `sm-widget-root sp-${this.type}`);
    }

    injectCustomStyles(css) {
        const styleId = `sm-style-${this.id}`;
        if (document.getElementById(styleId) || !css) return;

        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = css;
        document.head.appendChild(style);
    }

    /**
     * Заменяет плейсхолдеры {key} в строке HTML данными из объекта data.
     * Позволяет избежать цепочек .split().join() в дочерних классах.
     */
    processTemplate(html, data) {
        return html.replace(/{(\w+)}/g, (match, key) => {
            return data[key] !== undefined ? data[key] : match;
        });
    }

    // Трекинг событий
    track(eventName) {
        if (window.SmGet && window.SmGet.trackEvent) {
            window.SmGet.trackEvent(this.id, eventName);
        }
    }

    // Хелпер для создания контейнера
    createContainer(html, className = '') {
        const div = document.createElement('div');
        div.id = `sm-widget-${this.id}`;
        if (className) div.className = className;

        div.innerHTML = html.trim();
        this.container = div.firstElementChild;

        document.body.appendChild(this.container);
        this.track('view');
        this.saveShown();
        this.initAutoClose();

        return this.container;
    }

    initScrollTrigger(percentage) {
        const getScrollTarget = () => {
            const overflowElement = document.querySelector('.browser-viewport, #app-shell, main');
            return overflowElement || window;
        };

        const target = getScrollTarget();

        const onScroll = () => {
            let scrolled = 0;

            if (target === window) {
                const h = document.documentElement;
                const b = document.body;

                // Получаем максимально точную высоту контента
                const scrollHeight = Math.max(h.scrollHeight, b.scrollHeight);
                const clientHeight = h.clientHeight;

                // Если скроллить некуда (высота контента меньше или равна окну)
                if (scrollHeight <= clientHeight) {
                    scrolled = 0;
                } else {
                    const scrollTop = window.pageYOffset || h.scrollTop || b.scrollTop;
                    scrolled = (scrollTop / (scrollHeight - clientHeight)) * 100;
                }
            } else {
                // Для внутренних контейнеров
                const diff = target.scrollHeight - target.clientHeight;
                scrolled = diff > 0 ? (target.scrollTop / diff) * 100 : 0;
            }

            // Если все же получили NaN или Infinity (защита)
            if (!isFinite(scrolled)) scrolled = 0;

            // Для отладки
            // console.log(`Scroll: ${scrolled.toFixed(2)}% / Target: ${percentage}%`);

            if (scrolled >= percentage) {
                target.removeEventListener('scroll', onScroll);
                this.mount();
                    //setTimeout(() => this.mount(), delay);
            }
        };

        // Слушаем скролл
        target.addEventListener('scroll', onScroll, { passive: true });

        // Важно: если на сайте бесконечный скролл или динамический контент,
        // проверяем прогресс каждые 2 секунды на случай изменения высоты страницы
        const interval = setInterval(() => {
            if (document.readyState === 'complete') onScroll();
        }, 2000);

        // Удаляем интервал, если mount уже вызвался
        const originalMount = this.mount;
        this.mount = (...args) => {
            clearInterval(interval);
            return originalMount.apply(this, args);
        };

        onScroll();
    }

    // Метод для закрытия (можно переопределить)
    close() {
        if (this.container) {
            this.saveClosed();
            this.container.remove();
            this.container = null;
        }
    }

    // Метод для переопределения в наследниках
    mount() {
        console.warn('Mount method not implemented');
    }

    /*** Общие методы */

    /**
     * Создает обертку виджета с заданными классами и стилями.
     */
    createWrapper(id, classes = '', styles = '') {
        const el = document.createElement('div');
        el.id = `sm-widget-${id}`;
        el.className = classes;
        if (styles) el.style.cssText = styles;
        return el;
    }

    hexToRgb(hex) {
        hex = hex.replace(/^#/, '');
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
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
    /**
     * Сохраняет состояние закрытия или действия в зависимости от частоты показа.
     */
    setStoredRecord(key, value = Date.now(), isSession = false) {
        const storage = isSession ? sessionStorage : localStorage;
        storage.setItem(`${this.type}_${this.id}_${key}`, value);
    }
    /**
     * Собирает данные из любой формы в объект.
     */
    getFormData(formElement) {
        return Object.fromEntries(new FormData(formElement));
    }
}

// Запуск виджетов - ИСПРАВЛЕНА РЕКУРСИЯ
(function() {
    const payload = window.SmGet;
    if (!payload) return;
    window.SmGet.trackEvent = function(id, event){
        const url = 'http://smget-26.3.lar/api/v1/track';
        const data = JSON.stringify({
            widget_id: id,
            event: event,
            url: window.location.pathname
        });

        const blob = new Blob([data], { type: 'application/json' });
        navigator.sendBeacon(url, blob);
    };
    /*
    function sendJsGoal(targetName) {
        // Пытаемся найти функцию ym (Яндекс)
        if (typeof ym !== 'undefined') {
            // Нам нужно знать ID счетчика. Его можно либо выцепить из глобальных объектов,
            // либо передать из PHP при загрузке виджета (что правильнее).
            const counterId = window.SmGet.metrics?.yandex_id;
            if (counterId) {
                ym(counterId, 'reachGoal', targetName);
            }
        }
    }
    //*/
    // Функция загрузки скрипта виджета - ИСПРАВЛЕНО
    function loadWidgetScript(widget) {
        return new Promise((resolve, reject) => {
            try {
                // Проверяем, не загружен ли уже класс виджета
                const className = 'SmWidget_' + widget.type.replace(/-/g, '_');
                if (typeof window[className] === 'function') {
                    resolve();
                    return;
                }
                // Выполняем JS код виджета
                const scriptFunction = new Function(widget.assets.js);
                scriptFunction();

                // Даем время на регистрацию класса
                setTimeout(() => {
                    if (typeof window[className] === 'function') {
                        resolve();
                    } else {
                        reject(new Error(`Class ${className} not registered after script execution`));
                    }
                }, 10);
            } catch (e) {
                reject(e);
            }
        });
    }

    // Загружаем все виджеты последовательно, а не параллельно
    async function loadAllWidgets(widgets) {
        for (const widget of widgets) {
            try {
                await loadWidgetScript(widget);
            } catch (e) {
                console.error(`Failed to load widget ${widget.type}:`, e);
            }
        }
    }

    // Запускаем загрузку
    loadAllWidgets(payload.widgets)
        .then(() => {
            payload.widgets.forEach(widget => {
                const className = 'SmWidget_' + widget.type.replace(/-/g, '_');
                if (typeof window[className] === 'function') {
                    const instance = new window[className](widget.settings, widget.id, widget.assets, widget.behavior);
                    // Запускаем единую логику проверки условий
                    instance.init();
                }
            });
        })
        .catch(error => {
            console.error('SMGET: Failed to load widget scripts:', error);
        });
})();

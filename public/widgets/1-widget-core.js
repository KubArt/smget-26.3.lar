class SmWidget {
    constructor(settings, id, assets) {
        this.id = id;
        this.settings = settings;
        this.assets = assets;
        this.container = null;
    }

    // Инициализация с учетом задержки и триггеров
    init() {
        const delay = (this.settings.delay || 0) * 1000;

        if (this.settings.scroll_trigger > 0) {
            this.initScrollTrigger(this.settings.scroll_trigger, delay);
        } else {
            setTimeout(() => this.mount(), delay);
        }
    }

    // Унифицированная инжекция стилей
    injectStyles() {
        const styleId = `sm-style-${this.id}`;
        if (!document.getElementById(styleId) && this.assets.css) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = this.assets.css;
            document.head.appendChild(style);
        }
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

        div.innerHTML = html.trim(); // .trim() важен, чтобы убрать пробелы до тега
        this.container = div.firstElementChild; // Теперь тут точно будет DOM-элемент

        document.body.appendChild(this.container);
        this.track('view');
        return this.container;
    }

    initScrollTrigger(percent, delay) {
        const check = () => {
            const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent >= percent) {
                setTimeout(() => this.mount(), delay);
                window.removeEventListener('scroll', check);
            }
        };
        window.addEventListener('scroll', check);
        check();
    }

    // Метод для переопределения в наследниках
    mount() {
        console.warn('Mount method not implemented');
    }
}
// Запуск виджетов
(function() {
    const payload = window.SmGet;
    if (!payload) return;

    window.SmGet.trackEvent = function(id, event) {
        try {
            navigator.sendBeacon('http://smget-26.3.lar/v1/track', JSON.stringify({
                widget_id: id,
                event: event,
                url: window.location.pathname
            }));
        } catch(e) {
            console.error('Track error:', e);
        }
    };

    // Функция загрузки скрипта виджета
    function loadWidgetScript(widget) {
        return new Promise((resolve, reject) => {
            try {
                // Выполняем JS код виджета
                const scriptFunction = new Function(widget.assets.js);
                scriptFunction();
                resolve();
            } catch (e) {
                reject(e);
            }
        });
    }

    // Загружаем все виджеты
    Promise.all(payload.widgets.map(widget => loadWidgetScript(widget)))
        .then(() => {
            payload.widgets.forEach(widget => {
                try {
                    const className = 'SmWidget_' + widget.type.replace(/-/g, '_');

                    if (typeof window[className] === 'function') {
                        const instance = new window[className](widget.settings, widget.id, widget.assets);
                        instance.init();
                    } else {
                        console.error(`SMGET: Class ${className} not found. Available:`, Object.keys(window).filter(k => k.startsWith('SmWidget_')));
                    }
                } catch (e) {
                    console.error(`SMGET error [${widget.type}]:`, e);
                }
            });
        })
        .catch(error => {
            console.error('SMGET: Failed to load widget scripts:', error);
        });
})();

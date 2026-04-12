(function() {
    const payload = window.SmGet;
    if (!payload || !payload.widgets) return;

    window.SmGet.trackEvent = function(widgetId, eventName) {
        const url = 'https://your-domain.com/v1/track'; // Замените на ваш актуальный домен
        navigator.sendBeacon(url, JSON.stringify({
            widget_id: widgetId,
            event: eventName,
            url: window.location.pathname
        }));
    };

    payload.widgets.forEach(widget => {
        // 1. Исполняем JS код виджета (объявляет функцию initWidget_...)
        if (widget.assets.js) {
            try {
                const scriptFunction = new Function(widget.assets.js);
                scriptFunction();
            } catch (e) {
                console.error(`SMGET: JS Execution error for ${widget.type}`, e);
            }
        }

        // 2. Вызываем инициализацию
        const initFuncName = 'initWidget_' + widget.type.replace(/-/g, '_');
        if (typeof window[initFuncName] === 'function') {
            window[initFuncName](widget.settings, widget.id, widget.assets);
        }
    });
})();

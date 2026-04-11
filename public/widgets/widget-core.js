(function() {
    console.log('SMGET: Core initialized');
    const payload = window.SmGet;

    if (!payload || !payload.widgets) {
        console.error('SMGET: No payload found in window.SmGet');
        return;
    }

    // Универсальная функция отправки события
    window.SmGet.trackEvent = function(widgetId, eventName) {
        const url = 'http://smget-26.3.lar/v1/track';
        const data = JSON.stringify({
            widget_id: widgetId,
            event: eventName, // 'view' или 'click'
            url: window.location.pathname,
            _token: window.SmGet.csrf_placeholder // если понадобится позже
        });

        // sendBeacon гарантирует отправку даже если пользователь закроет вкладку
        navigator.sendBeacon(url, data);
    };

    payload.widgets.forEach(widget => {
        console.log(`SMGET: Processing widget ${widget.type}`);

        // 1. Подгружаем стили
        if (widget.assets.css) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = widget.assets.css;
            document.head.appendChild(link);
        }

        // 2. Подгружаем логику
        if (widget.assets.js) {
            const script = document.createElement('script');
            script.src = widget.assets.js;
            script.async = true;
            script.onload = () => {
                console.log(`SMGET: Script for ${widget.type} loaded`);
                // Формируем имя функции: initWidget_cookie_pops
                const initFuncName = 'initWidget_' + widget.type.replace(/-/g, '_');
                if (typeof window[initFuncName] === 'function') {
                    window[initFuncName](widget.config, widget.id);

                    // АВТО-ТРЕКИНГ: Фиксируем показ виджета сразу после загрузки
                    window.SmGet.trackEvent(widget.id, 'view');
                } else {
                    console.error(`SMGET: Function ${initFuncName} not found in ${widget.assets.js}`);
                }
            };
            document.body.appendChild(script);
        }
    });
})();

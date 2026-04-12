window.initWidget_cookie_pops = function(config, widgetId) {
    // Безопасное слияние конфига с дефолтами
    const settings = {
        text: config.text || 'Мы используем cookie',
        button_text: config.button_text || 'OK',
        delay: (config.delay || 1) * 1000,
        colors: {
            bg: (config.colors && config.colors.bg) || '#fff',
            text: (config.colors && config.colors.text) || '#000',
            btn_bg: (config.colors && config.colors.btn_bg) || '#007bff',
            btn_text: (config.colors && config.colors.btn_text) || '#fff'
        },
        position: config.position || 'bottom-right'
    };

    setTimeout(() => {
        const html = `
            <div id="sm-cookie-pops" style="position:fixed; bottom:20px; right:20px; background:${settings.colors.bg}; color:${settings.colors.text}; padding:20px; border-radius:10px; z-index:999999; box-shadow: 0 5px 20px rgba(0,0,0,0.2); font-family: sans-serif; width: 300px;">
                <div style="margin-bottom:15px">${settings.text}</div>
                <button id="sm-accept" style="background:${settings.colors.btn_bg}; color:${settings.colors.btn_text}; border:none; width:100%; padding:10px; border-radius:5px; cursor:pointer; font-weight:bold;">
                    ${settings.button_text}
                </button>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);

        document.getElementById('sm-accept').onclick = function() {
            if (window.SmGet && window.SmGet.trackEvent) {
                window.SmGet.trackEvent(widgetId, 'click');
            }
            document.getElementById('sm-cookie-pops').remove();
        };

        // Автоматический трекинг показа
        if (window.SmGet && window.SmGet.trackEvent) {
            window.SmGet.trackEvent(widgetId, 'view');
        }

    }, settings.delay);
};

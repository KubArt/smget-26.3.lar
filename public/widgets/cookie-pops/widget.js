// ВАЖНО: Имя функции должно строго совпадать с тем, что генерирует ядро
window.initWidget_cookie_pops = function(config, widgetId) {
    console.log('SMGET: Cookie Pops runner started', config);

    const html = `
        <div id="sm-cookie-pops" style="position:fixed; bottom:20px; right:20px; background:${config.colors.bg}; color:${config.colors.text}; padding:20px; border-radius:10px; z-index:999999; box-shadow: 0 5px 20px rgba(0,0,0,0.2); font-family: sans-serif; width: 300px;">
            <div style="margin-bottom:15px">${config.text}</div>
            <button id="sm-accept" style="background:${config.colors.btn_bg}; color:${config.colors.btn_text}; border:none; width:100%; padding:10px; border-radius:5px; cursor:pointer;">
                ${config.button_text}
            </button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', html);

    document.getElementById('sm-accept').onclick = function() {
        document.getElementById('sm-cookie-pops').remove();
        console.log('SMGET: Cookie Pops closed');
    };

    document.getElementById('sm-accept').onclick = function() {
        // ОТПРАВКА КЛИКА
        if (window.SmGet && window.SmGet.trackEvent) {
            window.SmGet.trackEvent(widgetId, 'click');
        }

        document.getElementById('sm-cookie-pops').remove();
    };

};

(function() {
    const scriptTag = document.currentScript;
    const apiKey = scriptTag.getAttribute('data-key');
    const apiUrl = "https://your-domain.com/api/v1/get-widgets"; // Твой URL

    if (!apiKey) return console.error('SMGET: API Key missing');

    // 1. Запрашиваем список активных виджетов
    fetch(`${apiUrl}?key=${apiKey}`)
        .then(response => response.json())
        .then(data => {
            if (data.widgets && data.widgets.length > 0) {
                initWidgets(data.widgets);
            }
        });

    function initWidgets(widgets) {
        widgets.forEach(widget => {
            // 2. Подгружаем CSS виджета
            if (widget.assets.css) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = widget.assets.css;
                document.head.appendChild(link);
            }

            // 3. Подгружаем JS виджета
            if (widget.assets.js) {
                const script = document.createElement('script');
                script.src = widget.assets.js;
                script.async = true;
                script.onload = () => {
                    // Инициализируем виджет, когда скрипт загрузится
                    // Каждый виджет должен иметь глобальную функцию инициализации
                    const initFn = `initSmWidget_${widget.type.replace(/-/g, '_')}`;
                    if (typeof window[initFn] === 'function') {
                        window[initFn](widget.config);
                    }
                };
                document.body.appendChild(script);
            }
        });
    }
})();

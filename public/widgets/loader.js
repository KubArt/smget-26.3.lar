(function() {
    const self = document.currentScript;
    const apiKey = self.getAttribute('data-key');
    if (!apiKey) return;

    // Сбор данных о пути и источнике
    const pageData = {
        key: apiKey,
        url: window.location.href,
        path: window.location.pathname,
        referrer: document.referrer,
        title: document.title
    };

    const params = new URLSearchParams(pageData).toString();

    fetch(`http://smget-26.3.lar/v1/get-widgets?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.widgets && data.widgets.length > 0) {
                window.SmGet = data;
                window.SmGet.context = pageData; // Сохраняем контекст для ядра

                const core = document.createElement('script');
                core.src = 'http://smget-26.3.lar/widgets/widget-core.js';
                core.async = true;
                document.head.appendChild(core);
            }
        });
})();

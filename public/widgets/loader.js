(function() {
    const self = document.currentScript;
    const apiKey = self.getAttribute('data-key');
    if (!apiKey) return;

    // Реестр загруженных типов в текущем окне
    window.SmLoadedTypes = window.SmLoadedTypes || new Set();

    const pageData = {
        key: apiKey,
        path: window.location.pathname,
        referrer: document.referrer,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        _t: new Date().getTime()
    };

    const params = new URLSearchParams(pageData).toString();

    // Используем протокол текущей страницы
    const protocol = window.location.protocol;
    const apiUrl = `${protocol}//smget-26.3.lar/api/v1/get-widgets?${params}`;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (!data.widgets) return;
            window.SmGet = data;
            const uniqueWidgets = data.widgets.filter(w => {
                if (window.SmLoadedTypes.has(w.type)) {
                    console.warn(`SMGET: Widget type [${w.type}] already loaded. Skipping.`);
                    return false;
                }
                window.SmLoadedTypes.add(w.type);
                return true;
            });

            if (uniqueWidgets.length > 0) {
                window.SmGet.widgets = uniqueWidgets;

                const core = document.createElement('script');
                core.src = `${protocol}//smget-26.3.lar/widgets/widget-core.js`;
                core.async = true;
                document.head.appendChild(core);
            }
        })
        .catch(error => {
            console.error('SMGET: Failed to load widgets:', error);
        });
})();

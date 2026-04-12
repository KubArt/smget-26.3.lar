(function() {
    const self = document.currentScript;
    const apiKey = self.getAttribute('data-key');
    if (!apiKey) return;

    // 1. Функция сбора и кэширования UTM
    const getStoredUtm = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
        let utm = {};

        utmKeys.forEach(key => {
            const val = urlParams.get(key);
            if (val) {
                // Если метка есть в URL, сохраняем/обновляем в сессии
                sessionStorage.setItem(`smget_${key}`, val);
                utm[key] = val;
            } else {
                // Если в URL нет, пробуем достать из сессии
                const stored = sessionStorage.getItem(`smget_${key}`);
                if (stored) utm[key] = stored;
            }
        });
        return utm;
    };

    const utmParams = getStoredUtm();

    const pageData = {
        key: apiKey,
        path: window.location.pathname,
        referrer: document.referrer,
        ...utmParams // Подмешиваем метки (из URL или из памяти)
    };

    const params = new URLSearchParams(pageData).toString();

    fetch(`http://smget-26.3.lar/v1/get-widgets?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.widgets && data.widgets.length > 0) {
                window.SmGet = data;
                window.SmGet.context = pageData;

                const core = document.createElement('script');
                core.src = 'http://smget-26.3.lar/widgets/widget-core.js';
                core.async = true;
                document.head.appendChild(core);
            }
        });
})();

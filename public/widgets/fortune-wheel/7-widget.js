/**
 * Виджет "Колесо Фортуны"
 * Полная версия для встраивания на сайт
 */
window.SmWidget_fortune_wheel = class extends SmWidget {
    constructor(settings, id, assets, behavior) {
        super(settings, id, assets, behavior);

        // Выносим настройки для удобства
        this.buttonConfig = settings.button || {};
        this.wheelConfig = settings.wheel || {};
        this.formConfig = settings.form || {};
        this.designConfig = settings.design || {};
        this.animationConfig = settings.animation || {};
        this.messages = settings.messages || {};
        this.limits = settings.limits || {};
        this.outer_border_width = settings.outer_border_width || 9;

        // Ключи для хранения
        this.storageKey = `sfw_${id}`;
        this.spinsCountKey = `sfw_${id}_spins`;


        // API настройки
        this.apiKey = settings.api_key || null;
        this.apiUrl = settings.api_url || 'http://smget-26.3.lar/api/v1/capture/fortune-wheel';
        this.siteId = settings.site_id || null;
        this.widgetId = this.id;


        // Состояние виджета
        this.isSpinning = false;
        this.currentRotation = 0;
        this.wonSegment = null;
        this.userContact = null;
        this.termsAccepted = false;
        this.isContactFormShown = true;

        // DOM элементы (будут заполнены в mount)
        this.canvas = null;
        this.ctx = null;
        this.actionsContainer = null;
    }

    init() {
        // Проверка лимита попыток
        if (this.isLimitReached()) {
            return;
        }
        super._init();
    }

    mount() {
        this.injectStyles();
        this.injectCSSVariables();
        this.render();
        this.initStates();
        this.initCanvas();
        this.bindEvents();
        this.track('view');


        // Автооткрытие если настроено
        if (this.buttonConfig.auto_open_delay > 0) {
            setTimeout(() => this.openModal(), this.buttonConfig.auto_open_delay * 1000);
        }
    }

    /**
     * Инжекция CSS переменных
     */
    injectCSSVariables() {
        const styleId = `sfw-vars-${this.id}`;
        if (!document.getElementById(styleId)) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = `
                .sfw-root {
                    --sfw-btn-bg: ${this.buttonConfig.bg_color || '#6366f1'};
                    --sfw-btn-text: ${this.buttonConfig.text_color || '#ffffff'};
                    --sfw-accent: ${this.designConfig.accent_color || '#6366f1'};
                    --sfw-modal-bg: ${this.designConfig.modal_bg_color || '#ffffff'};
                    --sfw-modal-text: ${this.designConfig.modal_text_color || '#1f2937'};
                    --sfw-pointer: ${this.wheelConfig.pointer_color || '#ff4444'};
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Инжекция CSS скина
     */
    injectStyles() {
        const styleId = `sfw-style-${this.id}`;
        if (!document.getElementById(styleId) && this.assets.css) {
            const style = document.createElement('style');
            style.id = styleId;
            style.textContent = this.assets.css;
            document.head.appendChild(style);
        }
    }


    /**
     * Рендер/обновление состояний (один метод вместо трех)
     */
    setState(state, data = {}) {
        const states = {
            contact: this.container.querySelector('.sfw-state-contact'),
            spinner: this.container.querySelector('.sfw-state-spinner'),
            result: this.container.querySelector('.sfw-state-result')
        };

        // Скрываем все состояния
        Object.values(states).forEach(el => {
            if (el) el.style.display = 'none';
        });

        // Показываем нужное
        if (states[state]) {
            states[state].style.display = 'block';
        }

        // Если есть данные для результата - обновляем
        if (state === 'result' && data.segment) {
            const labelEl = this.container.querySelector('.sfw-win-label');
            const codeEl = this.container.querySelector('.sfw-win-code');
            if (labelEl) labelEl.textContent = data.segment.label;
            if (codeEl) codeEl.textContent = data.segment.value || 'PROMO2024';
        }
    }
    /**
     * Инициализация после монтирования
     */
    initStates() {
        // Подставляем плейсхолдеры из конфига
        const contactInput = this.container.querySelector('.sfw-contact-input');
        if (contactInput) {
            const contactType = this.formConfig.contact_type || 'tel';
            contactInput.type = contactType;
            contactInput.placeholder = contactType === 'tel' ? '+7 (999) 123-45-67' : 'your@email.com';

            // Устанавливаем правильное имя поля для отправки на сервер
            // Если тип tel - поле называется 'phone', если email - 'email'
            contactInput.name = contactType === 'tel' ? 'phone' : 'email';
        }

        const termsSpan = this.container.querySelector('.sfw-terms-checkbox + span');
        if (termsSpan) {
            termsSpan.textContent = this.formConfig.terms_text || 'Я согласен с условиями розыгрыша';
        }

        const spinBtn = this.container.querySelector('.sfw-state-contact .sfw-spin-trigger');
        if (spinBtn) {
            spinBtn.textContent = this.buttonConfig.text || 'Крутить колесо';
        }

        // Показываем начальное состояние
        this.setState('contact');
    }

    /**
     * Рендер HTML виджета
     */
    render() {
        let html = this.assets.html
            .replace(/{id}/g, this.id)
            .replace(/{widget_id}/g, this.id)
            .replace(/{position}/g, this.buttonConfig.position === 'bottom-right' ? 'right' : 'left')
            .replace(/{title}/g, this.escapeHtml(this.designConfig.title || 'Выиграйте приз!'))
            .replace(/{description}/g, this.escapeHtml(this.designConfig.description || 'Испытайте свою удачу'))
            .replace(/{contact_type}/g, this.formConfig.contact_type || 'tel')
            .replace(/{contact_placeholder}/g, this.formConfig.contact_type === 'tel' ? '+7 (999) 123-45-67' : 'your@email.com')
            .replace(/{terms_text}/g, this.escapeHtml(this.formConfig.terms_text || 'Я согласен с условиями розыгрыша'))
            .replace(/{spin_button_text}/g, this.escapeHtml(this.buttonConfig.text || 'Крутить колесо'))
            .replace(/{decline_text}/g, this.escapeHtml(this.formConfig.decline_text || 'Отказаться'))
            .replace(/{win_title}/g, this.escapeHtml(this.formConfig.title || 'Поздравляем!'));


        this.container = this.createContainer(html, `sfw-root sp-position-${this.buttonConfig.position === 'bottom-right' ? 'right' : 'left'}`);

        // Применяем стили к кнопке
        const trigger = this.container.querySelector('.sfw-trigger');
        if (trigger) {
            const sizeMap = { small: '45px', medium: '60px', large: '75px' };
            const size = sizeMap[this.buttonConfig.size] || '60px';
            trigger.style.width = size;
            trigger.style.height = size;
            trigger.style.fontSize = this.buttonConfig.size === 'small' ? '20px' : (this.buttonConfig.size === 'large' ? '32px' : '28px');
            trigger.style.borderRadius = this.buttonConfig.border_radius || '50px';
            trigger.style.background = this.buttonConfig.bg_color;
            trigger.style.color = this.buttonConfig.text_color;

            const iconSpan = trigger.querySelector('.sfw-icon');
            if (iconSpan) iconSpan.textContent = this.buttonConfig.icon || '🎡';
        }

        // Применяем эффекты к корню
        if (this.designConfig.hover_effect && this.designConfig.hover_effect !== 'none') {
            this.container.classList.add(`sp-hover-${this.designConfig.hover_effect}`);
        }
        if (this.animationConfig.type && this.animationConfig.type !== 'none') {
            this.container.classList.add(`sp-animation-${this.animationConfig.type}`);
        }
        if (this.designConfig.opacity) {
            const trigger = this.container.querySelector('.sfw-trigger');
            if (trigger) trigger.style.opacity = this.designConfig.opacity;
        }
    }

    /**
     * Инициализация Canvas
     */
    initCanvas() {
        this.canvas = this.container.querySelector(`#sfw-canvas-${this.id}`);
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.drawWheel();
    }

    /**
     * Отрисовка колеса
     */
    drawWheel() {
        // Получаем активные сегменты (призы) - отфильтровываем отключенные
        const segments = (this.wheelConfig.segments || []).filter(s => s.enabled !== false);
        const size = 380; // Фиксированный размер canvas (можно брать из настроек)

        // Устанавливаем физический размер canvas (в пикселях)
        this.canvas.width = size;
        this.canvas.height = size;

        // Если нет ни одного активного сегмента - рисуем серый фон
        if (segments.length === 0) {
            this.ctx.fillStyle = '#e5e7eb';
            this.ctx.fillRect(0, 0, size, size);
            return;
        }

        // Геометрические параметры колеса
        const radius = size / 2;      // Радиус колеса (190px)
        const centerX = radius;        // Центр X (190px)
        const centerY = radius;        // Центр Y (190px)
        const arc = (2 * Math.PI) / segments.length;  // Угол одного сегмента в радианах

        // Очищаем canvas перед отрисовкой (без теней, чтобы тень не вращалась)
        this.ctx.clearRect(0, 0, size, size);

        // ========== 1. ОТРИСОВКА СЕГМЕНТОВ (секторов колеса) ==========
        segments.forEach((seg, i) => {
            const startAngle = i * arc;      // Начало сегмента (в радианах)
            const endAngle = startAngle + arc; // Конец сегмента

            // ---- 1.1 ЗАЛИВКА СЕГМЕНТА (цвет) ----
            this.ctx.beginPath();
            // Цвет: из настроек сегмента, иначе чередование серых оттенков
            this.ctx.fillStyle = seg.bg_color || (i % 2 ? '#f1f5f9' : '#e2e8f0');
            this.ctx.moveTo(centerX, centerY);                    // Начинаем из центра
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle); // Дуга
            this.ctx.fill();  // Заливаем сектор

            // ---- 1.2 ВНУТРЕННИЕ ГРАНИЦЫ СЕГМЕНТОВ (белые разделительные линии) ----
            this.ctx.beginPath();
            this.ctx.strokeStyle = this.wheelConfig.border_color || '#ffffff';
            this.ctx.lineWidth = this.wheelConfig.border_width || 3;
            this.ctx.moveTo(centerX, centerY);
            this.ctx.arc(centerX, centerY, radius - 10, startAngle, endAngle);
            this.ctx.lineTo(centerX, centerY);
            this.ctx.stroke();  // Рисуем границу

            // ---- 1.3 ТЕКСТ НА СЕГМЕНТЕ (название приза) ----
            this.drawTextOnSegment(seg.label, centerX, centerY, radius, startAngle, endAngle);
        });

        // ========== 3. ВНУТРЕННИЙ КРУГ (центральная часть колеса) ==========
        // ---- 3.1 БЕЛАЯ ЗАЛИВКА ----
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fill();

        // ========== 2. ВНЕШНЯЯ ОБВОДКА КОЛЕСА (обводка по внешнему кругу) ==========
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, radius - 10, 0, 2 * Math.PI);
        this.ctx.strokeStyle = this.wheelConfig.border_color || '#ffffff';
        this.ctx.lineWidth = this.wheelConfig.outer_border_width || 9; // Чуть толще внутренних линий
        this.ctx.stroke();

        // ========== 4. ЦЕНТРАЛЬНАЯ ТОЧКА (маленький кружок в середине) ==========
        this.ctx.beginPath();
        this.ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
        this.ctx.fillStyle = this.designConfig.accent_color || '#6366f1';
        this.ctx.fill();
    }

    /**
     * Отрисовка текста на сегменте с переносом слов
     */
    drawTextOnSegment(text, centerX, centerY, radius, startAngle, endAngle) {
        if (!text) return;

        const angle = startAngle + (endAngle - startAngle) / 2;
        const textRadius = radius - 75; // Расстояние от центра

        // Разбиваем текст на строки
        const lines = this.wrapText(text, 10); // максимум 10 символов в строке
        const fontSize = this.calculateFontSize(lines, radius);

        this.ctx.save();
        this.ctx.translate(centerX, centerY);
        this.ctx.rotate(angle);
        this.ctx.textAlign = "center";
        this.ctx.textBaseline = "middle";
        this.ctx.fillStyle = this.wheelConfig.text_color || '#1f2937';
        this.ctx.font = `bold ${fontSize}px system-ui`;

        // Отрисовка построчно
        const lineHeight = fontSize * 1.2;
        const startY = -((lines.length - 1) * lineHeight) / 2;

        lines.forEach((line, i) => {
            this.ctx.fillText(line, textRadius, startY + (i * lineHeight));
        });

        this.ctx.restore();
    }

    /**
     * Разбивка текста на строки
     */
    wrapText(text, maxCharsPerLine) {
        const words = text.split(' ');
        const lines = [];
        let currentLine = '';

        for (let word of words) {
            if ((currentLine + ' ' + word).trim().length <= maxCharsPerLine) {
                currentLine = currentLine ? currentLine + ' ' + word : word;
            } else {
                if (currentLine) lines.push(currentLine);
                currentLine = word;
            }
        }
        if (currentLine) lines.push(currentLine);

        // Если все равно длинная строка - принудительно режем
        if (lines.length === 1 && lines[0].length > maxCharsPerLine) {
            return [lines[0].slice(0, maxCharsPerLine - 2) + '..'];
        }

        return lines;
    }

    /**
     * Расчет размера шрифта в зависимости от количества строк
     */
    calculateFontSize(lines, radius) {
        const baseSize = Math.min(24, radius / 11);
        if (lines.length === 1) return baseSize;
        if (lines.length === 2) return baseSize - 2;
        return baseSize - 4;
    }

    /**
     * Запуск вращения
     */
    startSpin() {
        if (this.isSpinning) return;

        // Останавливаем медленное вращение ДО сброса
        this.canvas.classList.remove('sfw-idle-spin');

        // Сбрасываем в 0 без анимации
        this.canvas.style.transition = 'none';
        this.canvas.style.transform = 'rotate(0deg)';
        this.currentRotation = 0;  // ← Сбросить текущий угол
        void this.canvas.offsetHeight;

        const segments = (this.wheelConfig.segments || []).filter(s => s.enabled !== false);
        if (segments.length === 0) return;

        this.isSpinning = true;

        const winIndex = this.getRandomSegmentIndex(segments);
        this.wonSegment = segments[winIndex];

        const segmentDeg = 360 / segments.length;
        const rotationNeeded = (360 - (winIndex * segmentDeg)) - (segmentDeg / 2);
        const totalRotation = 1440 + rotationNeeded;

        this.currentRotation = totalRotation;  // ← присваиваем, а не прибавляем

        this.canvas.style.transition = `transform ${this.wheelConfig.rotation_speed || 4}s cubic-bezier(0.25, 0.1, 0.15, 1)`;
        this.canvas.style.transform = `rotate(${this.currentRotation}deg)`;

        this.track('spin_start');

        setTimeout(() => this.onSpinEnd(), (this.wheelConfig.rotation_speed || 4) * 1000);
    }

    /**
     * Выбор случайного сегмента с учетом веса
     */
    getRandomSegmentIndex(segments) {
        // TODO: добавить поддержку веса призов
        return Math.floor(Math.random() * segments.length);
    }

    /**
     * Завершение вращения
     */
    onSpinEnd() {
        this.isSpinning = false;
        this.track('spin_win');
        this.saveSpin();
     //   this.showWinResult();
        this.sendLead();
    }

    /**
     * Показ результата выигрыша
     */
    w_showWinResult() {
        this.setState('result', { segment: this.wonSegment });
    }
    /**
     * Показ результата выигрыша
     */
    showWinResult() {
        // Обновляем данные в блоке результата
        const labelEl = this.container?.querySelector('.sfw-win-label');
        const codeEl = this.container?.querySelector('.sfw-win-code');
        const messageEl = this.container?.querySelector('.sfw-win-message');
        const expiresEl = this.container?.querySelector('.sfw-win-expires');

        if (labelEl) labelEl.textContent = this.prizeData?.name || this.wonSegment.label;
        if (codeEl) codeEl.textContent = this.prizeData?.code;
        if (messageEl) messageEl.textContent = this.winMessage || this.formConfig.success_message;
        if (expiresEl && this.prizeData?.expires_at) {
            const date = new Date(this.prizeData.expires_at);
            expiresEl.textContent = `Действителен до: ${date.toLocaleDateString()}`;
            expiresEl.style.display = 'block';
        }

        this.setPreviewState('result');
        this.track('prize_received');
    }

    /**
     * Отправка лида на webhook
     */
    async sendLead() {

        const contact = this.userContact;
        const prizeCode = this.wonSegment?.value;

        if (!contact || !prizeCode) {
            this.showError('Ошибка получения приза');
            return;
        }

        //this.setPreviewState('spinner');

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Api-Key': 'widget',
                },
                body: JSON.stringify({
                    widget_id: this.widgetId,
                    contact: contact,
                    prize_code: prizeCode,
                    prize_label: this.wonSegment.label,
                    name: contact,
                    page_url: window.location.href,
                })
            });

            const data = await response.json();

            if (response.ok && data.status === 'success') {
                // Приз успешно получен
                this.prizeData = data.prize;
                this.winMessage = data.message;
                this.showWinResult();
            } else {
                this.handlePrizeError(data);
            }
        } catch (error) {
            console.error('API error:', error);
            this.showError('Ошибка соединения. Пожалуйста, попробуйте позже.');
        }
    }

    /**
     * Обработка ошибок от API
     */
    handlePrizeError(data) {
        const errorCode = data.code;
        let errorMessage = this.messages.default_error || 'Ошибка получения приза';

        switch (errorCode) {
            case 'INVALID_PRIZE_CODE':
                errorMessage = this.messages.invalid_prize || 'Неверный код приза';
                break;
            case 'PRIZE_CODE_EXPIRED':
                errorMessage = this.messages.prize_expired || 'Срок действия приза истек';
                break;
            case 'MAX_ATTEMPTS_REACHED':
                const attempts = data.attempts_used || 0;
                const limit = data.attempts_limit || 3;
                errorMessage = `Вы уже использовали ${attempts} из ${limit} попыток`;
                break;
            case 'CONTACT_REQUIRED':
                errorMessage = this.messages.fill_contact || 'Укажите контактные данные';
                break;
            default:
                errorMessage = data.error || this.messages.default_error;
        }

        this.showError(errorMessage);

        // Возвращаем в форму контакта
        setTimeout(() => {
            this.setPreviewState('contact');
            this.resetWheel();
        }, 2000);
    }
    /**
     * Показать ошибку пользователю
     */
    showError(message) {
        const actionsContainer = this.container?.querySelector('.sfw-actions');
        if (actionsContainer) {
            actionsContainer.innerHTML = `
                <div class="sfw-error-result">
                    <div class="sfw-error-icon">⚠️</div>
                    <p>${this.escapeHtml(message)}</p>
                    <button class="sfw-close-error">Закрыть</button>
                </div>
            `;

            const closeBtn = actionsContainer.querySelector('.sfw-close-error');
            if (closeBtn) {
                closeBtn.onclick = () => {
                    this.closeModal();
                    this.resetWheel();
                };
            }
        }
    }

    /**
     * Получение cookie по имени
     */
    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    /**
     * Сброс колеса
     */
    resetWheel() {
        this.currentRotation = 0;
        if (this.canvas) {
            this.canvas.style.transform = 'rotate(0deg)';
            this.canvas.style.transition = 'none';
        }
        this.isContactFormShown = true;
        this.userContact = null;
        this.termsAccepted = false;
    }

    /**
     * Проверка лимита попыток
     */
    isLimitReached() {
        const spinsPerUser = this.limits.spins_per_user || 0;
        if (spinsPerUser === 0) return false;

        const spins = parseInt(localStorage.getItem(this.spinsCountKey) || '0');
        return spins >= spinsPerUser;
    }

    /**
     * Сохранение попытки
     */
    saveSpin() {
        const spinsPerUser = this.limits.spins_per_user || 0;
        if (spinsPerUser === 0) return;

        const currentSpins = parseInt(localStorage.getItem(this.spinsCountKey) || '0');
        localStorage.setItem(this.spinsCountKey, (currentSpins + 1).toString());
    }

    /**
     * Привязка событий виджета
     */
    bindEvents() {
        // Кнопка открытия
        const toggleBtn = this.container.querySelector('[data-sp-toggle]');
        if (toggleBtn) {
            toggleBtn.onclick = () => this.openModal();
        }

        // Кнопки закрытия
        const closeBtns = this.container.querySelectorAll('[data-sp-close]');
        closeBtns.forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                this.closeModal();
            };
        });

        // Закрытие по оверлею
        const overlay = this.container.querySelector('.sfw-overlay');
        if (overlay) {
            overlay.onclick = (e) => {
                if (e.target === overlay) this.closeModal();
            };
        }
        // Кнопка спина в форме контакта
        // === ГЛАВНОЕ: Кнопка спина с проверкой формы ===
        const spinBtn = this.container.querySelector('.sfw-state-contact .sfw-spin-trigger');
        if (spinBtn) {
            spinBtn.onclick = () => {
                const contactInput = this.container.querySelector('.sfw-contact-input');
                const termsCheckbox = this.container.querySelector('.sfw-terms-checkbox');

                let hasError = false;  // ← НУЖНО

                if (!contactInput?.value) {
                    this.showFieldError(contactInput);
                    hasError = true;   // ← ДОБАВИТЬ
                }
                if (!termsCheckbox?.checked) {
                    this.showFieldError(termsCheckbox);
                    hasError = true;   // ← ДОБАВИТЬ
                }

                if (hasError) return;

                this.userContact = contactInput.value;
                this.setState('spinner');
                this.startSpin();
            };
        }
        // Кнопка отказа
        const declineBtn = this.container.querySelector('.sfw-state-contact .sfw-decline-btn');
        if (declineBtn) {
            declineBtn.onclick = () => {
                this.closeModal();
                this.track('decline');
            };
        }

        // Кнопка закрытия результата
        const closeWinBtn = this.container.querySelector('.sfw-state-result .sfw-close-win');
        if (closeWinBtn) {
            closeWinBtn.onclick = () => {
                this.closeModal();
                this.resetWheel();
            };
        }
    }

    // Вспомогательный метод для показа ошибки
    showFieldError(element) {
        if (!element) return;

        // Если это чекбокс - подсвечиваем его и родителя
        if (element.type === 'checkbox') {
            const parent = element.closest('.sfw-terms');
            if (parent) parent.classList.add('sfw-error');
            element.classList.add('sfw-error');
        } else {
            element.classList.add('sfw-error');
            element.style.borderColor = '#ef4444';
            element.style.backgroundColor = '#fef2f2';
        }

        // Анимация
        element.style.animation = 'sfwShake 0.3s ease';
        setTimeout(() => { element.style.animation = ''; }, 300);

        // Убираем ошибку при фокусе
        const removeError = () => {
            element.classList.remove('sfw-error');
            if (element.type === 'checkbox') {
                const parent = element.closest('.sfw-terms');
                if (parent) parent.classList.remove('sfw-error');
            } else {
                element.style.borderColor = '';
                element.style.backgroundColor = '';
            }
            element.removeEventListener('focus', removeError);
            element.removeEventListener('click', removeError);
        };

        element.addEventListener('focus', removeError, { once: true });
        element.addEventListener('click', removeError, { once: true });
    }

    /**
     * Открытие модального окна
     */
    openModal() {
        if (this.isLimitReached()) {
            alert(this.messages.spin_limit_reached || 'Вы уже использовали все попытки');
            return;
        }
        this.container.classList.add('sp-active');
        this.track('open');
    }

    /**
     * Закрытие модального окна
     */
    closeModal() {
        this.container.classList.remove('sp-active');
    }

};

@push('js')
    <script>
        function fortuneWheelEditor(config) {
            return {
                slug: config.slug,
                settings: config.settings,
                skins: config.skins,
                activeTab: 'button-tab',

                // Для предпросмотра
                rawTemplate: '',
                rawCss: '',
                shadowRoot: null,
                widgetRoot: null,

                // Флаги для предотвращения циклов
                isUpdating: false,
                isInitialized: false,

                async init() {
                    if (this.isInitialized) return;
                    this.isInitialized = true;

                    // Инициализация всех необходимых настроек
                    this.initDefaults();

                    // Инициализация вкладок
                    this.initTabs();

                    // Загружаем скин
                    await this.loadSkin(this.settings.template || 'default');

                    // Watch только для нужных полей, без глубокого слежения
                    this.$watch('settings.button', () => {
                        if (!this.isUpdating) this.updatePreview();
                    });

                    this.$watch('settings.wheel.size', () => {
                        if (!this.isUpdating) this.updatePreview();
                    });

                    this.$watch('settings.wheel.pointer_color', () => {
                        if (!this.isUpdating) this.updatePreview();
                    });

                    this.$watch('settings.design', () => {
                        if (!this.isUpdating) this.updatePreview();
                    });

                    this.$watch('settings.form', () => {
                        if (!this.isUpdating) this.updatePreview();
                    }, { deep: true });

                    this.$watch('settings.coupons', () => {
                        if (!this.isUpdating) {
                            this.updateWheelSegments();
                            this.updatePreview();
                        }
                    }, { deep: true });
                },

                initDefaults() {
                    // Кнопка
                    this.settings.button = this.settings.button || {};
                    this.settings.button.position = this.settings.button.position || 'bottom-right';
                    this.settings.button.custom_position = this.settings.button.custom_position || { x: 20, y: 20 };
                    this.settings.button.text = this.settings.button.text || 'Крутить колесо';
                    this.settings.button.icon = this.settings.button.icon || '🎡';
                    this.settings.button.size = this.settings.button.size || 'medium';
                    this.settings.button.bg_color = this.settings.button.bg_color || '#FF6B6B';
                    this.settings.button.text_color = this.settings.button.text_color || '#FFFFFF';
                    this.settings.button.border_radius = this.settings.button.border_radius || '50px';
                    this.settings.button.show_on_load = this.settings.button.show_on_load !== false;
                    this.settings.button.auto_open_delay = this.settings.button.auto_open_delay || 0;

                    // Колесо
                    this.settings.wheel = this.settings.wheel || {};
                    this.settings.wheel.size = this.settings.wheel.size || 400;
                    this.settings.wheel.rotation_speed = this.settings.wheel.rotation_speed || 8;
                    this.settings.wheel.background_color = this.settings.wheel.background_color || '#FFFFFF';
                    this.settings.wheel.text_color = this.settings.wheel.text_color || '#FFFFFF';
                    this.settings.wheel.border_color = this.settings.wheel.border_color || '#FFD700';
                    this.settings.wheel.border_width = this.settings.wheel.border_width || 3;
                    this.settings.wheel.pointer_color = this.settings.wheel.pointer_color || '#FF4444';
                    this.settings.wheel.font_size = this.settings.wheel.font_size || 14;
                    this.settings.wheel.segments = this.settings.wheel.segments || [];

                    // Дизайн модального окна
                    this.settings.design = this.settings.design || {};
                    this.settings.design.modal_bg = this.settings.design.modal_bg || '#FFFFFF';
                    this.settings.design.accent_color = this.settings.design.accent_color || '#667eea';
                    this.settings.design.title = this.settings.design.title || 'Выиграйте приз!';
                    this.settings.design.description = this.settings.design.description || 'Крутите колесо и получите скидку до 50%';

                    // Купоны
                    this.settings.coupons = this.settings.coupons || [];
                    if (this.settings.coupons.length === 0) {
                        this.settings.coupons = [
                            { id: '1', enabled: true, name: 'Скидка 10%', probability: 30, code: 'WELCOME10', generate_unique: false, expiry_days: 30, color: '#FF6B6B', description: 'Скидка 10% на первый заказ' },
                            { id: '2', enabled: true, name: 'Скидка 20%', probability: 20, code: '', generate_unique: true, expiry_days: 14, color: '#4ECDC4', description: 'Скидка 20% на весь ассортимент' },
                            { id: '3', enabled: true, name: 'Попробуй еще раз', probability: 50, code: '', generate_unique: false, expiry_days: 0, color: '#95A5A6', description: 'К сожалению, вы ничего не выиграли' }
                        ];
                    }

                    // Форма
                    this.settings.form = this.settings.form || {};
                    this.settings.form.enabled = this.settings.form.enabled !== false;
                    this.settings.form.title = this.settings.form.title || 'Поздравляем!';
                    this.settings.form.subtitle = this.settings.form.subtitle || 'Введите ваши данные, чтобы получить приз';
                    this.settings.form.button_text = this.settings.form.button_text || 'Получить приз';
                    this.settings.form.success_message = this.settings.form.success_message || 'Ваш купон: {CODE}';
                    this.settings.form.webhook_url = this.settings.form.webhook_url || '';
                    this.settings.form.fields = this.settings.form.fields || [
                        { type: 'text', name: 'name', label: 'Ваше имя', required: true, placeholder: 'Иван Иванов' },
                        { type: 'email', name: 'email', label: 'Email', required: true, placeholder: 'ivan@example.com' }
                    ];

                    // Лимиты
                    this.settings.limits = this.settings.limits || {};
                    this.settings.limits.spins_per_user = this.settings.limits.spins_per_user || 1;
                    this.settings.limits.spins_per_day = this.settings.limits.spins_per_day || 1;
                    this.settings.limits.require_auth = this.settings.limits.require_auth || false;

                    // Поведение
                    this.settings.trigger_type = this.settings.trigger_type || 'click';
                    this.settings.delay = this.settings.delay || 0;
                    this.settings.scroll_percent = this.settings.scroll_percent || 50;
                    this.settings.frequency = this.settings.frequency || 'once_session';
                    this.settings.close_behavior = this.settings.close_behavior || 'hide_session';

                    // Шаблон
                    this.settings.template = this.settings.template || 'default';

                    // Обновляем сегменты колеса
                    this.updateWheelSegments();
                },

                initTabs() {
                    const tabs = document.querySelectorAll('[data-tab]');
                    tabs.forEach(tab => {
                        tab.addEventListener('click', (e) => {
                            e.preventDefault();
                            const tabId = tab.getAttribute('data-tab');
                            this.switchTab(tabId);
                        });
                    });
                },

                switchTab(tabId) {
                    this.activeTab = tabId;

                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('active');
                        pane.style.display = 'none';
                    });

                    const activePane = document.getElementById(tabId);
                    if (activePane) {
                        activePane.classList.add('active');
                        activePane.style.display = 'block';
                    }

                    document.querySelectorAll('[data-tab]').forEach(tab => {
                        tab.classList.remove('active');
                        if (tab.getAttribute('data-tab') === tabId) {
                            tab.classList.add('active');
                        }
                    });
                },

                updateWheelSegments() {
                    const coupons = (this.settings.coupons || []).filter(c => c.enabled !== false);
                    const totalProbability = coupons.reduce((sum, c) => sum + (parseInt(c.probability) || 0), 0);

                    if (totalProbability === 0 || coupons.length === 0) {
                        this.settings.wheel.segments = [];
                        return;
                    }

                    let currentAngle = 0;
                    const segments = coupons.map(coupon => {
                        const angle = (parseInt(coupon.probability) / totalProbability) * 360;
                        const segment = {
                            id: coupon.id,
                            name: coupon.name,
                            angle: angle,
                            start_angle: currentAngle,
                            end_angle: currentAngle + angle,
                            color: coupon.color || '#FF6B6B'
                        };
                        currentAngle += angle;
                        return segment;
                    });

                    // Обновляем только если изменилось
                    if (JSON.stringify(this.settings.wheel.segments) !== JSON.stringify(segments)) {
                        this.settings.wheel.segments = segments;
                    }
                },

                async loadSkin(skinId) {
                    try {
                        const baseUrl = `/widgets/${this.slug}/skins/${skinId}`;
                        const [htmlRes, cssRes] = await Promise.all([
                            fetch(`${baseUrl}/template.html`),
                            fetch(`${baseUrl}/style.css`)
                        ]);
                        this.rawTemplate = await htmlRes.text();
                        this.rawCss = await cssRes.text();
                        this.initPreview();
                        this.updatePreview();
                    } catch (e) {
                        console.error('Error loading skin:', e);
                    }
                },

                initPreview() {
                    const container = document.getElementById('preview-host');
                    if (!container) return;

                    // Очищаем предыдущий shadowRoot
                    if (this.shadowRoot) {
                        this.shadowRoot.innerHTML = '';
                    }

                    this.shadowRoot = container.shadowRoot || container.attachShadow({ mode: 'open' });

                    // Подготавливаем CSS для предпросмотра (заменяем fixed на absolute)
                    let css = this.rawCss || '';
                    css = css.replace(/position:\s*fixed/g, 'position: absolute');
                    css = css.replace(/position:fixed/g, 'position: absolute');
                    css = css.replace(/100vh/g, '100%');
                    css = css.replace(/100vw/g, '100%');

                    this.shadowRoot.innerHTML = `
                        <style>
                            :host {
                                display: block;
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                            }
                            ${css}
                        </style>
                        <div id="widget-root"></div>
                    `;
                    this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                },

                updatePreview() {
                    if (this.isUpdating) return;
                    if (!this.widgetRoot || !this.rawTemplate) return;

                    this.isUpdating = true;

                    try {
                        // Подставляем данные в шаблон
                        let html = this.rawTemplate
                            .replace(/\{id\}/g, 'preview')
                            .replace(/\{wheel_size\}/g, this.settings.wheel?.size || 400)
                            .replace(/\{button_icon\}/g, this.escapeHtml(this.settings.button?.icon || '🎡'))
                            .replace(/\{button_text\}/g, this.escapeHtml(this.settings.button?.text || 'Крутить колесо'))
                            .replace(/\{title\}/g, this.escapeHtml(this.settings.design?.title || 'Выиграйте приз!'))
                            .replace(/\{description\}/g, this.escapeHtml(this.settings.design?.description || 'Крутите колесо и получите скидку до 50%'))
                            .replace(/\{form_title\}/g, this.escapeHtml(this.settings.form?.title || 'Поздравляем!'))
                            .replace(/\{form_subtitle\}/g, this.escapeHtml(this.settings.form?.subtitle || 'Введите ваши данные, чтобы получить приз'))
                            .replace(/\{form_button_text\}/g, this.escapeHtml(this.settings.form?.button_text || 'Играть'));

                        this.widgetRoot.innerHTML = html;

                        const widget = this.widgetRoot.firstElementChild;
                        if (!widget) return;

                        // Применяем динамические стили через CSS переменные
                        const design = this.settings.design || {};
                        const wheel = this.settings.wheel || {};
                        const btn = this.settings.button || {};

                        widget.style.setProperty('--modal-bg', design.modal_bg || '#FFFFFF');
                        widget.style.setProperty('--accent-color', design.accent_color || '#667eea');
                        widget.style.setProperty('--pointer-color', wheel.pointer_color || '#FF4444');

                        // Стили для кнопки открытия
                        const openBtn = widget.querySelector('.fw-open-button');
                        if (openBtn) {
                            openBtn.style.background = btn.bg_color || '#FF6B6B';
                            openBtn.style.color = btn.text_color || '#FFFFFF';
                            openBtn.style.borderRadius = btn.border_radius || '50px';

                            // Позиция кнопки
                            if (btn.position === 'custom') {
                                openBtn.style.bottom = `${btn.custom_position?.y || 20}px`;
                                openBtn.style.right = `${btn.custom_position?.x || 20}px`;
                            } else {
                                const pos = btn.position || 'bottom-right';
                                if (pos === 'bottom-right') { openBtn.style.bottom = '20px'; openBtn.style.right = '20px'; }
                                if (pos === 'bottom-left') { openBtn.style.bottom = '20px'; openBtn.style.left = '20px'; }
                                if (pos === 'top-right') { openBtn.style.top = '20px'; openBtn.style.right = '20px'; }
                                if (pos === 'top-left') { openBtn.style.top = '20px'; openBtn.style.left = '20px'; }
                            }

                            // Размер кнопки
                            const size = btn.size || 'medium';
                            if (size === 'small') { openBtn.style.padding = '8px 16px'; openBtn.style.fontSize = '14px'; }
                            if (size === 'medium') { openBtn.style.padding = '12px 24px'; openBtn.style.fontSize = '16px'; }
                            if (size === 'large') { openBtn.style.padding = '16px 32px'; openBtn.style.fontSize = '18px'; }
                        }

                        // Отрисовываем колесо на canvas
                        setTimeout(() => this.drawWheelOnCanvas(widget), 50);

                        // Генерируем поля формы
                        this.generateFormFields(widget);

                        // Привязываем события для предпросмотра
                        this.attachPreviewEvents(widget);
                    } catch (e) {
                        console.error('Update preview error:', e);
                    } finally {
                        this.isUpdating = false;
                    }
                },

                drawWheelOnCanvas(widget) {
                    const canvas = widget.querySelector('#fw-canvas-preview');
                    if (!canvas) return;

                    const segments = this.settings.wheel?.segments || [];
                    const size = this.settings.wheel?.size || 400;

                    canvas.width = size;
                    canvas.height = size;

                    const ctx = canvas.getContext('2d');
                    if (!ctx) return;

                    const centerX = size / 2;
                    const centerY = size / 2;
                    const radius = size / 2 - 10;

                    ctx.clearRect(0, 0, size, size);

                    if (segments.length === 0) {
                        // Рисуем сообщение о необходимости добавить призы
                        ctx.font = '14px Arial';
                        ctx.fillStyle = '#999';
                        ctx.textAlign = 'center';
                        ctx.fillText('Добавьте призы', centerX, centerY);
                        ctx.font = '12px Arial';
                        ctx.fillText('в разделе "Купоны"', centerX, centerY + 25);
                        return;
                    }

                    let startAngle = 0;

                    segments.forEach((segment) => {
                        const angle = (segment.angle || 0) * Math.PI / 180;
                        const endAngle = startAngle + angle;

                        ctx.beginPath();
                        ctx.moveTo(centerX, centerY);
                        ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                        ctx.closePath();

                        ctx.fillStyle = segment.color || '#FF6B6B';
                        ctx.fill();

                        ctx.strokeStyle = this.settings.wheel?.border_color || '#FFFFFF';
                        ctx.lineWidth = this.settings.wheel?.border_width || 2;
                        ctx.stroke();

                        // Рисуем текст
                        const textAngle = startAngle + angle / 2;
                        const textRadius = radius * 0.65;
                        const textX = centerX + textRadius * Math.cos(textAngle);
                        const textY = centerY + textRadius * Math.sin(textAngle);

                        let text = segment.name || '';
                        if (text.length > 12) text = text.substr(0, 10) + '..';

                        ctx.save();
                        ctx.translate(textX, textY);
                        ctx.rotate(textAngle + Math.PI / 2);
                        ctx.fillStyle = this.settings.wheel?.text_color || '#FFFFFF';
                        ctx.font = `bold ${Math.max(10, Math.min(14, (segment.angle || 20) / 12))}px Arial`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(text, 0, 0);
                        ctx.restore();

                        startAngle = endAngle;
                    });

                    // Центральный круг
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 25, 0, 2 * Math.PI);
                    ctx.fillStyle = this.settings.wheel?.background_color || '#FFFFFF';
                    ctx.fill();
                    ctx.stroke();

                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 8, 0, 2 * Math.PI);
                    ctx.fillStyle = this.settings.wheel?.pointer_color || '#FF4444';
                    ctx.fill();
                },

                generateFormFields(widget) {
                    const container = widget.querySelector('#fw-form-fields');
                    if (!container) return;

                    const fields = this.settings.form?.fields || [];
                    const fieldsHtml = fields.map(field => {
                        const required = field.required ? 'required' : '';
                        const placeholder = this.escapeHtml(field.placeholder || field.label || '');

                        if (field.type === 'textarea') {
                            return `<textarea name="${field.name}" placeholder="${placeholder}" ${required} class="fw-field"></textarea>`;
                        }
                        return `<input type="${field.type}" name="${field.name}" placeholder="${placeholder}" ${required} class="fw-field">`;
                    }).join('');

                    container.innerHTML = fieldsHtml || '<div class="fw-field-empty">Нет полей формы</div>';
                },

                attachPreviewEvents(widget) {
                    // Простое демо-вращение колеса
                    const canvas = widget.querySelector('#fw-canvas-preview');
                    if (canvas && !canvas._hasEvents) {
                        canvas._hasEvents = true;
                        canvas.style.cursor = 'pointer';
                        canvas.addEventListener('click', () => {
                            canvas.style.transition = 'transform 1s ease-out';
                            canvas.style.transform = 'rotate(1440deg)';
                            setTimeout(() => {
                                canvas.style.transform = '';
                                setTimeout(() => canvas.style.transition = '', 100);
                            }, 1000);
                        });
                    }

                    // Кнопка открытия модалки
                    const openBtn = widget.querySelector('.fw-open-button');
                    const modal = widget.querySelector('.fw-overlay');

                    if (openBtn && modal && !openBtn._hasEvents) {
                        openBtn._hasEvents = true;
                        openBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            modal.style.display = 'flex';
                        });
                    }

                    // Закрытие модалки
                    const closeBtn = widget.querySelector('.fw-close');
                    if (closeBtn && modal && !closeBtn._hasEvents) {
                        closeBtn._hasEvents = true;
                        closeBtn.addEventListener('click', () => {
                            modal.style.display = 'none';
                        });
                    }

                    // Клик по оверлею
                    if (modal && !modal._hasEvents) {
                        modal._hasEvents = true;
                        modal.addEventListener('click', (e) => {
                            if (e.target === modal) {
                                modal.style.display = 'none';
                            }
                        });
                    }

                    // Кнопка "Играть" - имитация вращения
                    const spinBtn = widget.querySelector('.fw-spin-btn');
                    if (spinBtn && !spinBtn._hasEvents) {
                        spinBtn._hasEvents = true;
                        spinBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (canvas) {
                                canvas.style.transition = 'transform 2s ease-out';
                                canvas.style.transform = 'rotate(2160deg)';
                                setTimeout(() => {
                                    canvas.style.transform = '';
                                    setTimeout(() => canvas.style.transition = '', 100);
                                }, 2000);
                            }
                        });
                    }
                },

                async applyTemplate(skinId) {
                    if (this.settings.template === skinId) return;
                    this.settings.template = skinId;
                    await this.loadSkin(skinId);
                },

                addCoupon() {
                    if (!this.settings.coupons) this.settings.coupons = [];
                    this.settings.coupons.push({
                        id: 'coupon_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6),
                        enabled: true,
                        name: 'Новый приз',
                        description: '',
                        probability: 10,
                        code: '',
                        generate_unique: false,
                        expiry_days: 7,
                        color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')
                    });
                    this.updateWheelSegments();
                    this.updatePreview();
                },

                removeCoupon(index) {
                    if (confirm('Удалить этот приз?')) {
                        this.settings.coupons.splice(index, 1);
                        this.updateWheelSegments();
                        this.updatePreview();
                    }
                },

                addFormField() {
                    if (!this.settings.form.fields) this.settings.form.fields = [];
                    this.settings.form.fields.push({
                        type: 'text',
                        name: 'field_' + Date.now(),
                        label: 'Новое поле',
                        placeholder: 'Введите значение',
                        required: false
                    });
                    this.updatePreview();
                },

                removeFormField(index) {
                    this.settings.form.fields.splice(index, 1);
                    this.updatePreview();
                },

                escapeHtml(text) {
                    if (!text) return '';
                    const div = document.createElement('div');
                    div.textContent = text;
                    return div.innerHTML;
                },

                async saveConfig(event) {
                    const btn = event?.target?.closest('button[type="submit"]') || event?.currentTarget;
                    if (!btn) return;

                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                    try {
                        const response = await axios.post(window.location.href, { settings: this.settings });
                        if (response.data.status === 'success') {
                            if (typeof showNotification === 'function') {
                                showNotification(response.data.message, 'success');
                            } else {
                                alert('Настройки сохранены');
                            }
                        }
                    } catch (error) {
                        const msg = error.response?.data?.message || 'Ошибка при сохранении';
                        if (typeof showNotification === 'function') {
                            showNotification(msg, 'danger');
                        } else {
                            alert(msg);
                        }
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                }
            };
        }
    </script>
@endpush

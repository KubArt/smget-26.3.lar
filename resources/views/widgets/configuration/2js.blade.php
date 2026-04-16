@push('js')
    <script>
        function fortuneWheelEditor(config) {
            return {
                slug: config.slug,
                settings: config.settings,
                skins: config.skins,
                activeTab: 'button-tab',

                init() {
                    // Инициализация всех необходимых настроек
                    this.initDefaults();

                    // Инициализация вкладок
                    this.initTabs();

                    // Обновление предпросмотра
                    this.$watch('settings', () => {
                        this.updatePreview();
                    }, { deep: true });

                    // Запуск предпросмотра
                    this.$nextTick(() => {
                        this.updatePreview();
                    });
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

                    // Купоны
                    this.settings.coupons = this.settings.coupons || [];
                    if (this.settings.coupons.length === 0) {
                        this.settings.coupons = [
                            {
                                id: '1',
                                enabled: true,
                                name: 'Скидка 10%',
                                probability: 30,
                                code: 'WELCOME10',
                                generate_unique: false,
                                expiry_days: 30,
                                color: '#FF6B6B',
                                description: 'Скидка 10% на первый заказ'
                            },
                            {
                                id: '2',
                                enabled: true,
                                name: 'Скидка 20%',
                                probability: 20,
                                code: '',
                                generate_unique: true,
                                expiry_days: 14,
                                color: '#4ECDC4',
                                description: 'Скидка 20% на весь ассортимент'
                            },
                            {
                                id: '3',
                                enabled: true,
                                name: 'Попробуй еще раз',
                                probability: 50,
                                code: '',
                                generate_unique: false,
                                expiry_days: 0,
                                color: '#95A5A6',
                                description: 'К сожалению, вы ничего не выиграли'
                            }
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
                        { type: 'email', name: 'email', label: 'Email', required: true, placeholder: 'ivan@example.com' },
                        { type: 'tel', name: 'phone', label: 'Телефон', required: false, placeholder: '+7 (999) 123-45-67' }
                    ];

                    // Лимиты
                    this.settings.limits = this.settings.limits || {};
                    this.settings.limits.spins_per_user = this.settings.limits.spins_per_user || 1;
                    this.settings.limits.spins_per_day = this.settings.limits.spins_per_day || 1;
                    this.settings.limits.spins_total = this.settings.limits.spins_total || 0;
                    this.settings.limits.require_auth = this.settings.limits.require_auth || false;
                    this.settings.limits.collect_email = this.settings.limits.collect_email !== false;

                    // Поведение
                    this.settings.trigger_type = this.settings.trigger_type || 'click';
                    this.settings.delay = this.settings.delay || 0;
                    this.settings.scroll_percent = this.settings.scroll_percent || 50;
                    this.settings.frequency = this.settings.frequency || 'once_session';
                    this.settings.close_behavior = this.settings.close_behavior || 'hide_session';

                    // Сегменты колеса
                    this.updateWheelSegments();
                },

                initTabs() {
                    // Находим все ссылки вкладок
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

                    // Скрываем все панели
                    document.querySelectorAll('.tab-pane').forEach(pane => {
                        pane.classList.remove('active');
                        pane.style.display = 'none';
                    });

                    // Показываем выбранную панель
                    const activePane = document.getElementById(tabId);
                    if (activePane) {
                        activePane.classList.add('active');
                        activePane.style.display = 'block';
                    }

                    // Обновляем активный класс на кнопках
                    document.querySelectorAll('[data-tab]').forEach(tab => {
                        tab.classList.remove('active');
                        if (tab.getAttribute('data-tab') === tabId) {
                            tab.classList.add('active');
                        }
                    });
                },

                updateWheelSegments() {
                    // Фильтруем только включенные купоны
                    const coupons = (this.settings.coupons || []).filter(c => c.enabled !== false);
                    const totalProbability = coupons.reduce((sum, c) => sum + (parseInt(c.probability) || 0), 0);

                    if (totalProbability === 0) return;

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

                    this.settings.wheel.segments = segments;
                },

                updatePreview() {
                    const container = document.getElementById('preview-host');
                    if (!container) return;

                    const wheelSize = this.settings.wheel?.size || 400;
                    const segments = this.settings.wheel?.segments || [];
                    const btn = this.settings.button || {};

                    let wheelHtml = '';

                    if (segments.length > 0) {
                        wheelHtml = this.generateWheelPreview(segments, wheelSize);
                    } else {
                        wheelHtml = `<div style="font-size: 80px;">🎡</div>`;
                    }

                    container.innerHTML = `
                        <div style="display: flex; justify-content: center; align-items: center; height: 100%; flex-direction: column; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            ${wheelHtml}
                            <div style="margin-top: 20px; text-align: center; color: white;">
                                <button style="background: ${btn.bg_color || '#FF6B6B'}; color: ${btn.text_color || '#FFFFFF'}; border: none; padding: 12px 24px; border-radius: ${btn.border_radius || '50px'}; cursor: pointer; font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                    ${btn.icon || '🎡'} ${btn.text || 'Крутить колесо'}
                                </button>
                            </div>
                        </div>
                    `;
                },

                generateWheelPreview(segments, size) {
                    const center = size / 2;
                    const radius = size / 2 - 10;
                    let currentAngle = 0;
                    let svg = '';

                    segments.forEach((segment) => {
                        const angle = segment.angle || 0;
                        const start = currentAngle;
                        const end = currentAngle + angle;

                        const startRad = (start - 90) * Math.PI / 180;
                        const endRad = (end - 90) * Math.PI / 180;

                        const x1 = center + radius * Math.cos(startRad);
                        const y1 = center + radius * Math.sin(startRad);
                        const x2 = center + radius * Math.cos(endRad);
                        const y2 = center + radius * Math.sin(endRad);

                        const largeArc = angle > 180 ? 1 : 0;

                        svg += `<path d="M ${center} ${center} L ${x1} ${y1} A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2} Z"
                                    fill="${segment.color || '#FF6B6B'}"
                                    stroke="${this.settings.wheel?.border_color || '#FFFFFF'}"
                                    stroke-width="${this.settings.wheel?.border_width || 2}" />`;

                        const textAngle = start + angle / 2;
                        const textRad = (textAngle - 90) * Math.PI / 180;
                        const textX = center + (radius * 0.65) * Math.cos(textRad);
                        const textY = center + (radius * 0.65) * Math.sin(textRad);

                        let text = segment.name || '';
                        if (text.length > 8) text = text.substr(0, 6) + '..';

                        svg += `<text x="${textX}" y="${textY}" text-anchor="middle" dominant-baseline="middle"
                                    fill="${this.settings.wheel?.text_color || '#FFFFFF'}"
                                    font-size="${Math.max(10, Math.min(14, angle / 12))}"
                                    font-weight="bold">${text}</text>`;

                        currentAngle += angle;
                    });

                    svg += `<circle cx="${center}" cy="${center}" r="25" fill="${this.settings.wheel?.background_color || '#FFFFFF'}" stroke="#333" stroke-width="2"/>`;
                    svg += `<circle cx="${center}" cy="${center}" r="8" fill="${this.settings.wheel?.pointer_color || '#FF4444'}"/>`;

                    return `
                        <div style="position: relative;">
                            <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" style="border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); background: ${this.settings.wheel?.background_color || '#FFFFFF'};">
                                ${svg}
                            </svg>
                            <div style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%);">
                                <div style="width: 0; height: 0; border-left: 20px solid transparent; border-right: 20px solid transparent; border-top: 35px solid ${this.settings.wheel?.pointer_color || '#FF4444'}; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></div>
                            </div>
                        </div>
                    `;
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
                },

                removeCoupon(index) {
                    if (confirm('Удалить этот приз?')) {
                        this.settings.coupons.splice(index, 1);
                        this.updateWheelSegments();
                    }
                },

                addFormField() {
                    if (!this.settings.form.fields) this.settings.form.fields = [];
                    this.settings.form.fields.push({
                        type: 'text',
                        name: 'field_' + Date.now(),
                        label: 'Новое поле',
                        placeholder: 'Введите значение',
                        required: false,
                        default_value: ''
                    });
                },

                removeFormField(index) {
                    this.settings.form.fields.splice(index, 1);
                },

                async saveConfig(event) {
                    // Получаем кнопку из события
                    const btn = event?.target?.closest('button[type="submit"]') || event?.currentTarget;

                    if (!btn) {
                        console.error('Button not found');
                        return;
                    }

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

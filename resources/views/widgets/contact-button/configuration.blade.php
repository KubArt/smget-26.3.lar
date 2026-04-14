@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="contactButtonEditor({{ json_encode($config) }})" x-init="init">
        <div class="row">
            <!-- КОЛОНКА НАСТРОЕК -->
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка Мультикнопки: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <!-- Выбор скина -->
                        <div class="mb-4">
                            <label class="form-label text-primary fw-bold">Выберите макет</label>
                            <div class="row g-2">
                                <template x-for="skin in skins" :key="skin.slug">
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                :class="settings.template === skin.slug ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                @click="applyTemplate(skin.slug)">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-circle me-2 fs-xs" :class="settings.template === skin.slug ? 'text-primary' : 'text-muted'"></i>
                                                <span class="small fw-semibold" x-text="skin.name"></span>
                                            </div>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <input type="hidden" name="template" :value="settings.template">
                        </div>

                        <hr>

                        <!-- Основные параметры -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Основные параметры</label>

                            <div class="mb-3">
                                <label class="small text-muted">Позиция</label>
                                <select class="form-select" x-model="settings.position">
                                    <option value="right">Справа внизу</option>
                                    <option value="left">Слева внизу</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted">Текст подсказки</label>
                                <input type="text" class="form-control" placeholder="Например: Свяжитесь с нами" x-model="settings.main_tooltip">
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted">Задержка появления (сек)</label>
                                <input type="number" class="form-control" min="0" max="10" step="0.5" x-model="settings.delay">
                            </div>
                        </div>

                        <hr>

                        <!-- Дизайн -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Внешний вид</label>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="small text-muted">Размер кнопки</label>
                                    <select class="form-select" x-model="settings.design.size">
                                        <option value="small">Маленькая</option>
                                        <option value="medium">Средняя</option>
                                        <option value="large">Большая</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted">Эффект при наведении</label>
                                    <select class="form-select" x-model="settings.design.hover_effect">
                                        <option value="lift">Приподнимание</option>
                                        <option value="scale">Увеличение</option>
                                        <option value="glow">Свечение</option>
                                        <option value="rotate">Поворот</option>
                                        <option value="pulse">Пульсация</option>
                                        <option value="shake">Тряска</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted">Анимация кнопки</label>
                                <select class="form-select" x-model="settings.animation.type">
                                    <option value="none">Нет анимации</option>
                                    <option value="wave">Волна</option>
                                    <option value="pulse">Пульсация</option>
                                    <option value="shake">Тряска</option>
                                    <option value="ring">Звонок</option>
                                    <option value="bounce">Подпрыгивание</option>
                                    <option value="glow">Свечение</option>
                                    <option value="spin">Вращение иконки</option>
                                    <option value="heartbeat">Сердцебиение</option>
                                    <option value="flash">Вспышка</option>
                                    <option value="swing">Раскачивание</option>
                                    <option value="wobble">Шаткий эффект</option>
                                    <option value="fade">Исчезание</option>
                                    <option value="rotate">Медленный поворот</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="small text-muted">Прозрачность: <span x-text="settings.design.opacity"></span></label>
                                <input type="range" class="form-range" min="0.1" max="1" step="0.1" x-model="settings.design.opacity">
                            </div>
                        </div>

                        <hr>

                        <!-- Цвета -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Цветовая схема</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small text-muted">Цвет кнопки</label>
                                    <input type="color" class="form-control form-control-color" x-model="settings.design.main_color">
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted">Цвет иконки</label>
                                    <input type="color" class="form-control form-control-color" x-model="settings.design.icon_color">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Каналы связи -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                Каналы связи
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fa fa-plus me-1"></i> Добавить
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end shadow-lg">
                                        <template x-for="(info, type) in availableTypes" :key="type">
                                            <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" @click="addChannel(type)">
                                                <span x-html="getSvgIcon(type)" style="width: 20px; height: 20px; margin-right: 10px;"></span>
                                                <span x-text="info.name"></span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </label>

                            <div class="channels-list border rounded p-3 bg-light" style="max-height: 500px; overflow-y: auto;">
                                <template x-for="(channel, index) in settings.channels" :key="channel.id">
                                    <div class="channel-item bg-white border rounded p-3 mb-3 shadow-sm">
                                        <div class="row g-2 align-items-start">
                                            <div class="col-auto">
                                                <div class="drag-handle me-2" style="cursor: grab;">
                                                    <i class="fa fa-grip-vertical text-muted"></i>
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                     :style="`width: 40px; height: 40px; background: ${channel.bg_color}; cursor: pointer;`"
                                                     @click="$refs['chanColor'+index]?.click()">
                                                    <span x-html="getSvgIcon(channel.type)" style="width: 22px; height: 22px; color: ${channel.icon_color || '#fff'}"></span>
                                                </div>
                                                <input type="color" class="d-none" :x-ref="'chanColor'+index" x-model="channel.bg_color">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control form-control-sm fw-bold mb-2" placeholder="Название" x-model="channel.label">
                                                <input type="text" class="form-control form-control-sm" :placeholder="getActionPlaceholder(channel.type)" x-model="channel.action_value">
                                            </div>
                                            <div class="col-auto">
                                                <div class="mb-2">
                                                    <input type="color" class="form-control form-control-color form-control-sm" style="width: 40px; height: 40px; padding: 0;" x-model="channel.icon_color" title="Цвет иконки">
                                                </div>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" x-model="channel.is_active">
                                                    <label class="small">Активен</label>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-link text-danger w-100" @click="removeChannel(index)">
                                                    <i class="fa fa-trash-can"></i> Удалить
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="settings.channels.length === 0" class="text-center text-muted py-4">
                                    <i class="fa fa-comments fa-2x mb-2 opacity-25"></i>
                                    <p class="small mb-0">Нет добавленных каналов<br>Нажмите "Добавить" чтобы начать</p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig">
                            <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                        </button>
                    </div>
                </div>
            </div>

            <!-- ПРЕДПРОСМОТР -->
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }">
                <div class="block block-rounded sticky-top" style="top: 20px;">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Предпросмотр</h3>
                        <div class="block-options">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'desktop' ? 'active' : ''" @click="previewMode = 'desktop'">
                                    <i class="fa fa-desktop me-1"></i> ПК
                                </button>
                                <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'tablet' ? 'active' : ''" @click="previewMode = 'tablet'">
                                    <i class="fa fa-tablet-alt me-1"></i> Планшет
                                </button>
                                <button type="button" class="btn btn-alt-secondary" :class="previewMode === 'mobile' ? 'active' : ''" @click="previewMode = 'mobile'">
                                    <i class="fa fa-mobile-alt me-1"></i> Мобильный
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="block-content p-3 bg-body-dark">
                        <div class="browser-mockup" :class="previewMode" id="browser-mockup">
                            <div class="browser-header">
                                <div class="d-flex gap-1">
                                    <span class="dot red"></span>
                                    <span class="dot yellow"></span>
                                    <span class="dot green"></span>
                                </div>
                                <div class="address-bar">
                                    <i class="fa fa-lock me-1 text-success"></i> your-website.com
                                </div>
                                <div class="browser-controls">
                                    <span class="badge bg-secondary" x-text="previewMode === 'desktop' ? '1920px' : (previewMode === 'tablet' ? '768px' : '375px')"></span>
                                </div>
                            </div>
                            <div class="browser-viewport" id="browser-viewport">
                                <div id="preview-host"></div>
                                <div class="site-placeholder">
                                    <div class="hero-rect"></div>
                                    <div class="p-3">
                                        <div class="row g-3">
                                            <div class="col-4"><div class="line"></div></div>
                                            <div class="col-8"><div class="line w-75"></div></div>
                                            <div class="col-12"><div class="line"></div></div>
                                            <div class="col-12"><div class="line w-50"></div></div>
                                            <div class="col-6"><div class="line"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted" x-text="previewMode === 'desktop' ? '1920px × 500px' : (previewMode === 'tablet' ? '768px × 500px' : '375px × 500px')"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .browser-mockup {
                border: 1px solid #d1d1d1;
                border-radius: 8px;
                background: #fff;
                overflow: hidden;
                height: 800px;
                display: flex;
                flex-direction: column;
                margin: 0 auto;
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            }
            .browser-mockup.desktop { width: 100%; max-width: 100%; }
            .browser-mockup.tablet { width: 768px; }
            .browser-mockup.mobile { width: 375px; }
            .browser-header {
                background: #f1f1f1;
                padding: 8px 12px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid #e1e1e1;
                flex-shrink: 0;
            }
            .browser-header .dot {
                height: 10px;
                width: 10px;
                border-radius: 50%;
                margin-right: 6px;
            }
            .dot.red { background: #ff5f56; }
            .dot.yellow { background: #ffbd2e; }
            .dot.green { background: #27c93f; }
            .browser-header .address-bar {
                background: #fff;
                flex: 1;
                max-width: 400px;
                margin: 0 12px;
                border-radius: 4px;
                font-size: 11px;
                padding: 3px 10px;
                color: #666;
                text-align: center;
                border: 1px solid #e1e1e1;
            }
            .browser-controls { min-width: 60px; text-align: right; }
            .browser-viewport {
                position: relative;
                flex-grow: 1;
                background: #fff;
                overflow-y: auto;
                overflow-x: hidden;
            }
            .site-placeholder { padding: 0; pointer-events: none; }
            .hero-rect {
                height: 160px;
                background: linear-gradient(135deg, #f0f2f5 0%, #e9ecef 100%);
                margin-bottom: 10px;
                width: 100%;
            }
            .line {
                height: 12px;
                background: #f0f2f5;
                border-radius: 6px;
                margin-bottom: 15px;
                width: 100%;
                background: linear-gradient(90deg, #f0f2f5 0%, #e9ecef 50%, #f0f2f5 100%);
                background-size: 200% auto;
                animation: shimmer 1.5s infinite;
            }
            @keyframes shimmer {
                0% { background-position: -200% 0; }
                100% { background-position: 200% 0; }
            }
            #preview-host {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 100;
                pointer-events: none;
            }
            @media (max-width: 768px) {
                .browser-mockup.tablet,
                .browser-mockup.mobile {
                    width: calc(100% - 32px);
                }
            }
            .channel-item {
                border-left: 3px solid #3b82f6;
                transition: all 0.2s ease;
            }
            .channel-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        </style>

        @push('js')
            <script>
                function contactButtonEditor(config) {
                    return {
                        // Данные
                        slug: config.slug,
                        settings: config.settings,
                        skins: config.skins,
                        previewMode: 'desktop',

                        // Внутреннее состояние
                        rawTemplate: '',
                        rawCss: '',
                        shadowRoot: null,
                        widgetRoot: null,

                        // Типы каналов с SVG иконками
                        availableTypes: {
                            /*
                            whatsapp: { name: 'WhatsApp', color: '#25D366', placeholder: '79001234567', svg: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.032 2.001c-5.514 0-9.996 4.48-9.996 9.991 0 1.76.457 3.484 1.328 5.003L2 22.001l5.197-1.345c1.47.87 3.138 1.33 4.835 1.33 5.513 0 9.996-4.48 9.996-9.991 0-5.511-4.483-9.991-9.996-9.991zm0 18.406c-1.5 0-2.97-.404-4.252-1.169l-.305-.18-3.085.8.824-3.007-.198-.316c-.842-1.33-1.287-2.86-1.287-4.44 0-4.656 3.795-8.447 8.458-8.447 4.663 0 8.458 3.79 8.458 8.447 0 4.656-3.795 8.447-8.458 8.447z"/><path d="M16.94 14.07c-.262-.13-1.55-.764-1.79-.851-.24-.087-.414-.13-.59.13-.175.26-.68.851-.834 1.025-.154.175-.307.197-.57.066-.262-.13-1.106-.408-2.107-1.3-.78-.695-1.306-1.553-1.458-1.816-.153-.263-.016-.405.115-.536.118-.118.262-.306.393-.46.13-.153.175-.26.262-.434.087-.173.044-.325-.022-.456-.066-.13-.59-1.42-.808-1.945-.212-.51-.427-.44-.59-.45-.153-.01-.328-.01-.503-.01-.175 0-.46.066-.7.328-.24.262-.918.897-.918 2.19 0 1.292.942 2.54 1.074 2.717.13.175 1.854 2.83 4.49 3.97.627.27 1.117.432 1.5.553.63.2 1.204.172 1.657.104.505-.076 1.55-.633 1.768-1.245.218-.612.218-1.136.152-1.245-.065-.11-.24-.175-.502-.306z"/></svg>' },
                            telegram: { name: 'Telegram', color: '#0088cc', placeholder: 'username', svg: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.07-.06-.18-.04-.26-.02-.11.03-1.8 1.14-5.09 3.36-.48.33-.92.49-1.31.48-.43-.01-1.26-.24-1.88-.44-.75-.24-1.35-.37-1.3-.78.03-.21.32-.43.88-.66 2.22-.97 3.96-1.61 5.22-1.92 2.48-.61 3-.73 3.34-.73.07 0 .19.02.28.09.12.08.16.2.17.32.01.11-.04.27-.07.38z"/></svg>' },
                            phone: { name: 'Телефон', color: '#34b7f1', placeholder: '+79001234567', svg: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>' },
                            email: { name: 'Email', color: '#ea4335', placeholder: 'mail@example.com', svg: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>' },
                            vk: { name: 'VK', color: '#0077ff', placeholder: 'club123', svg: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M21.5 7.3c.1-.3 0-.5-.4-.5h-2.7c-.4 0-.6.2-.7.5 0 0-.8 2-2 3.3-.4.4-.5.5-.7.5-.1 0-.2-.1-.2-.5V7.3c0-.4-.1-.5-.5-.5h-4.2c-.3 0-.4.2-.4.4 0 .8.6.9.6 1.5v2.3c0 .5-.1.6-.3.6-.5 0-1.7-1.8-2.4-3.9-.1-.4-.3-.6-.7-.6H4.9c-.5 0-.6.2-.6.5 0 .8.6 4.7 2.7 7.2 1.4 1.7 3.4 2.6 5.3 2.6 1.1 0 1.2-.3 1.2-.8v-2c0-.4.1-.5.4-.5.3 0 .8.2 1.6 1.1.9 1 1.3 1.5 1.9 1.5h2.7c.5 0 .7-.3.6-.7-.2-.5-1-1.4-2-2.4-.4-.5-1-1-1.2-1.3-.2-.3-.1-.5.1-.8 0 0 1.8-2.5 2-3.3z"/></svg>' },
                            custom: { name: 'Своя ссылка', color: '#6c757d', placeholder: 'https://...', svg: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1 0 1.71-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>' }
                            //*/
                            whatsapp: {
                                name: 'WhatsApp',
                                icon: 'fab fa-whatsapp',
                                color: '#25D366',
                                placeholder: '79001234567',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.032 2.001c-5.514 0-9.996 4.48-9.996 9.991 0 1.76.457 3.484 1.328 5.003L2 22.001l5.197-1.345c1.47.87 3.138 1.33 4.835 1.33 5.513 0 9.996-4.48 9.996-9.991 0-5.511-4.483-9.991-9.996-9.991zm0 18.406c-1.5 0-2.97-.404-4.252-1.169l-.305-.18-3.085.8.824-3.007-.198-.316c-.842-1.33-1.287-2.86-1.287-4.44 0-4.656 3.795-8.447 8.458-8.447 4.663 0 8.458 3.79 8.458 8.447 0 4.656-3.795 8.447-8.458 8.447z"/><path d="M16.94 14.07c-.262-.13-1.55-.764-1.79-.851-.24-.087-.414-.13-.59.13-.175.26-.68.851-.834 1.025-.154.175-.307.197-.57.066-.262-.13-1.106-.408-2.107-1.3-.78-.695-1.306-1.553-1.458-1.816-.153-.263-.016-.405.115-.536.118-.118.262-.306.393-.46.13-.153.175-.26.262-.434.087-.173.044-.325-.022-.456-.066-.13-.59-1.42-.808-1.945-.212-.51-.427-.44-.59-.45-.153-.01-.328-.01-.503-.01-.175 0-.46.066-.7.328-.24.262-.918.897-.918 2.19 0 1.292.942 2.54 1.074 2.717.13.175 1.854 2.83 4.49 3.97.627.27 1.117.432 1.5.553.63.2 1.204.172 1.657.104.505-.076 1.55-.633 1.768-1.245.218-.612.218-1.136.152-1.245-.065-.11-.24-.175-.502-.306z"/></svg>`
                            },
                            telegram: {
                                name: 'Telegram',
                                icon: 'fab fa-telegram-plane',
                                color: '#0088cc',
                                placeholder: 'username',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.07-.06-.18-.04-.26-.02-.11.03-1.8 1.14-5.09 3.36-.48.33-.92.49-1.31.48-.43-.01-1.26-.24-1.88-.44-.75-.24-1.35-.37-1.3-.78.03-.21.32-.43.88-.66 2.22-.97 3.96-1.61 5.22-1.92 2.48-.61 3-.73 3.34-.73.07 0 .19.02.28.09.12.08.16.2.17.32.01.11-.04.27-.07.38z"/></svg>`
                            },
                            phone: {
                                name: 'Телефон',
                                icon: 'fas fa-phone',
                                color: '#34b7f1',
                                placeholder: '+79001234567',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>`
                            },
                            email: {
                                name: 'Email',
                                icon: 'fas fa-envelope',
                                color: '#ea4335',
                                placeholder: 'mail@example.com',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>`
                            },
                            vk: {
                                name: 'VK',
                                icon: 'fab fa-vk',
                                color: '#0077ff',
                                placeholder: 'club123',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M21.5 7.3c.1-.3 0-.5-.4-.5h-2.7c-.4 0-.6.2-.7.5 0 0-.8 2-2 3.3-.4.4-.5.5-.7.5-.1 0-.2-.1-.2-.5V7.3c0-.4-.1-.5-.5-.5h-4.2c-.3 0-.4.2-.4.4 0 .8.6.9.6 1.5v2.3c0 .5-.1.6-.3.6-.5 0-1.7-1.8-2.4-3.9-.1-.4-.3-.6-.7-.6H4.9c-.5 0-.6.2-.6.5 0 .8.6 4.7 2.7 7.2 1.4 1.7 3.4 2.6 5.3 2.6 1.1 0 1.2-.3 1.2-.8v-2c0-.4.1-.5.4-.5.3 0 .8.2 1.6 1.1.9 1 1.3 1.5 1.9 1.5h2.7c.5 0 .7-.3.6-.7-.2-.5-1-1.4-2-2.4-.4-.5-1-1-1.2-1.3-.2-.3-.1-.5.1-.8 0 0 1.8-2.5 2-3.3z"/></svg>`
                            },
                            max: {
                                name: 'Max',
                                icon: 'fas fa-tv',
                                color: '#ff6600',
                                placeholder: 'https://max.kg/',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6H4c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H4V8h16v8zM8 4h8v2H8z"/><rect x="6" y="12" width="4" height="2" rx="1"/><rect x="14" y="12" width="4" height="2" rx="1"/></svg>`
                            },
                            odnoklassniki: {
                                name: 'Одноклассники',
                                icon: 'fab fa-odnoklassniki',
                                color: '#ed6b0a',
                                placeholder: 'https://ok.ru/group/...',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-3.3 0-6 2.7-6 6s2.7 6 6 6 6-2.7 6-6-2.7-6-6-6zm0 9c-1.7 0-3-1.3-3-3s1.3-3 3-3 3 1.3 3 3-1.3 3-3 3z"/><path d="M17.5 14.5c-1.5 1.2-3.5 1.8-5.5 1.8s-4-.6-5.5-1.8c-.4-.3-1-.3-1.4.1-.3.4-.3 1 .1 1.4 1.7 1.4 4 2.2 6.3 2.3l-1.9 1.9c-.4.4-.4 1 0 1.4.2.2.5.3.7.3s.5-.1.7-.3l2.5-2.5 2.5 2.5c.2.2.5.3.7.3s.5-.1.7-.3c.4-.4.4-1 0-1.4l-1.9-1.9c2.3-.1 4.6-.9 6.3-2.3.4-.3.4-1 .1-1.4-.4-.4-1-.4-1.4-.1z"/></svg>`
                            },
                            viber: {
                                name: 'Viber',
                                icon: 'fab fa-viber',
                                color: '#7360f2',
                                placeholder: 'https://chats.viber.com/...',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm.5 15.5h-1v-1h1v1zm0-2.5h-1v-5h1v5zm2 2.5h-1v-1h1v1zm0-2.5h-1v-5h1v5zm2 2.5h-1v-1h1v1zm0-2.5h-1v-5h1v5z"/><circle cx="12" cy="12" r="2"/></svg>`
                            },
                            callback: {
                                name: 'Обратный звонок',
                                icon: 'fas fa-phone-alt',
                                color: '#28a745',
                                placeholder: 'https://example.com/callback',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M20 15.5c-1.2 0-2.4-.2-3.6-.6-.3-.1-.7 0-1 .3l-2.2 2.2c-2.8-1.4-5.1-3.8-6.6-6.6l2.2-2.2c.3-.3.4-.7.3-1-.3-1.1-.5-2.3-.5-3.5 0-.6-.4-1-1-1H4c-.6 0-1 .4-1 1 0 9.4 7.6 17 17 17 .6 0 1-.4 1-1v-3.5c0-.6-.4-1-1-1zM19 12h2c0-5-4-9-9-9v2c3.9 0 7 3.1 7 7z"/><path d="M15 12h2c0-2.8-2.2-5-5-5v2c1.7 0 3 1.3 3 3z"/></svg>`
                            },
                            youtube: {
                                name: 'YouTube',
                                icon: 'fab fa-youtube',
                                color: '#ff0000',
                                placeholder: 'https://youtube.com/@...',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2c-.3-1-1-1.8-2-2-1.8-.5-9.5-.5-9.5-.5s-7.7 0-9.5.5c-1 .2-1.7 1-2 2-.5 1.8-.5 5.8-.5 5.8s0 4 .5 5.8c.3 1 1 1.8 2 2 1.8.5 9.5.5 9.5.5s7.7 0 9.5-.5c1-.2 1.7-1 2-2 .5-1.8.5-5.8.5-5.8s0-4-.5-5.8zM9.5 15.5v-7l6.5 3.5-6.5 3.5z"/></svg>`
                            },
                            custom: {
                                name: 'Своя ссылка',
                                icon: 'fas fa-link',
                                color: '#6c757d',
                                placeholder: 'https://...',
                                svg: `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1 0 1.71-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>`
                            }

                        },

                        async init() {
                            // Инициализация настроек по умолчанию
                            if (!this.settings.channels) this.settings.channels = [];
                            if (!this.settings.design) {
                                this.settings.design = {
                                    main_color: '#3b82f6',
                                    icon_color: '#ffffff',
                                    size: 'medium',
                                    opacity: 1,
                                    hover_effect: 'lift'
                                };
                            }
                            if (!this.settings.animation) this.settings.animation = { type: 'wave', enabled: true };
                            if (!this.settings.position) this.settings.position = 'right';
                            if (!this.settings.main_tooltip) this.settings.main_tooltip = 'Свяжитесь с нами';
                            if (!this.settings.delay) this.settings.delay = 1;
                            if (!this.settings.template) this.settings.template = Object.keys(this.skins)[0] || 'default';

                            await this.loadSkin(this.settings.template);
                            this.$watch('settings', () => this.updatePreview(), { deep: true });
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

                            this.shadowRoot = container.shadowRoot || container.attachShadow({ mode: 'open' });

                            let css = this.rawCss;
                            css = css.replace(/position:\s*fixed/g, 'position: absolute');
                            css = css.replace(/position:fixed/g, 'position: absolute');
                            css = css.replace(/100vh/g, '100%');
                            css = css.replace(/100vw/g, '100%');

                            this.shadowRoot.innerHTML = `
                        <style>:host { display: block; position: absolute; top: 0; left: 0; right: 0; bottom: 0; } ${css}</style>
                        <div id="widget-root"></div>
                    `;
                            this.widgetRoot = this.shadowRoot.getElementById('widget-root');
                        },

                        updatePreview() {
                            if (!this.widgetRoot || !this.rawTemplate) return;

                            // Генерация HTML каналов с SVG
                            const channelsHtml = this.settings.channels
                                .filter(c => c.is_active !== false && c.action_value)
                                .map(c => {
                                    let url = '#';
                                    const value = c.action_value || '';
                                    if (c.type === 'whatsapp') url = `https://wa.me/${value.replace(/\D/g, '')}`;
                                    else if (c.type === 'telegram') url = `https://t.me/${value.replace('@', '')}`;
                                    else if (c.type === 'phone') url = `tel:${value.replace(/\D/g, '')}`;
                                    else if (c.type === 'email') url = `mailto:${value}`;
                                    else if (value) url = value;

                                    const svg = this.availableTypes[c.type]?.svg || this.availableTypes.custom.svg;

                                    return `
                                <a href="${url}" class="sp-channel-item" target="_blank">
                                    <div class="sp-channel-icon" style="background: ${c.bg_color}; color: ${c.icon_color || '#fff'}">
                                        ${svg}
                                    </div>
                                    <span class="sp-channel-label">${this.escapeHtml(c.label || c.type)}</span>
                                </a>
                            `;
                                }).join('');

                            let html = this.rawTemplate
                                .replace(/\{position\}/g, this.settings.position)
                                .replace(/\{channels_html\}/g, channelsHtml)
                                .replace(/\{main_tooltip\}/g, this.escapeHtml(this.settings.main_tooltip))
                                .replace(/\{widget_id\}/g, 'preview');

                            this.widgetRoot.innerHTML = html;

                            const widget = this.widgetRoot.firstElementChild;
                            if (!widget) return;

                            const rgb = this.hexToRgb(this.settings.design.main_color);
                            widget.style.setProperty('--main-color', this.settings.design.main_color);
                            widget.style.setProperty('--main-color-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
                            widget.style.setProperty('--icon-color', this.settings.design.icon_color);
                            widget.style.setProperty('--scale-factor', this.getScaleFactor(this.settings.design.size));
                            widget.style.setProperty('--btn-opacity', this.settings.design.opacity);

                            widget.classList.add(`sp-size-${this.settings.design.size}`);
                            if (this.settings.design.hover_effect !== 'none') widget.classList.add(`sp-hover-${this.settings.design.hover_effect}`);
                            if (this.settings.animation.type !== 'none') widget.classList.add(`sp-animation-${this.settings.animation.type}`);

                            this.attachEvents(widget);
                        },

                        attachEvents(widget) {
                            const toggle = widget.querySelector('[data-sp-toggle]');
                            const close = widget.querySelector('[data-sp-close]');
                            const overlay = widget.querySelector('[data-sp-overlay]');

                            if (toggle) {
                                const clone = toggle.cloneNode(true);
                                toggle.parentNode.replaceChild(clone, toggle);
                                clone.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    widget.classList.toggle('sp-active');
                                });
                            }
                            if (close) {
                                const clone = close.cloneNode(true);
                                close.parentNode.replaceChild(clone, close);
                                clone.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    widget.classList.remove('sp-active');
                                });
                            }
                            if (overlay) {
                                const clone = overlay.cloneNode(true);
                                overlay.parentNode.replaceChild(clone, overlay);
                                clone.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    widget.classList.remove('sp-active');
                                });
                            }

                            setTimeout(() => {
                                widget.classList.add('sp-active');
                                setTimeout(() => widget.classList.remove('sp-active'), 3000);
                            }, 500);
                        },

                        async applyTemplate(skinId) {
                            if (this.settings.template === skinId) return;
                            this.settings.template = skinId;
                            await this.loadSkin(skinId);
                        },

                        addChannel(type) {
                            const proto = this.availableTypes[type];
                            if (!proto) return;
                            if (!Array.isArray(this.settings.channels)) this.settings.channels = [];
                            this.settings.channels.push({
                                id: Date.now(),
                                type: type,
                                label: proto.name,
                                action_value: '',
                                bg_color: proto.color,
                                icon_color: '#ffffff',
                                is_active: true
                            });
                        },

                        removeChannel(index) {
                            this.settings.channels.splice(index, 1);
                        },

                        getSvgIcon(type) {
                            return this.availableTypes[type]?.svg || this.availableTypes.custom.svg;
                        },

                        getActionPlaceholder(type) {
                            return this.availableTypes[type]?.placeholder || 'Введите значение';
                        },

                        getScaleFactor(size) {
                            const sizes = { small: '0.8', medium: '1', large: '1.2' };
                            return sizes[size] || '1';
                        },

                        hexToRgb(hex) {
                            hex = hex.replace(/^#/, '');
                            if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
                            const int = parseInt(hex, 16);
                            return { r: (int >> 16) & 255, g: (int >> 8) & 255, b: int & 255 };
                        },

                        escapeHtml(str) {
                            if (!str) return '';
                            const div = document.createElement('div');
                            div.textContent = str;
                            return div.innerHTML;
                        },

                        saveConfig() {
                            const btn = event.currentTarget;
                            const original = btn.innerHTML;
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Сохранение...';

                            axios.post(window.location.href, { settings: this.settings })
                                .then(response => {
                                    if (response.data.status === 'success') {
                                        if (typeof showNotification === 'function') {
                                            showNotification(response.data.message, 'success');
                                        } else {
                                            alert(response.data.message);
                                        }
                                    }
                                })
                                .catch(error => {
                                    const msg = error.response?.data?.message || 'Ошибка при сохранении';
                                    if (typeof showNotification === 'function') {
                                        showNotification(msg, 'danger');
                                    } else {
                                        alert(msg);
                                    }
                                })
                                .finally(() => {
                                    btn.disabled = false;
                                    btn.innerHTML = original;
                                });
                        }
                    };
                }
            </script>
    @endpush
@endsection

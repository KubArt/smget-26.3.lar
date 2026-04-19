@php
    $behavior = $widget->behavior ?? [];
    // Используем данные уже нормализованные в контроллере
    // trigger_type всегда будет корректным
    // delay, scroll_percent, click_selector - только если нужны
@endphp

<div class="row items-push">
    <div class="col-xl-8">
        <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted mb-3">Триггер показа</label>
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach([
                    'immediate' => ['icon' => 'fa-bolt', 'label' => 'При загрузке', 'tip' => 'Виджет появится сразу после загрузки страницы.', 'color' => '#10b981'],
                    'delay' => ['icon' => 'fa-clock', 'label' => 'Таймер', 'tip' => 'Показ через определенное количество секунд.', 'color' => '#3b82f6'],
                    'scroll' => ['icon' => 'fa-arrows-up-down', 'label' => 'Скролл', 'tip' => 'Сработает, когда пользователь пролистает страницу.', 'color' => '#8b5cf6'],
                    'exit' => ['icon' => 'fa-arrow-right-from-bracket', 'label' => 'Уход', 'tip' => 'Появится, когда курсор уйдет к вкладкам браузера.', 'color' => '#ef4444'],
                    'click' => ['icon' => 'fa-mouse-pointer', 'label' => 'Клик', 'tip' => 'Запуск при нажатии на конкретный элемент сайта.', 'color' => '#f59e0b']
                ] as $val => $data)
                    <div style="flex: 1; min-width: 100px;">
                        <input type="radio" class="btn-check" name="behavior[trigger_type]" id="trigger_{{ $val }}"
                               value="{{ $val }}" {{ ($behavior['trigger_type'] ?? 'immediate') == $val ? 'checked' : '' }}>
                        <label class="btn btn-outline-secondary w-100 py-2 d-flex flex-column align-items-center justify-content-center border-2 transition-all"
                               for="trigger_{{ $val }}"
                               style="--bs-btn-active-bg: {{ $data['color'] }}; --bs-btn-active-border-color: {{ $data['color'] }}; transition: all 0.2s;">
                            <i class="fa {{ $data['icon'] }} mb-1 fs-5"></i>
                            <span style="font-size: 0.7rem;" class="fw-semibold">{{ $data['label'] }}</span>
                        </label>
                        <div class="d-none trigger-tip-source" data-for="trigger_{{ $val }}">{{ $data['tip'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="p-3 rounded-3 bg-light border border-dashed" id="triggerSettingsPanel">
                <div id="trigger_hint" class="fs-sm text-muted mb-3 pb-2 border-bottom">
                    <i class="fa fa-lightbulb me-1"></i> Выберите триггер для настройки
                </div>

                <div id="delay_block" class="trigger-settings animated fadeIn" style="display: none;">
                    <div class="row align-items-center g-2">
                        <div class="col-auto">
                            <label class="form-label mb-0 small fw-bold">
                                <i class="fa fa-clock me-1 text-primary"></i>Задержка:
                            </label>
                        </div>
                        <div class="col-4">
                            <div class="input-group input-group-sm">
                                <input type="number" name="behavior[delay]" class="form-control text-center" value="{{ $behavior['delay'] ?? 3 }}" min="0" max="30" step="0.5">
                                <span class="input-group-text">сек.</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted">от 0 до 30 секунд</small>
                        </div>
                    </div>
                </div>

                <div id="scroll_block" class="trigger-settings animated fadeIn" style="display: none;">
                    <div class="mb-2">
                        <label class="form-label small fw-bold d-flex justify-content-between">
                            <span><i class="fa fa-arrows-up-down me-1 text-primary"></i>Процент прокрутки:</span>
                            <span class="badge bg-primary scroll-value">{{ $behavior['scroll_percent'] ?? 50 }}%</span>
                        </label>
                        <input type="range" name="behavior[scroll_percent]" class="form-range scroll-range" min="0" max="100" value="{{ $behavior['scroll_percent'] ?? 50 }}">
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">начало</small>
                            <small class="text-muted">середина</small>
                            <small class="text-muted">конец</small>
                        </div>
                    </div>
                </div>

                <div id="click_block" class="trigger-settings animated fadeIn" style="display: none;">
                    <label class="form-label small fw-bold">
                        <i class="fa fa-code me-1 text-primary"></i>CSS селектор:
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"># .</span>
                        <input type="text" name="behavior[click_selector]" class="form-control" placeholder=".btn-open, #popup-trigger" value="{{ $behavior['click_selector'] ?? '' }}">
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="fa fa-info-circle me-1"></i>
                        Примеры: <code>.my-button</code>, <code>#open-popup</code>, <code>button[data-action="open"]</code>
                    </small>
                </div>
            </div>
        </div>

        <div class="row pt-3 border-top g-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted text-uppercase">
                    <i class="fa fa-chart-line me-1"></i>Частота показа
                </label>
                <select class="form-select form-select-sm" name="behavior[frequency]">
                    <option value="always" {{ ($behavior['frequency'] ?? 'always') == 'always' ? 'selected' : '' }}>♾️ Без ограничений</option>
                    <option value="once_session" {{ ($behavior['frequency'] ?? '') == 'once_session' ? 'selected' : '' }}>🔄 Раз в сессию</option>
                    <option value="once_day" {{ ($behavior['frequency'] ?? '') == 'once_day' ? 'selected' : '' }}>📅 Раз в день</option>
                    <option value="once_week" {{ ($behavior['frequency'] ?? '') == 'once_week' ? 'selected' : '' }}>📆 Раз в неделю</option>
                    <option value="once_month" {{ ($behavior['frequency'] ?? '') == 'once_month' ? 'selected' : '' }}>📆 Раз в месяц</option>
                    <option value="once_forever" {{ ($behavior['frequency'] ?? '') == 'once_forever' ? 'selected' : '' }}>🔒 Один раз навсегда</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold text-muted text-uppercase">
                    <i class="fa fa-hourglass-half me-1"></i>Авто-закрытие
                </label>
                <div class="input-group input-group-sm">
                    <input type="number" name="behavior[auto_close]" class="form-control text-center" value="{{ $behavior['auto_close'] ?? 0 }}" min="0" max="60" step="1">
                    <span class="input-group-text">сек.</span>
                </div>
                <small class="text-muted">0 = не закрывать автоматически</small>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="alert alert-info border-0 shadow-sm p-3">
            <h6 class="alert-heading fw-bold mb-2">
                <i class="fa fa-info-circle me-1"></i> Как это работает?
            </h6>
            <ul class="list-unstyled fs-xs mb-0">
                <li class="mb-2"><i class="fa fa-bolt text-success me-1"></i> <strong>Мгновенно:</strong> Для важных уведомлений</li>
                <li class="mb-2"><i class="fa fa-clock text-primary me-1"></i> <strong>Таймер:</strong> Даем время ознакомиться</li>
                <li class="mb-2"><i class="fa fa-arrows-up-down text-purple me-1"></i> <strong>Скролл:</strong> Пользователь вовлечен в контент</li>
                <li class="mb-2"><i class="fa fa-arrow-right-from-bracket text-danger me-1"></i> <strong>Уход:</strong> Последний шанс удержать</li>
                <li><i class="fa fa-mouse-pointer text-warning me-1"></i> <strong>Клик:</strong> Интерактивный запуск</li>
            </ul>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const triggerInputs = document.querySelectorAll('input[name="behavior[trigger_type]"]');
            const hintDisplay = document.getElementById('trigger_hint');

            const settingsBlocks = {
                delay: document.getElementById('delay_block'),
                scroll: document.getElementById('scroll_block'),
                click: document.getElementById('click_block')
            };

            function updateBehaviorUI() {
                const selectedInput = document.querySelector('input[name="behavior[trigger_type]"]:checked');
                if (!selectedInput) return;

                const value = selectedInput.value;

                const tipSource = document.querySelector(`.trigger-tip-source[data-for="${selectedInput.id}"]`);
                if (hintDisplay && tipSource) {
                    hintDisplay.innerHTML = `<i class="fa fa-lightbulb me-1 text-warning"></i> ${tipSource.textContent}`;
                }

                Object.keys(settingsBlocks).forEach(key => {
                    const block = settingsBlocks[key];
                    if (block) {
                        block.style.display = (key === value) ? 'block' : 'none';
                    }
                });
            }

            const scrollRange = document.querySelector('.scroll-range');
            const scrollValueSpan = document.querySelector('.scroll-value');
            if (scrollRange && scrollValueSpan) {
                scrollRange.addEventListener('input', (e) => {
                    scrollValueSpan.textContent = e.target.value + '%';
                });
            }

            triggerInputs.forEach(input => input.addEventListener('change', updateBehaviorUI));
            updateBehaviorUI();
        });
    </script>

    <style>
        .animated {
            transition: all 0.2s ease-in-out;
        }
        .fadeIn {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .btn-outline-secondary {
            transition: all 0.2s;
        }
        .btn-outline-secondary:hover {
            transform: translateY(-2px);
        }
        .border-dashed {
            border-style: dashed !important;
        }
        .text-purple {
            color: #8b5cf6;
        }
    </style>
@endpush

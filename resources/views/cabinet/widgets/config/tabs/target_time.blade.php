<div class="row items-push">
    <div class="col-xl-8">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted mb-0">
                    <i class="fa fa-clock me-1"></i> Правила показа по времени
                </label>
                <button type="button" class="btn btn-sm btn-primary" id="addTimeRuleBtn">
                    <i class="fa fa-plus me-1"></i> Добавить правило (ИЛИ)
                </button>
            </div>

            <div id="timeRulesContainer" class="space-y-3">
                @php $timeRules = $widget->target_time ?? []; @endphp

                @if(empty($timeRules))
                    <div class="alert alert-light text-center py-4 border border-dashed" id="emptyRulesPlaceholder">
                        <i class="fa fa-clock fa-2x text-muted mb-2 d-block"></i>
                        <p class="mb-0 small text-muted">Нет правил временного таргетинга</p>
                        <p class="mb-0 small text-muted">Нажмите "Добавить правило" чтобы начать</p>
                    </div>
                @else
                    @foreach($timeRules as $index => $rule)
                        <div class="time-rule-card border rounded-3 p-3 bg-white shadow-sm" data-rule-index="{{ $index }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="fw-semibold small text-uppercase text-muted">
                                    <i class="fa fa-merge me-1"></i> Правило #{{ $loop->iteration }}
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger remove-rule-btn">
                                    <i class="fa fa-trash-can"></i> Удалить
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="small fw-bold d-block mb-2">Тип правила</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check rule-type" name="target_time[{{ $index }}][type]"
                                               id="type_days_{{ $index }}" value="days"
                                            {{ ($rule['type'] ?? 'days') == 'days' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="type_days_{{ $index }}">
                                            <i class="fa fa-calendar-week me-1"></i> Дни недели
                                        </label>

                                        <input type="radio" class="btn-check rule-type" name="target_time[{{ $index }}][type]"
                                               id="type_range_{{ $index }}" value="date_range"
                                            {{ ($rule['type'] ?? '') == 'date_range' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary" for="type_range_{{ $index }}">
                                            <i class="fa fa-calendar-alt me-1"></i> Диапазон дат
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 days-block" style="display: {{ ($rule['type'] ?? 'days') == 'days' ? 'block' : 'none' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="small fw-bold">Дни недели</label>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-sm btn-outline-secondary select-all-days">Выбрать все</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary clear-all-days">Очистить</button>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @php
                                            $weekDays = [
                                                'mon' => 'Пн', 'tue' => 'Вт', 'wed' => 'Ср',
                                                'thu' => 'Чт', 'fri' => 'Пт', 'sat' => 'Сб', 'sun' => 'Вс'
                                            ];
                                        @endphp
                                        @foreach($weekDays as $key => $label)
                                            <div class="form-check">
                                                <input class="form-check-input day-checkbox" type="checkbox"
                                                       name="target_time[{{ $index }}][days][]" value="{{ $key }}" id="day_{{ $index }}_{{ $key }}"
                                                    {{ in_array($key, $rule['days'] ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="day_{{ $index }}_{{ $key }}">{{ $label }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-12 range-block" style="display: {{ ($rule['type'] ?? '') == 'date_range' ? 'block' : 'none' }}">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="small fw-bold">С</label>
                                            <input type="date" class="form-control form-control-sm"
                                                   name="target_time[{{ $index }}][start_date]"
                                                   value="{{ $rule['start_date'] ?? '' }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="small fw-bold">По</label>
                                            <input type="date" class="form-control form-control-sm"
                                                   name="target_time[{{ $index }}][end_date]"
                                                   value="{{ $rule['end_date'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="small fw-bold">Время с</label>
                                            <input type="time" class="form-control form-control-sm time-start"
                                                   name="target_time[{{ $index }}][start_time]"
                                                   value="{{ $rule['start_time'] ?? '' }}" step="60">
                                        </div>
                                        <div class="col-6">
                                            <label class="small fw-bold">Время до</label>
                                            <input type="time" class="form-control form-control-sm time-end"
                                                   name="target_time[{{ $index }}][end_time]"
                                                   value="{{ $rule['end_time'] ?? '' }}" step="60">
                                        </div>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input round-the-clock"
                                               id="round_the_clock_{{ $index }}" data-index="{{ $index }}">
                                        <label class="form-check-label small" for="round_the_clock_{{ $index }}">
                                            <i class="fa fa-infinity me-1"></i> Круглосуточно (очистить время)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="alert alert-info border-0 shadow-sm p-3">
            <h6 class="alert-heading fw-bold mb-2">
                <i class="fa fa-info-circle me-1"></i> Как это работает?
            </h6>
            <ul class="list-unstyled fs-xs mb-0">
                <li class="mb-2"><strong>ИЛИ:</strong> Виджет покажется, если сработает хотя бы одно правило</li>
                <li class="mb-2"><strong>Дни недели + время:</strong> Показ только в указанные дни и часы</li>
                <li><strong>Диапазон дат + время:</strong> Показ в период и в указанные часы</li>
                <li><strong>Круглосуточно:</strong> Если время не указано, правило действует весь день</li>
            </ul>
        </div>
    </div>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('timeRulesContainer');
            let ruleCounter = {{ count($widget->target_time ?? []) }};

            function updateRuleVisibility(ruleDiv) {
                const type = ruleDiv.querySelector('.rule-type:checked')?.value || 'days';
                const daysBlock = ruleDiv.querySelector('.days-block');
                const rangeBlock = ruleDiv.querySelector('.range-block');

                if (daysBlock) daysBlock.style.display = type === 'days' ? 'block' : 'none';
                if (rangeBlock) rangeBlock.style.display = type === 'date_range' ? 'block' : 'none';
            }

            function initRoundTheClock(ruleDiv, index) {
                const startTimeInput = ruleDiv.querySelector(`input[name="target_time[${index}][start_time]"]`);
                const endTimeInput = ruleDiv.querySelector(`input[name="target_time[${index}][end_time]"]`);
                const roundTheClockCheckbox = ruleDiv.querySelector('.round-the-clock');

                if (!roundTheClockCheckbox) return;

                const hasNoTime = (!startTimeInput?.value || startTimeInput.value === '') &&
                    (!endTimeInput?.value || endTimeInput.value === '');
                roundTheClockCheckbox.checked = hasNoTime;

                roundTheClockCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        if (startTimeInput) startTimeInput.value = '';
                        if (endTimeInput) endTimeInput.value = '';
                    }
                });
            }

            function initDayButtons(ruleDiv) {
                const selectAllBtn = ruleDiv.querySelector('.select-all-days');
                const clearAllBtn = ruleDiv.querySelector('.clear-all-days');
                const checkboxes = ruleDiv.querySelectorAll('.day-checkbox');

                if (selectAllBtn) {
                    selectAllBtn.addEventListener('click', () => {
                        checkboxes.forEach(cb => cb.checked = true);
                    });
                }
                if (clearAllBtn) {
                    clearAllBtn.addEventListener('click', () => {
                        checkboxes.forEach(cb => cb.checked = false);
                    });
                }
            }

            function bindRuleEvents(ruleDiv) {
                ruleDiv.querySelectorAll('.rule-type').forEach(radio => {
                    radio.addEventListener('change', () => updateRuleVisibility(ruleDiv));
                });

                const removeBtn = ruleDiv.querySelector('.remove-rule-btn');
                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        ruleDiv.remove();
                        if (container.querySelectorAll('.time-rule-card').length === 0) {
                            container.innerHTML = `
                            <div class="alert alert-light text-center py-4 border border-dashed" id="emptyRulesPlaceholder">
                                <i class="fa fa-clock fa-2x text-muted mb-2 d-block"></i>
                                <p class="mb-0 small text-muted">Нет правил временного таргетинга</p>
                                <p class="mb-0 small text-muted">Нажмите "Добавить правило" чтобы начать</p>
                            </div>
                        `;
                        }
                    });
                }

                const index = ruleDiv.querySelector('.rule-type')?.getAttribute('name')?.match(/\d+/)?.[0];
                if (index !== undefined) {
                    initRoundTheClock(ruleDiv, index);
                }

                initDayButtons(ruleDiv);
                updateRuleVisibility(ruleDiv);
            }

            function addNewRule() {
                const placeholder = document.getElementById('emptyRulesPlaceholder');
                if (placeholder) placeholder.remove();

                const index = ruleCounter++;

                const weekDays = { mon: 'Пн', tue: 'Вт', wed: 'Ср', thu: 'Чт', fri: 'Пт', sat: 'Сб', sun: 'Вс' };
                let daysHtml = '';
                for (const [key, label] of Object.entries(weekDays)) {
                    daysHtml += `
                    <div class="form-check">
                        <input class="form-check-input day-checkbox" type="checkbox"
                               name="target_time[${index}][days][]" value="${key}" id="day_${index}_${key}">
                        <label class="form-check-label small" for="day_${index}_${key}">${label}</label>
                    </div>
                `;
                }

                const ruleHtml = `
                <div class="time-rule-card border rounded-3 p-3 bg-white shadow-sm mb-3" data-rule-index="${index}">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="fw-semibold small text-uppercase text-muted">
                            <i class="fa fa-merge me-1"></i> Правило #${index + 1}
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger remove-rule-btn">
                            <i class="fa fa-trash-can"></i> Удалить
                        </button>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check rule-type" name="target_time[${index}][type]"
                                       id="type_days_${index}" value="days" checked>
                                <label class="btn btn-outline-secondary" for="type_days_${index}">
                                    <i class="fa fa-calendar-week me-1"></i> Дни недели
                                </label>
                                <input type="radio" class="btn-check rule-type" name="target_time[${index}][type]"
                                       id="type_range_${index}" value="date_range">
                                <label class="btn btn-outline-secondary" for="type_range_${index}">
                                    <i class="fa fa-calendar-alt me-1"></i> Диапазон дат
                                </label>
                            </div>
                        </div>
                        <div class="col-12 days-block">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="small fw-bold">Дни недели</label>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-sm btn-outline-secondary select-all-days">Выбрать все</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary clear-all-days">Очистить</button>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-1">${daysHtml}</div>
                        </div>
                        <div class="col-12 range-block" style="display: none;">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small fw-bold">С</label>
                                    <input type="date" class="form-control form-control-sm"
                                           name="target_time[${index}][start_date]">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">По</label>
                                    <input type="date" class="form-control form-control-sm"
                                           name="target_time[${index}][end_date]">
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small fw-bold">Время с</label>
                                    <input type="time" class="form-control form-control-sm time-start"
                                           name="target_time[${index}][start_time]" step="60">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold">Время до</label>
                                    <input type="time" class="form-control form-control-sm time-end"
                                           name="target_time[${index}][end_time]" step="60">
                                </div>
                            </div>
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input round-the-clock" id="round_the_clock_${index}" data-index="${index}">
                                <label class="form-check-label small" for="round_the_clock_${index}">
                                    <i class="fa fa-infinity me-1"></i> Круглосуточно (очистить время)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                container.insertAdjacentHTML('beforeend', ruleHtml);
                const newRule = container.lastElementChild;
                bindRuleEvents(newRule);
            }

            document.getElementById('addTimeRuleBtn')?.addEventListener('click', addNewRule);
            document.querySelectorAll('.time-rule-card').forEach(card => bindRuleEvents(card));
        });
    </script>
@endpush

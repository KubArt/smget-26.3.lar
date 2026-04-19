@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="content-heading mb-0">Виджеты сайта: {{ $site->domain }}</h2>
            <a href="{{ route('cabinet.marketplace.index', ['site_id' => $site->id]) }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Добавить виджет
            </a>
        </div>

        <div class="block block-rounded">
            <div class="block-content">
                @if($widgets->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover align-middle">
                            <thead class="bg-body-light">
                            <tr>
                                <th style="min-width: 180px;">Виджет</th>
                                <th style="min-width: 150px;">Тип</th>
                                <th style="min-width: 160px;">Показ</th>
                                <th style="min-width: 140px;">Таргетинг</th>
                                <th class="text-center" style="width: 80px;">Статус</th>
                                <th class="text-end" style="width: 140px;">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($widgets as $widget)
                                @php
                                    $behavior = $widget->behavior ?? [];
                                    $triggerType = $behavior['trigger_type'] ?? 'immediate';
                                    $frequency = $behavior['frequency'] ?? 'always';

                                    // Иконка триггера
                                    $triggerIcons = [
                                        'immediate' => ['icon' => 'fa-bolt', 'color' => 'text-success', 'label' => 'При загрузке'],
                                        'delay' => ['icon' => 'fa-clock', 'color' => 'text-info', 'label' => 'Таймер'],
                                        'scroll' => ['icon' => 'fa-arrows-up-down', 'color' => 'text-primary', 'label' => 'Скролл'],
                                        'exit' => ['icon' => 'fa-arrow-right-from-bracket', 'color' => 'text-danger', 'label' => 'Уход'],
                                        'click' => ['icon' => 'fa-mouse-pointer', 'color' => 'text-warning', 'label' => 'Клик'],
                                    ];
                                    $trigger = $triggerIcons[$triggerType] ?? $triggerIcons['immediate'];

                                    // Иконка частоты
                                    $frequencyIcons = [
                                        'always' => ['icon' => 'fa-infinity', 'color' => 'text-muted', 'label' => 'Без лимита'],
                                        'once_session' => ['icon' => 'fa-window-restore', 'color' => 'text-info', 'label' => 'Раз в сессию'],
                                        'once_day' => ['icon' => 'fa-sun', 'color' => 'text-warning', 'label' => 'Раз в день'],
                                        'once_week' => ['icon' => 'fa-calendar-week', 'color' => 'text-primary', 'label' => 'Раз в неделю'],
                                        'once_month' => ['icon' => 'fa-calendar-alt', 'color' => 'text-secondary', 'label' => 'Раз в месяц'],
                                        'once_forever' => ['icon' => 'fa-lock', 'color' => 'text-danger', 'label' => 'Один раз'],
                                    ];
                                    $freq = $frequencyIcons[$frequency] ?? $frequencyIcons['always'];

                                    // Таргетинг иконки
                                    $hasTargeting = false;
                                    $targetingIcons = [];

                                    // Проверка таргета по страницам
                                    $hasPathTarget = !empty($widget->target_paths['allow']) || !empty($widget->target_paths['disallow']);
                                    if ($hasPathTarget) {
                                        $hasTargeting = true;
                                    }

                                    // Проверка UTM таргета
                                    $hasUtmTarget = !empty($widget->target_utm);
                                    if ($hasUtmTarget) {
                                        $hasTargeting = true;
                                    }
                                    // Проверка по времени
                                    $hasTimeTarget = !empty($widget->target_time);
                                    if ($hasTimeTarget) {
                                        $hasTargeting = true;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="fa {{ $widget->widgetType->icon ?? 'fa-puzzle-piece' }} fa-fw text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $widget->custom_name ?: $widget->widgetType->name }}</div>
                                                @if($widget->custom_name)
                                                    <small class="text-muted">{{ $widget->widgetType->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="bg-alt-{{ $widget->widgetType->category === 'marketing' ? 'success' : ($widget->widgetType->category === 'social' ? 'info' : 'secondary') }}">
                                            {{ $widget->widgetType->category ?? 'other' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <div class="mb-1">
                                                <i class="fa {{ $trigger['icon'] }} {{ $trigger['color'] }} fa-fw me-1"></i>
                                                <span class="small">{{ $trigger['label'] }}</span>
                                                @if($triggerType === 'delay' && isset($behavior['delay']))
                                                    <span class="badge bg-light text-dark ms-1">{{ $behavior['delay'] }} сек</span>
                                                @endif
                                                @if($triggerType === 'scroll' && isset($behavior['scroll_percent']))
                                                    <span class="badge bg-light text-dark ms-1">{{ $behavior['scroll_percent'] }}%</span>
                                                @endif
                                            </div>
                                            <div>
                                                <i class="fa {{ $freq['icon'] }} {{ $freq['color'] }} fa-fw me-1"></i>
                                                <span class="small text-muted">{{ $freq['label'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($hasTargeting)
                                            <div class="d-flex gap-1">
                                                @if($hasPathTarget)
                                                    <span class="badge bg-light text-dark" title="Настроен таргетинг по URL">
                                                        <i class="fa fa-link fa-fw"></i> URL
                                                    </span>
                                                @endif
                                                @if($hasUtmTarget)
                                                    <span class="badge bg-light text-dark" title="Настроен таргетинг по UTM">
                                                        <i class="fa fa-tag fa-fw"></i> UTM
                                                    </span>
                                                @endif
                                                @if($hasTimeTarget)
                                                    <span class="badge bg-light text-dark" title="Настроен таргетинг по UTM">
                                                    <i class="fa fa-clock-rotate-left fa-fw"></i> TIME
                                                </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">
                                                <i class="fa fa-globe fa-fw me-1"></i> Все страницы
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input widget-toggle-input"
                                                   type="checkbox"
                                                   id="widget-switch-{{ $widget->id }}"
                                                   data-id="{{ $widget->id }}"
                                                   data-name="{{ $widget->widgetType->name }}"
                                                {{ $widget->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('cabinet.sites.widgets.design', [$site, $widget]) }}"
                                               class="btn btn-alt-secondary" title="Настроить дизайн">
                                                <i class="fa fa-paint-brush"></i>
                                            </a>
                                            <a href="{{ route('cabinet.sites.widgets.statistic', [$site, $widget]) }}"
                                               class="btn btn-alt-secondary" title="Статистика">
                                                <i class="fa fa-chart-line"></i>
                                            </a>
                                            <a href="{{ route('cabinet.sites.widgets.config', [$site, $widget]) }}"
                                               class="btn btn-alt-secondary" title="Настройки">
                                                <i class="fa fa-sliders-h"></i>
                                            </a>
                                            <form action="{{ route('cabinet.sites.widgets.destroy', [$site, $widget]) }}"
                                                  method="POST" onsubmit="return confirm('Удалить виджет?')" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-alt-secondary text-danger" title="Удалить">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-5 text-center">
                        <i class="fa fa-puzzle-piece fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">На этом сайте еще нет установленных виджетов.</p>
                        <a href="{{ route('cabinet.marketplace.index', ['site_id' => $site->id]) }}" class="btn btn-alt-primary">
                            <i class="fa fa-store me-1"></i> Перейти в маркетплейс
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            $('.widget-toggle-input').on('change', function() {
                let $input = $(this);
                let widgetId = $input.data('id');
                let isChecked = $input.is(':checked');
                let widgetName = $input.data('name');

                $input.prop('disabled', true);

                $.ajax({
                    url: `/cabinet/sites/{{ $site->id }}/widgets/${widgetId}/toggle`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: isChecked ? 1 : 0
                    },
                    success: function(response) {
                        if (response.success) {
                            One.helpers('jq-notify', {
                                type: response.is_enabled ? 'success' : 'info',
                                icon: response.is_enabled ? 'fa fa-check me-1' : 'fa fa-info-circle me-1',
                                message: response.message
                            });

                            // Анимация строки
                            let $row = $input.closest('tr');
                            $row.addClass('bg-body-light');
                            setTimeout(() => $row.removeClass('bg-body-light'), 300);
                        }
                    },
                    error: function(xhr) {
                        $input.prop('checked', !isChecked);

                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: 'Ошибка при изменении статуса виджета'
                        });
                    },
                    complete: function() {
                        $input.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush

@push('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
        }
        .badge.bg-alt-success {
            background-color: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }
        .badge.bg-alt-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }
        .badge.bg-alt-secondary {
            background-color: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }
        .bg-body-light {
            background-color: #f8fafc;
            transition: background-color 0.3s;
        }
    </style>
@endpush

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
                    <table class="table table-vcenter table-hover">
                        <thead>
                        <tr>
                            <th>Название</th>
                            <th>Тип</th>
                            <th class="text-center">Статус</th>
                            <th class="text-end">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($widgets as $widget)
                            <tr>
                                <td class="fw-semibold">{{ $widget->name ?? $widget->widgetType->name }}</td>
                                <td><span class="badge bg-alt-info text-info">{{ $widget->widgetType->category }}</span></td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input widget-toggle-input"
                                               type="checkbox"
                                               id="widget-switch-{{ $widget->id }}"
                                               data-id="{{ $widget->id }}"
                                               data-name="{{ $widget->widgetType->name }}"
                                            {{ $widget->is_enabled ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">

                                        <a href="{{ route('cabinet.sites.widgets.design', [$site, $widget]) }}" class="btn btn-sm btn-alt-primary" title="Настроить дизайн">
                                            <i class="fa fa-paint-brush"></i> Дизайн
                                        </a>

                                        <a href="{{ route('cabinet.sites.widgets.statistic', [$site, $widget]) }}"
                                           class="btn btn-sm btn-alt-secondary" title="Статистика">
                                            <i class="fa fa-fw fa-chart-line text-primary"></i>
                                        </a>

                                        <a href="{{ route('cabinet.sites.widgets.config', [$site, $widget]) }}"
                                           class="btn btn-sm btn-alt-secondary" title="Настроить">
                                            <i class="fa fa-pencil-alt"></i>
                                        </a>
                                        <form action="{{ route('cabinet.sites.widgets.destroy', [$site, $widget]) }}"
                                              method="POST" onsubmit="return confirm('Удалить виджет?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-alt-danger">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="py-5 text-center">
                        <i class="fa fa-puzzle-piece fa-3x text-muted mb-3"></i>
                        <p class="text-muted">На этом сайте еще нет установленных виджетов.</p>
                        <a href="{{ route('cabinet.marketplace.index', ['site_id' => $site->id]) }}" class="btn btn-alt-primary">
                            Перейти в маркетплейс
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

                // Блокируем элемент на время запроса
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
                            // Информативное уведомление OneUI
                            One.helpers('jq-notify', {
                                type: response.is_enabled ? 'success' : 'info',
                                icon: response.is_enabled ? 'fa fa-check me-1' : 'fa fa-info-circle me-1',
                                message: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        // Возвращаем состояние назад при ошибке
                        $input.prop('checked', !isChecked);

                        One.helpers('jq-notify', {
                            type: 'danger',
                            icon: 'fa fa-times me-1',
                            message: 'Ошибка при изменении статуса'
                        });
                    },
                    complete: function() {
                        // Разблокируем элемент
                        $input.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush

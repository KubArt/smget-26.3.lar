{{-- resources/views/cabinet/sites/metrics/config.blade.php --}}
@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-7">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="{{ $metricConfig['icon'] ?? 'fa fa-chart-line' }} me-2"></i>
                            {{ $metricConfig['name'] }} - настройки
                        </h3>
                    </div>

                    <form action="{{ route('cabinet.sites.metrics.update', [$site, $metricSlug]) }}"
                          method="POST" id="metric-form">
                        @csrf @method('PUT')

                        <div class="block-content pb-4">
                            {{-- Статус --}}
                            <div class="mb-4">
                                <label class="form-check form-switch">
                                    <input type="checkbox" name="is_active" value="1"
                                           class="form-check-input"
                                        {{ $siteMetric->is_active ? 'checked' : '' }}>
                                    <span class="form-check-label">Отправлять конверсии</span>
                                </label>
                            </div>

                            {{-- Динамические поля в зависимости от типа метрики --}}
                            @php
                                $partialPath = "cabinet.sites.metrics.partials.{$metricSlug}";
                            @endphp

                            @if(view()->exists($partialPath))
                                @include($partialPath, ['settings' => $siteMetric->settings ?? []])
                            @else
                                {{-- Универсальные поля для метрик с API key --}}
                                <div class="mb-4">
                                    <label class="form-label">API Key / Token</label>
                                    <input type="text" name="settings[api_key]" class="form-control"
                                           value="{{ $siteMetric->settings['api_key'] ?? '' }}">
                                    <div class="form-text text-muted">
                                        Введите API ключ из личного кабинета сервиса
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Дополнительные настройки (JSON)</label>
                                    <textarea name="settings[custom]" class="form-control" rows="3"
                                              placeholder='{"param1": "value1", "param2": "value2"}'></textarea>
                                </div>
                            @endif

                            <div class="pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Сохранить
                                </button>

                                @if($siteMetric->exists && $siteMetric->is_active)
                                    <button type="button" class="btn btn-alt-secondary ms-2"
                                            onclick="testConnection()">
                                        <i class="fa fa-plug me-1"></i> Проверить подключение
                                    </button>
                                @endif

                                @if($siteMetric->exists)
                                    <button type="button" class="btn btn-alt-danger ms-2"
                                            onclick="confirmDisconnect()">
                                        <i class="fa fa-trash me-1"></i> Отключить
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-5">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Инструкция по настройке</h3>
                    </div>
                    <div class="block-content pb-4">
                        @php
                            $instructionPath = "cabinet.sites.metrics.instructions.{$metricSlug}";
                        @endphp

                        @if(view()->exists($instructionPath))
                            @include($instructionPath)
                        @else
                            @include('cabinet.sites.metrics.instructions.default')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="delete-form" action="{{ route('cabinet.sites.metrics.destroy', [$site, $metricSlug]) }}"
          method="POST" style="display: none;">
        @csrf @method('DELETE')
    </form>

    @push('js')
        <script>
            function testConnection() {
                $.ajax({
                    url: "{{ route('cabinet.sites.metrics.test', [$site, $metricSlug]) }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            One.helpers('jq-notify', {type: 'success', message: response.message});
                        } else {
                            One.helpers('jq-notify', {type: 'danger', message: response.message});
                        }
                    },
                    error: function() {
                        One.helpers('jq-notify', {type: 'danger', message: 'Ошибка при проверке подключения'});
                    }
                });
            }

            function confirmDisconnect() {
                if (confirm('Вы уверены? Отключение метрики удалит все настройки.')) {
                    document.getElementById('delete-form').submit();
                }
            }
        </script>
    @endpush
@endsection

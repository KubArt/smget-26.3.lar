@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <h2 class="content-heading">Настройка интеграции: {{ $service->name }}</h2>

        <div class="row">
            <div class="col-md-7">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Параметры подключения</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form action="{{ route('cabinet.sites.integrations.update', [$site, $service]) }}" method="POST" id="main-settings-form">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label">Статус интеграции</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_enabled" value="1" {{ $siteService->is_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label">Активна</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="api_key">API Ключ стороннего сервиса (external)</label>
                                <input type="text" class="form-control" id="api_key" name="api_key" value="{{ $siteService->api_key }}" placeholder="Введите токен из личного кабинета {{ $service->name }}">
                            </div>
                        </form>

                        <div class="mb-4">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                Ваш уникальный Webhook URL
                                <form action="{{ route('cabinet.sites.integrations.regenerate', [$site, $service]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Внимание! Старый URL перестанет работать. Вы уверены?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 m-0" style="text-decoration: none;">
                                        <i class="fa fa-sync-alt me-1"></i> Перевыпустить токен
                                    </button>
                                </form>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="webhook-url" readonly
                                       value="{{ url("/api/v1/capture/{$service->slug}?token={$siteService->api_key}") }}">
                                <button type="button" class="btn btn-alt-secondary" onclick="copyWebhook()">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                Используйте этот URL в настройках вебхуков {{ $service->name }}.
                            </div>
                        </div>

                        {{--
                            TODO: добавить возможность генерации различного кол-ва маршрутов
                            для каждого сервиса (например, для разных типов событий/форм)

                            Рекомендация по реализации TODO в будущем:
                            Для «различного кол-ва маршрутов» лучше всего добавить в таблицу services поле endpoints (тип JSON), где вы будете хранить массив доступных путей, и затем в цикле выводить их в интерфейсе настроек.
                        --}}

                        <div class="pt-2">
                            <button type="submit" form="main-settings-form" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Сохранить настройки
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Инструкция по настройке</h3>
                    </div>
                    <div class="block-content pb-4">
                        <div class="alert alert-info d-flex align-items-start">
                            <i class="fa fa-info-circle me-2 mt-1"></i>
                            <div>{{ $service->instruction ?? 'Инструкция отсутствует.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        function copyWebhook() {
            var copyText = document.getElementById("webhook-url");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);

            One.helpers('jq-notify', {
                type: 'success',
                icon: 'fa fa-check me-1',
                message: 'URL скопирован в буфер обмена'
            });
        }
    </script>
@endpush

{{-- resources/views/cabinet/sites/metrics/index.blade.php --}}
@extends('cabinet.layouts.cabinet')

@section('title', 'Метрики и аналитика - ' . $site->domain)

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Интеграция с метриками</h1>
        </div>

        <div class="row">
            @foreach($availableMetrics as $slug => $config)
                @php
                    $connected = $connectedMetrics->where('type', $slug)->first();
                    $isActive = $connected && $connected->is_active;
                @endphp

                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card shadow h-100 py-2 border-left-{{ $isActive ? 'success' : 'secondary' }}">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1 {{ $isActive ? 'text-success' : 'text-gray-800' }}">
                                        {{ $config['name'] }}
                                    </div>
                                    <div class="mb-0 text-gray-600 small">
                                        {{ $config['description'] }}
                                    </div>

                                    @if($connected && isset($connected->settings['counter_id']))
                                        <div class="mt-2 badge badge-info">
                                            ID: {{ $connected->settings['counter_id'] }}
                                        </div>
                                    @endif
                                </div>
                                <div class="col-auto">
                                    <i class="{{ $config['icon'] }} fa-2x text-gray-300"></i>
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                @if(!$connected)
                                    {{-- Кнопка подключения через Popup --}}
                                    <button onclick="openOAuth('{{ route('cabinet.sites.metrics.redirect', [$site, $slug]) }}')"
                                            class="btn btn-sm btn-primary">
                                        <i class="fas fa-plug fa-sm"></i> Подключить
                                    </button>
                                @else
                                    <div class="btn-group">
                                        <a href="{{ route('cabinet.sites.metrics.config', [$site, $slug]) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-cog"></i> Настроить
                                        </a>

                                        <button onclick="testConnection('{{ $slug }}')"
                                                class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-sync"></i> Тест
                                        </button>
                                    </div>

                                    <form action="{{ route('cabinet.sites.metrics.destroy', [$site, $slug]) }}" method="POST" onsubmit="return confirm('Отключить метрику?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm text-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('js')
        <script>
            /**
             * Открытие окна авторизации
             */
            function openOAuth(url) {
                const width = 600;
                const height = 700;
                const left = (window.innerWidth / 2) - (width / 2);
                const top = (window.innerHeight / 2) - (height / 2);

                const oauthWindow = window.open(
                    url,
                    'OAuth',
                    `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,status=yes`
                );

                // Интервал для проверки, не закрыто ли окно, чтобы обновить основную страницу
                const timer = setInterval(function() {
                    if (oauthWindow.closed) {
                        clearInterval(timer);
                        // Обновляем страницу, чтобы отобразить изменения после callback
                        window.location.reload();
                    }
                }, 1000);
            }

            /**
             * Проверка соединения (AJAX)
             */
            function testConnection(slug) {
                const btn = event.currentTarget;
                btn.disabled = true;

                fetch('{{ route("cabinet.sites.metrics.test", [$site, ":slug"]) }}'.replace(':slug', slug), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message || (data.success ? 'Соединение установлено!' : 'Ошибка проверки'));
                    })
                    .catch(err => alert('Ошибка при выполнении запроса'))
                    .finally(() => btn.disabled = false);
            }
        </script>
    @endpush
@endsection

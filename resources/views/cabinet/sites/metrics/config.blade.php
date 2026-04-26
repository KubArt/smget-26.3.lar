@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-7">
                {{-- Основной блок настроек --}}
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="{{ $metricConfig['icon'] ?? 'fa fa-chart-line' }} me-2 text-primary"></i>
                            {{ $metricConfig['name'] }} - параметры
                        </h3>
                    </div>

                    <form action="{{ route('cabinet.sites.metrics.update', [$site, $metricSlug]) }}" method="POST" id="metric-form">
                        @csrf @method('PUT')

                        <div class="block-content pb-4">
                            {{-- Статус активности --}}
                            <div class="mb-4">
                                <label class="form-check form-switch">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                        {{ $siteMetric->is_active ? 'checked' : '' }}>
                                    <span class="form-check-label text-dark fw-semibold">Передача данных активна</span>
                                </label>
                                <div class="form-text">Если выключено, скрипты виджетов перестанут отправлять события в {{ $metricConfig['name'] }}.</div>
                            </div>

                            {{-- Динамические поля из драйвера --}}
                            @include("cabinet.sites.metrics.partials.{$metricSlug}", ['settings' => $siteMetric->settings])

                            <div class="mt-4 pt-3 border-top d-flex">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Сохранить изменения
                                </button>

                                @if($siteMetric->is_active)
                                    <button type="button" class="btn btn-alt-secondary ms-2" onclick="testConnection()">
                                        <i class="fa fa-sync me-1"></i> Проверить подключение
                                    </button>
                                @endif

                                <button type="button" class="btn btn-link text-danger ms-auto" onclick="confirmDisconnect()">
                                    Отключить метрику
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- НОВЫЙ БЛОК: Синхронизированные цели --}}
                @if($siteMetric->is_active)
                    <div class="block block-rounded border-start border-success border-3">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Активные цели (из виджетов)</h3>
                            <div class="block-options">
                                <a href="{{ route('cabinet.sites.metrics.select-counter', [$site, $metricSlug]) }}" class="btn btn-sm btn-alt-primary">
                                    <i class="fa fa-sync me-1"></i> Пересинхронизировать
                                </a>
                            </div>
                        </div>
                        <div class="block-content">
                            <p class="fs-sm text-muted">Эти события автоматически передаются в ваш счетчик при взаимодействии пользователя с виджетами:</p>
                            <div class="list-group list-group-flush mb-3">
                                {{-- Данные передаются из контроллера через MetricsManager::getPendingGoals() --}}
                                @forelse($syncedGoals as $group)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-primary">{{ $group['widget_name'] }}</span>
                                            <span class="badge bg-body-dark text-dark fs-xs">{{ $group['widget_type'] }}</span>
                                        </div>
                                        @foreach($group['goals'] as $goal)
                                            <div class="d-flex justify-content-between align-items-center ps-3 py-1">
                                                <div class="fs-sm">
                                                    <i class="fa fa-caret-right text-muted me-1"></i>
                                                    {{ $goal['display_name'] }}
                                                </div>
                                                <code class="fs-xs text-info">{{ $goal['event_key'] }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <i class="fa fa-ghost fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">Нет активных виджетов для синхронизации</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ТЕСТОВЫЙ БЛОК: Отображение сырых данных для API --}}
                <div class="block block-rounded border-start border-warning border-3 mt-4">
                    <div class="block-header block-header-default bg-warning-light">
                        <h3 class="block-title text-warning-dark">
                            <i class="fa fa-bug me-2"></i> Debug: Данные для API (удалить после теста)
                        </h3>
                    </div>
                    <div class="block-content">
                        <div class="alert alert-warning py-2 fs-xs">
                            Это список того, что метод <code>syncGoals()</code> пытается отправить в Яндекс.
                        </div>

                        @php
                            // Собираем плоский массив, который идет в драйвер
                            $debugFlatGoals = [];
                            foreach($syncedGoals as $group) {
                                foreach($group['goals'] as $goal) {
                                    $debugFlatGoals[] = [
                                        'goal' => [
                                            'name' => $group['widget_name'] . ": " . $goal['display_name'],
                                            'type' => 'action',
                                            'conditions' => [
                                                ['type' => 'exact', 'url' => $goal['event_key']]
                                            ]
                                        ]
                                    ];
                                }
                            }
                        @endphp

                        <pre class="bg-dark text-white p-3 rounded fs-xs" style="max-height: 400px; overflow-y: auto;">
                            {{ json_encode($debugFlatGoals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </pre>

                        <div class="mb-3 mt-2">
                            <button type="button" class="btn btn-sm btn-warning w-100" onclick="testGoalsSync()">
                                <i class="fa fa-rocket me-1"></i> Принудительно вызвать syncGoals()
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Добавим JS функцию для теста --}}
                @push('js')
                    <script>
                        function testGoalsSync() {
                            Swal.fire({
                                title: 'Запустить синхронизацию?',
                                text: "Будет вызван метод драйвера syncGoals с текущими данными",
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Да, погнали'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch("{{ route('cabinet.sites.metrics.final-sync', [$site, $metricSlug]) }}", {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                    })
                                        .then(r => Swal.fire('Готово', 'Запрос отправлен, проверьте лог или кабинет Метрики', 'success'))
                                        .catch(e => Swal.fire('Ошибка', 'Что-то пошло не так', 'error'));
                                }
                            });
                        }
                    </script>
                @endpush










            </div>

            {{-- Инструкции (боковая панель) --}}
            <div class="col-md-5">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Справка</h3>
                    </div>
                    <div class="block-content">
                        @include("cabinet.sites.metrics.instructions.{$metricSlug}")
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        /**
         * Проверка связи с API (например, Яндекс.Метрикой)
         */
        function testConnection() {
            const btn = event.currentTarget;
            const originalContent = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Проверяю...';

            fetch("{{ route('cabinet.sites.metrics.test', [$site, $metricSlug]) }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Успешно!', data.message || 'Связь с аккаунтом установлена.', 'success');
                    } else {
                        Swal.fire('Ошибка', data.message || 'Не удалось проверить подключение.', 'error');
                    }
                })
                .catch(() => Swal.fire('Ошибка', 'Произошла системная ошибка при проверке.', 'error'))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
        }

        /**
         * Подтверждение отключения
         */
        function confirmDisconnect() {
            Swal.fire({
                title: 'Вы уверены?',
                text: "Интеграция будет удалена, цели перестанут отслеживаться.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Да, отключить',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('cabinet.sites.metrics.destroy', [$site, $metricSlug]) }}";
                    form.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(form);
                    form.submit();
                }
            })
        }
    </script>
@endpush

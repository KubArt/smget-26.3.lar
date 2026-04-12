@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-2">Статистика виджета: {{ $widget->custom_name }}</h1>
                <h2 class="h6 fw-medium fw-medium text-muted mb-0">Сводные данные по показам и кликам</h2>
            </div>
        </div>
        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <form id="stats-filter-form" class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label">Период</label>
                        <select name="period_preset" id="period_preset" class="form-select">
                            <option value="today">Сегодня</option>
                            <option value="yesterday">Вчера</option>
                            <option value="7_days" selected>Последние 7 дней</option>
                            <option value="30_days">Последние 30 дней</option>
                            <option value="this_week">Эта неделя</option>
                            <option value="last_week">Предыдущая неделя</option>
                            <option value="this_month">Этот месяц</option>
                            <option value="last_month">Предыдущий месяц</option>
                            <option value="custom">Произвольный выбор</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Группировка</label>
                        <select name="group_by" class="form-select">
                            <option value="day">По дням</option>
                            <option value="week">По неделям</option>
                            <option value="month">По месяцам</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Источник (UTM)</label>
                        <select name="utm_source" class="form-select">
                            <option value="">Все источники</option>
                            @foreach($availableUtms as $source)
                                <option value="{{ $source }}">{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Вид</label>
                        <button type="button" class="btn btn-alt-primary w-100" id="toggle-chart-type">
                            <i class="fa fa-chart-bar"></i>
                        </button>
                    </div>

                    <div class="col-md-3 d-none" id="custom-date-range">
                        <label class="form-label">Даты (от и до)</label>
                        <div class="input-group">
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-6 col-lg-4">
                <div class="block block-rounded text-center">
                    <div class="block-content block-content-full">
                        <div class="fs-2 fw-semibold text-primary" id="total-views">0</div>
                    </div>
                    <div class="block-content py-2 bg-body-light">
                        <p class="fw-medium fs-sm text-muted mb-0">Всего показов</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="block block-rounded text-center">
                    <div class="block-content block-content-full">
                        <div class="fs-2 fw-semibold text-success" id="total-clicks">0</div>
                    </div>
                    <div class="block-content py-2 bg-body-light">
                        <p class="fw-medium fs-sm text-muted mb-0">Всего кликов</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="block block-rounded text-center">
                    <div class="block-content block-content-full">
                        <div class="fs-2 fw-semibold text-warning" id="avg-ctr">0%</div>
                    </div>
                    <div class="block-content py-2 bg-body-light">
                        <p class="fw-medium fs-sm text-muted mb-0">Средний CTR</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Динамика активности</h3>
            </div>
            <div class="block-content block-content-full text-center">
                <div class="py-3" style="height: 400px;">
                    <canvas id="js-chartjs-widget-events"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        // Помощник для работы с датами (формат YYYY-MM-DD)
        const formatDate = (date) => date.toISOString().split('T')[0];

        function calculateDates(preset) {
            const now = new Date();
            let from, to = new Date();

            switch (preset) {
                case 'today':
                    from = now;
                    break;
                case 'yesterday':
                    from = new Date();
                    from.setDate(now.getDate() - 1);
                    to = new Date(from);
                    break;
                case '7_days':
                    from = new Date();
                    from.setDate(now.getDate() - 6);
                    break;
                case '30_days':
                    from = new Date();
                    from.setDate(now.getDate() - 29);
                    break;
                case 'this_week':
                    const dayOfWeek = now.getDay() || 7;
                    from = new Date();
                    from.setDate(now.getDate() - dayOfWeek + 1);
                    break;
                case 'last_week':
                    const prevWeekEnd = new Date();
                    const currentDay = now.getDay() || 7;
                    prevWeekEnd.setDate(now.getDate() - currentDay);
                    from = new Date(prevWeekEnd);
                    from.setDate(prevWeekEnd.getDate() - 6);
                    to = prevWeekEnd;
                    break;
                case 'this_month':
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                    break;
                case 'last_month':
                    from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    to = new Date(now.getFullYear(), now.getMonth(), 0);
                    break;
                case 'custom':
                    return null; // Не меняем даты, используем значения из инпутов
            }
            return { from: formatDate(from), to: formatDate(to) };
        }

        // Слушатель изменений формы
        document.getElementById('stats-filter-form').addEventListener('change', function(e) {
            const preset = document.getElementById('period_preset').value;
            const customRange = document.getElementById('custom-date-range');

            if (e.target.id === 'period_preset') {
                if (preset === 'custom') {
                    customRange.classList.remove('d-none');
                } else {
                    customRange.classList.add('d-none');
                    const dates = calculateDates(preset);
                    if (dates) {
                        document.querySelector('input[name="date_from"]').value = dates.from;
                        document.querySelector('input[name="date_to"]').value = dates.to;
                    }
                }
            }

            // Автоматическое обновление при любом изменении
            updateStatistics();
        });

        // Переключение типа графика
        document.getElementById('toggle-chart-type').onclick = () => {
            currentChartType = currentChartType === 'line' ? 'bar' : 'line';
            updateStatistics();
        };

        // Первый запуск при загрузке страницы
        document.addEventListener('DOMContentLoaded', () => {
            // Устанавливаем даты для дефолтного пресета (7 дней) перед первым запросом
            const dates = calculateDates('7_days');
            document.querySelector('input[name="date_from"]').value = dates.from;
            document.querySelector('input[name="date_to"]').value = dates.to;
            updateStatistics();
        });


        let myChart = null;
        let currentChartType = 'line';

        async function updateStatistics() {
            const form = document.getElementById('stats-filter-form');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();

            // Показываем лоадер OneUI
            if (typeof One !== 'undefined') One.layout('loader_show');

            try {
                // ИСПРАВЛЕНИЕ: Мы сохраняем результат fetch в переменную response
                const response = await fetch(`${window.location.pathname}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();

                // 1. Обновляем карточки
                document.getElementById('total-views').innerText = data.totals.views;
                document.getElementById('total-clicks').innerText = data.totals.clicks;
                document.getElementById('avg-ctr').innerText = data.totals.ctr + '%';

                // 2. Обновляем график
                renderChart(data);

            } catch (e) {
                console.error('Stats Error:', e);
                if (typeof One !== 'undefined') {
                    One.helpers('jq-notify', {type: 'danger', message: 'Ошибка при загрузке статистики'});
                }
            } finally {
                // Прячем лоадер
                if (typeof One !== 'undefined') One.layout('loader_hide');
            }
        }

        function renderChart(data) {
            const ctx = document.getElementById('js-chartjs-widget-events').getContext('2d');

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: currentChartType,
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Показы',
                            data: data.views,
                            backgroundColor: 'rgba(6, 101, 208, .1)',
                            borderColor: 'rgba(6, 101, 208, .8)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Клики',
                            data: data.clicks,
                            backgroundColor: 'rgba(28, 187, 140, .1)',
                            borderColor: 'rgba(28, 187, 140, .8)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { tooltip: { mode: 'index', intersect: false } }
                }
            });
        }

        // Инициализация
        document.getElementById('stats-filter-form').onsubmit = (e) => {
            e.preventDefault();
            updateStatistics();
        };

        document.getElementById('toggle-chart-type').onclick = () => {
            currentChartType = currentChartType === 'line' ? 'bar' : 'line';
            updateStatistics();
        };

        updateStatistics(); // Первый запуск
    </script>
@endpush

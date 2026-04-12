<div class="row">
    <div class="col-md-4">
        <div class="block block-rounded block-bordered text-center p-3">
            <div class="fs-sm fw-semibold text-uppercase text-muted">Показы</div>
            <div class="fs-2 fw-bold text-primary">{{ $stats['views'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="block block-rounded block-bordered text-center p-3">
            <div class="fs-sm fw-semibold text-uppercase text-muted">Клики</div>
            <div class="fs-2 fw-bold text-success">{{ $stats['clicks'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="block block-rounded block-bordered text-center p-3">
            <div class="fs-sm fw-semibold text-uppercase text-muted">CTR</div>
            <div class="fs-2 fw-bold text-warning">
                {{ ($stats['views'] ?? 0) > 0 ? number_format(($stats['clicks'] ?? 0) / $stats['views'] * 100, 1) : 0 }}%
            </div>
        </div>
    </div>
</div>

<div class="block block-rounded block-bordered mt-4">
    <div class="block-content block-content-full">
        <canvas id="widgetChart" style="height: 300px;"></canvas>
    </div>
</div>

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('widgetChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!}, // ['01.04', '02.04', ...]
                datasets: [{
                    label: 'Показы',
                    data: {!! json_encode($chartViews) !!},
                    borderColor: '#0665d0',
                    backgroundColor: 'rgba(6, 101, 208, .1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Клики',
                    data: {!! json_encode($chartClicks) !!},
                    borderColor: '#198754',
                    backgroundColor: 'transparent',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
@endpush

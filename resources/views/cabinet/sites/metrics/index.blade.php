{{-- cabinet.sites.metrics.index --}}
@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="content-heading mb-0">Метрики и аналитика: {{ $site->domain }}</h2>
        </div>

        <div class="row">
            @foreach($availableMetrics as $slug => $metric)
                @php
                    $connected = $connectedMetrics->firstWhere('type', $slug);
                    $isActive = $connected && $connected->is_active;
                @endphp

                <div class="col-md-6 col-xl-4">
                    <div class="block block-rounded text-center">
                        <div class="block-content block-content-full">
                            <div class="item item-circle bg-gray-lighter mx-auto my-3">
                                <i class="{{ $metric['icon'] ?? 'fa fa-chart-line' }} text-primary fa-2x"></i>
                            </div>
                            <div class="text-black fw-semibold">{{ $metric['name'] }}</div>
                            <div class="text-muted fs-sm mt-1">{{ $metric['description'] }}</div>
                        </div>
                        <div class="block-content block-content-full bg-body-light">
                            @if($connected)
                                @if($isActive)
                                    <span class="badge bg-success mb-2">Активна</span>
                                @else
                                    <span class="badge bg-secondary mb-2">Отключена</span>
                                @endif
                                <br>
                                <a href="{{ route('cabinet.sites.metrics.config', [$site, $slug]) }}"
                                   class="btn btn-sm btn-alt-primary">
                                    <i class="fa fa-cog me-1"></i> Настроить
                                </a>
                            @else
                                <a href="{{ route('cabinet.sites.metrics.config', [$site, $slug]) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus me-1"></i> Подключить
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

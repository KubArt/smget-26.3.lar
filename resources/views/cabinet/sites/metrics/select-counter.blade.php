{{-- resources/views/cabinet/sites/metrics/select-counter.blade.php --}}

@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Выберите счетчик Яндекс.Метрики</h3>
            </div>

            <form action="{{ route('cabinet.sites.metrics.save-counter', [$site, $metricSlug]) }}" method="POST">
                @csrf

                <div class="block-content">
                    <p class="text-muted mb-4">
                        У вас несколько счетчиков. Выберите тот, для которого будут отправляться конверсии.
                    </p>

                    <div class="list-group mb-4">
                        @foreach($counters as $counter)
                            <label class="list-group-item d-flex align-items-center">
                                <input type="radio" name="counter_id" value="{{ $counter['id'] }}" class="me-3" required>
                                <div>
                                    <strong>{{ $counter['name'] }}</strong>
                                    @if($counter['site'])
                                        <div class="fs-sm text-muted">{{ $counter['site'] }}</div>
                                    @endif
                                    <div class="fs-xs text-muted">ID: {{ $counter['id'] }}</div>
                                </div>
                                @if($counter['status'] !== 'active')
                                    <span class="badge bg-warning ms-auto">Неактивен</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="block-content block-content-full bg-body-light">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check me-1"></i> Выбрать и активировать
                    </button>
                    <a href="{{ route('cabinet.sites.metrics.index', $site) }}" class="btn btn-alt-secondary">
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

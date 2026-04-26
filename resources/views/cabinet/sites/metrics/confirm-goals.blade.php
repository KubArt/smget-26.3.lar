@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="block block-rounded shadow">
                    <div class="block-header block-header-default bg-primary-dark">
                        <h3 class="block-title text-white">Интеграция с Яндекс.Метрикой</h3>
                    </div>
                    <div class="block-content">
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <div class="flex-shrink-0"><i class="fa fa-info-circle me-2"></i></div>
                            <div class="flex-grow-1">
                                Мы проанализировали ваши виджеты и подготовили оптимальный список целей.
                                Они будут созданы в счетчике <strong>ID: {{ $siteMetric->settings['counter_id'] }}</strong>.
                            </div>
                        </div>

                        <h4 class="fw-normal mt-4 mb-3">Список целей по виджетам:</h4>

                        @foreach($goals as $group)
                            <div class="card mb-3 border-0 bg-body-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3 text-primary">
                                            <i class="fa fa-layer-group fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">{{ $group['widget_name'] }}</h5>
                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;">
                                                Тип: {{ $group['widget_type'] }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="list-group list-group-flush rounded">
                                        @foreach($group['goals'] as $goal)
                                            <div class="list-group-item d-flex justify-content-between align-items-center bg-white border-light">
                                                <div>
                                                    <span class="fw-semibold text-dark">{{ $goal['display_name'] }}</span>
                                                    @if($goal['synonym'])
                                                        <span class="text-muted mx-2">|</span>
                                                        <small class="text-muted italic">{{ $goal['synonym'] }}</small>
                                                    @endif
                                                </div>
                                                <span class="badge bg-success-light text-success fw-medium">JS-событие</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="py-4 text-center border-top mt-4">
                            <form action="{{ route('cabinet.sites.metrics.final-sync', [$site, $metricSlug]) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-lg btn-alt-primary px-5 shadow-sm">
                                    <i class="fa fa-check me-2"></i> Подтвердить и создать цели
                                </button>
                                <div class="mt-3">
                                    <a href="{{ route('cabinet.sites.metrics.index', $site) }}" class="text-muted text-decoration-none">
                                        <small>Пропустить и настроить позже вручную</small>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

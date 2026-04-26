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
                                        {{ $siteMetric->is_active ? 'checked' : '' }}
                                        {{ isset($siteMetric->settings['access_token']) ? '' : 'disabled' }}
                                    >
                                    <span class="form-check-label">Отправлять конверсии</span>
                                </label>
                                @if(!isset($siteMetric->settings['access_token']))
                                    <div class="text-muted fs-sm mt-1">
                                        Сначала авторизуйтесь через Яндекс
                                    </div>
                                @endif
                            </div>

                            {{-- OAuth блок --}}
                            @if(!isset($siteMetric->settings['access_token']))
                                <div class="mb-4 p-3 bg-body-light rounded">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="fab fa-yandex fa-2x text-primary me-3"></i>
                                            <span class="fw-semibold">Авторизация через Яндекс</span>
                                            <div class="fs-sm text-muted mt-1">
                                                Нажмите кнопку для авторизации и предоставления доступа
                                            </div>
                                        </div>
                                        <a href="{{ route('cabinet.sites.metrics.redirect', [$site, $metricSlug]) }}"
                                           class="btn btn-primary">
                                            <i class="fab fa-yandex me-1"></i> Авторизоваться
                                        </a>
                                    </div>
                                </div>
                            @else
                                {{-- Уже авторизован --}}
                                <div class="mb-4 p-3 bg-success-light rounded">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="fa fa-check-circle text-success me-2"></i>
                                            <span class="fw-semibold">Токен получен</span>
                                            <div class="fs-sm text-muted mt-1">
                                                Авторизация активна до:
                                                {{ \Carbon\Carbon::parse($siteMetric->settings['token_expires_at'])->format('d.m.Y H:i') }}
                                            </div>
                                            @if(isset($siteMetric->settings['counter_id']))
                                                <div class="fs-sm mt-1">
                                                    <i class="fa fa-chart-line me-1"></i>
                                                    Счетчик: <strong>{{ $siteMetric->settings['counter_id'] }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                        <a href="{{ route('cabinet.sites.metrics.redirect', [$site, $metricSlug]) }}"
                                           class="btn btn-alt-secondary">
                                            <i class="fa fa-sync-alt me-1"></i> Обновить токен
                                        </a>
                                    </div>
                                </div>
                            @endif

                            {{-- Ручные поля (для отладки или если нет OAuth) --}}
                            @if(!isset($metricConfig['oauth']))
                                @include("cabinet.sites.metrics.partials.{$metricSlug}", [
                                    'settings' => $siteMetric->settings ?? []
                                ])
                            @endif

                            <div class="pt-3">
                                <button type="submit" class="btn btn-primary"
                                    {{ !isset($siteMetric->settings['access_token']) ? 'disabled' : '' }}>
                                    <i class="fa fa-save me-1"></i> Сохранить
                                </button>

                                @if(isset($siteMetric->settings['access_token']) && $siteMetric->is_active)
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
                        <h3 class="block-title">Инструкция</h3>
                    </div>
                    <div class="block-content">
                        @include("cabinet.sites.metrics.instructions.{$metricSlug}")
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

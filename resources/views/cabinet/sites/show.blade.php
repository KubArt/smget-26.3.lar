@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-xl-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Настройка виджета для {{ $site->domain }}</h3>
                        <div class="block-options">
                            <a href="{{ route('cabinet.sites.edit', $site) }}" class="btn-block-option">
                                <i class="fa fa-cog"></i> Настройки
                            </a>
                        </div>
                    </div>
                    <div class="block-content">
                        <h5>1. Установите код на сайт</h5>
                        <p class="fs-sm text-muted">Скопируйте этот код и вставьте его в раздел <code>&lt;head&gt;</code> или перед <code>&lt;/body&gt;</code> на всех страницах вашего сайта.</p>

                        <div class="position-relative bg-dark p-3 rounded mb-4">
                            <code id="widget-code" class="text-info">
                                &lt;script src="{{ config('app.url') }}/v1/widget.js" data-key="{{ $site->api_key }}" async&gt;&lt;/script&gt;
                            </code>
                            <button class="btn btn-sm btn-alt-secondary position-absolute top-0 end-0 m-2" onclick="copyToClipboard('#widget-code')">
                                <i class="fa fa-copy"></i>
                            </button>
                        </div>

                        <h5>2. Проверьте установку</h5>
                        <div id="verification-status">
                            @if($site->is_verified)
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="fa fa-check-circle me-2"></i>
                                    <div>Сайт подтвержден: <strong>{{ $site->verified_at->format('d.m.Y H:i') }}</strong></div>
                                </div>
                            @else
                                <div class="alert alert-warning d-flex align-items-center justify-content-between" role="alert">
                                    <div>
                                        <i class="fa fa-exclamation-triangle me-2"></i> Код еще не обнаружен.
                                    </div>
                                    <button type="button" id="btn-verify" class="btn btn-sm btn-warning">
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        Проверить сейчас
                                    </button>
                                </div>
                            @endif
                            <p>сделать проверку по расписанию</p>
                        </div>


                        <a href="{{ route('cabinet.sites.metrics.index', $site) }}"
                           class="btn btn-sm btn-alt-success"
                           data-bs-toggle="tooltip"
                           title="Метрики и аналитика">
                            <i class="fa fa-chart-line"></i>
                        </a>

                    </div>
                </div>

            </div>



            <div class="col-xl-4">

                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Управление тарифом</h3>
                        <div class="block-options">
                            @if($site->is_active)
                                <span class="badge bg-success">Активен</span>
                            @else
                                <span class="badge bg-danger">Пауза</span>
                            @endif
                        </div>
                    </div>
                    <div class="block-content">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fs-sm fw-semibold text-uppercase text-muted">Лимит лидов</span>
                                <span class="fs-sm fw-bold {{ $progressPercent >= 90 ? 'text-danger' : 'text-dark' }}">
                                    {{ $leadsCount }} / {{ $isUnlimited ? '∞' : $leadsLimit }}
                                </span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $progressPercent >= 90 ? 'bg-danger' : 'bg-primary' }}"
                                     role="progressbar"
                                     style="width: {{ $isUnlimited ? 100 : $progressPercent }}%">
                                </div>
                            </div>
                            @if(!$isUnlimited && $leadsCount >= $leadsLimit)
                                <small class="text-danger mt-1 d-block fw-medium">
                                    <i class="fa fa-exclamation-triangle me-1"></i> Лимит исчерпан
                                </small>
                            @endif
                        </div>

                        <table class="table table-borderless table-sm fs-sm mb-0">
                            <tbody>
                            <tr>
                                <td class="text-muted">Тариф:</td>
                                <td class="text-end fw-bold text-primary">
                                    {{ $site->plan->name ?? 'Бесплатный' }}
                                </td>
                            </tr>
                            @if($activeSub)
                                <tr>
                                    <td class="text-muted">Истекает:</td>
                                    <td class="text-end {{ $isExpiredSoon ? 'text-danger fw-bold' : '' }}">
                                        {{ $activeSub->expires_at->format('d.m.Y') }}
                                    </td>
                                </tr>
                            @endif

                            @if($site->plan && $site->plan->features_description)
                                @foreach($site->plan->features_description as $label => $value)
                                    <tr class="border-top">
                                        <td class="text-muted pt-2">{{ $label }}:</td>
                                        <td class="text-end pt-2 text-dark">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>

                        @if($features['hide_contacts'] ?? false)
                            <div class="alert alert-soft-warning p-2 mt-3 mb-0">
                                <p class="fs-xs mb-0 text-center">
                                    <i class="fa fa-lock me-1"></i> Контакты скрыты (нужен Medium+)
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="block-content block-content-full">
                        <a href="{{ route('cabinet.billing.plans.index') }}" class="btn btn-sm btn-alt-primary w-100">
                            <i class="fa fa-sync-alt opacity-50 me-1"></i>
                            {{ $activeSub ? 'Улучшить тариф' : 'Активировать тариф' }}
                        </a>
                    </div>

                    <div class="block-content block-content-full bg-body-light py-2 text-center">
                        <span class="fs-xs text-muted">ID: {{ $site->id }} | Создан: {{ $site->created_at->format('d.m.Y') }}</span>
                    </div>
                </div>

                <div class="block block-rounded">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1">Виджеты на сайте</h4>
                            <p class="fs-sm text-muted mb-0">Управление всеми активными инструментами для {{ $site->domain }}</p>
                        </div>
                        <a href="{{ route('cabinet.sites.widgets.index', $site) }}" class="btn btn-alt-primary">
                            Управлять виджетами ({{ $site->widgets()->count() }})
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            $('#btn-verify').on('click', function() {
                let $btn = $(this);
                let $spinner = $btn.find('.spinner-border');

                // Визуальная блокировка
                $btn.prop('disabled', true);
                $spinner.removeClass('d-none');

                $.ajax({
                    url: "{{ route('cabinet.sites.verify.ajax', $site) }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            One.helpers('jq-notify', {type: 'success', icon: 'fa fa-check', message: response.message});
                            // Обновляем блок статуса на лету
                            $('#verification-status').html(`
                            <div class="alert alert-success d-flex align-items-center" role="alert">
                                <i class="fa fa-check-circle me-2"></i>
                                <div>Сайт подтвержден: <strong>${response.verified_at}</strong></div>
                            </div>
                        `);
                        } else {
                            One.helpers('jq-notify', {type: 'danger', icon: 'fa fa-times', message: response.message});
                        }
                    },
                    error: function() {
                        One.helpers('jq-notify', {type: 'danger', message: 'Произошла системная ошибка'});
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        $spinner.addClass('d-none');
                    }
                });
            });
        });
    </script>
@endpush

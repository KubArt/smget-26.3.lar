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
                    </div>
                </div>

            </div>



            <div class="col-xl-4">

                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Лимиты тарифа: {{ $site->plan->name ?? 'Бесплатный' }}</h3>
                    </div>
                    <div class="block-content">
                        <div class="mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fs-sm fw-semibold">Лиды (за месяц)</span>
                                <span class="fs-sm text-muted">{{ $limits['leads']['current'] }} / {{ $limits['leads']['limit'] }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $limits['leads']['is_exceeded'] ? 'bg-danger' : 'bg-primary' }}"
                                     role="progressbar"
                                     style="width: {{ ($limits['leads']['current'] / $limits['leads']['limit']) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Информация</h3>
                    </div>
                    <div class="block-content">
                        <table class="table table-borderless table-sm fs-sm">
                            <tbody>
                            <tr>
                                <td>Статус:</td>
                                <td class="text-end">
                                    @if($site->is_active) <span class="text-success">Активен</span> @else <span class="text-danger">Пауза</span> @endif
                                </td>
                            </tr>

                            {{-- Блок тарифа --}}
                            <tr class="border-top">
                                <td class="pt-2">Тариф:</td>
                                <td class="text-end pt-2">
                                    @if($site->activeSubscription)
                                        <span class="fw-bold text-primary">{{ $site->activeSubscription->plan->name }}</span>
                                    @else
                                        <span class="text-muted">Не оплачен</span>
                                    @endif
                                </td>
                            </tr>

                            @if($site->activeSubscription)
                                <tr>
                                    <td>Истекает:</td>
                                    <td class="text-end text-danger fw-medium">
                                        {{ $site->activeSubscription->expires_at->format('d.m.Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-center pt-2">
                                        <a class="btn btn-sm btn-alt-primary w-100" href="{{ route('cabinet.billing.plans.index') }}">
                                            Продлить или сменить
                                        </a>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="2" class="text-center pt-2">
                                        <a class="btn btn-sm btn-primary w-100" href="{{ route('cabinet.billing.plans.index') }}">
                                            <i class="fa fa-shopping-cart me-1"></i> Купить тариф
                                        </a>
                                    </td>
                                </tr>
                            @endif

                            <tr class="border-top">
                                <td class="pt-2">Добавлен:</td>
                                <td class="text-end pt-2">{{ $site->created_at->format('d.m.Y') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Ссылка на описание тарифов --}}
                    <div class="block-content block-content-full bg-body-light fs-xs text-center">
                        <a href="{{ route('cabinet.billing.plans.index') }}" class="fw-medium">
                            <i class="fa fa-info-circle me-1"></i> Подробнее о возможностях тарифов
                        </a>
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

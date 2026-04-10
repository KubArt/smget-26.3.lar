@extends('cabinet.layouts.cabinet')

@section('title', 'Тарифные планы')

@section('content')
    <div class="content">
        <div class="text-center py-5">
            <h2 class="fw-bold mb-2">Выберите подходящий тариф</h2>
            <p class="fs-lg fw-medium text-muted">Активируйте дополнительные возможности для ваших проектов</p>
        </div>

        <div class="row">
            @foreach($plans as $plan)
                @php $features = json_decode($plan->features, true); @endphp
                <div class="col-md-6 col-xl-3">
                    <div class="block block-rounded block-link-pop text-center d-flex flex-column h-100">
                        <div class="block-header">
                            <h3 class="block-title text-uppercase">{{ $plan->name }}</h3>
                        </div>
                        <div class="block-content bg-body-light">
                            <div class="py-3">
                                <p class="display-4 fw-bold mb-0">{{ number_format($plan->price, 0, '.', ' ') }} ₽</p>
                                <p class="text-muted">на {{ $plan->duration_days }} дней</p>
                            </div>
                        </div>
                        <div class="block-content flex-grow-1">
                            <div class="fs-sm py-2">
                                <p>{{ $plan->description }}</p>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fa fa-check text-success me-1"></i>
                                        Виджетов: <strong>{{ $features['widgets_count'] == -1 ? 'Безлимитно' : $features['widgets_count'] }}</strong>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fa fa-check text-success me-1"></i>
                                        Поддержка: <strong>{{ $features['support'] }}</strong>
                                    </li>
                                    @if(!($features['branding'] ?? true))
                                        <li class="mb-2">
                                            <i class="fa fa-check text-success me-1"></i> Без копирайта сервиса
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <div class="block-content block-content-full bg-body-light">
                            <button type="button" class="btn btn-primary w-100"
                                    onclick="selectPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }})">
                                Выбрать
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="modal fade" id="modal-subscribe" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('cabinet.billing.subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" id="modal_plan_id">
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Активация тарифа <span id="modal_plan_name"></span></h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content">
                            <div id="balance-alert" class="alert d-flex align-items-center" style="display: none !important;">
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Выберите сайт для подключения</label>
                                <select class="form-select" name="site_id" required>
                                    <option value="">-- Выберите сайт --</option>
                                    @foreach($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->domain }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body-light">
                            <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" id="subscribe-submit-btn" class="btn btn-sm btn-primary">Подтвердить оплату</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        function selectPlan(id, name, price) {
            const modalElement = document.getElementById('modal-subscribe');
            const submitBtn = document.getElementById('subscribe-submit-btn');
            const alertBox = document.getElementById('balance-alert');
            const modalPlanName = document.getElementById('modal_plan_name');
            const modalPlanId = document.getElementById('modal_plan_id');

            // Предварительная настройка
            modalPlanId.value = id;
            modalPlanName.innerText = name;
            alertBox.style.setProperty('display', 'none', 'important'); // Скрываем старое сообщение
            submitBtn.disabled = true; // Блокируем кнопку на время проверки

            // Показываем модалку сразу, чтобы юзер видел процесс
            const modal = new bootstrap.Modal(modalElement);
            modal.show();

            // AJAX запрос
            fetch('{{ route("cabinet.billing.check-balance") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ plan_id: id })
            })
                .then(response => response.json())
                .then(data => {
                    alertBox.style.setProperty('display', 'flex', 'important'); // Показываем блок

                    if (data.can_afford) {
                        alertBox.className = 'alert alert-success d-flex align-items-center animate__animated animate__fadeIn';
                        alertBox.innerHTML = `
                            <i class="fa fa-check-circle me-2"></i>
                            <div>Баланс в норме. Будет списано <strong>${data.price.toLocaleString()} ₽</strong>.</div>
                        `;
                        submitBtn.disabled = false;
                    } else {
                        alertBox.className = 'alert alert-danger d-flex align-items-center animate__animated animate__shakeX';
                        alertBox.innerHTML = `
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                <div>Недостаточно <strong>${data.diff.toLocaleString()} ₽</strong>.
                                <a href="{{ route('cabinet.billing.index') }}" class="fw-bold text-danger decoration-underline">Пополнить баланс?</a></div>
                            `;
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alertBox.style.setProperty('display', 'flex', 'important');
                    alertBox.className = 'alert alert-warning';
                    alertBox.innerText = 'Ошибка проверки баланса. Попробуйте обновить страницу.';
                });
        }

        // Находим форму внутри модалки
        const subscribeForm = document.querySelector('#modal-subscribe form');

        subscribeForm.onsubmit = function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('subscribe-submit-btn');
            const alertBox = document.getElementById('balance-alert');
            const formData = new FormData(this);

            // Визуальный процесс загрузки
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Активация...';

            fetch('{{ route("cabinet.billing.subscribe") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Успех
                        alertBox.className = 'alert alert-success d-flex align-items-center';
                        alertBox.innerHTML = `<i class="fa fa-check-circle me-2"></i> <div>${data.message}</div>`;

                        // Обновляем баланс в шапке (если есть ID или класс)
                        const balanceElement = document.querySelector('.header-balance-value');
                        if(balanceElement) balanceElement.innerText = data.new_balance;

                        setTimeout(() => {
                            window.location.href = '{{ route("cabinet.billing.index") }}';
                        }, 1500);
                    } else {
                        // Ошибка
                        alertBox.className = 'alert alert-danger d-flex align-items-center';
                        alertBox.innerHTML = `<i class="fa fa-times-circle me-2"></i> <div>${data.message}</div>`;
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'Подтвердить оплату';
                    }
                })
                .catch(error => {
                    alertBox.className = 'alert alert-warning';
                    alertBox.innerText = 'Системная ошибка. Попробуйте позже.';
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Подтвердить оплату';
                });
        };

    </script>
@endpush

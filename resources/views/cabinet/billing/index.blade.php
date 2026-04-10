@extends('cabinet.layouts.cabinet')

@section('title', 'Мои финансы')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-6 col-xl-4">
                <div class="block block-rounded d-flex flex-column h-100 mb-0">
                    <div class="block-content block-content-full flex-grow-1 d-flex justify-content-between align-items-center">
                        <dl class="mb-0">
                            <dt class="fs-3 fw-bold">{{ number_format(auth()->user()->balance, 0, '.', ' ') }} ₽</dt>
                            <dd class="fs-sm fw-medium text-muted mb-0">Текущий баланс</dd>
                        </dl>
                        <div class="item item-rounded-lg bg-body-light">
                            <i class="fa fa-wallet fs-3 text-primary"></i>
                        </div>
                    </div>
                    <div class="bg-body-light rounded-bottom">
                        <a class="block-content block-content-full block-content-sm fs-sm fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-voucher">
                            <span>Активировать ваучер</span>
                            <i class="fa fa-arrow-right ms-1 opacity-25 link-primary"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-8">
                <div class="block block-rounded h-100 mb-0">
                    <div class="block-content block-content-full">
                        <div class="row text-center">
                            <div class="col-sm-4 py-2">
                                <div class="fs-3 fw-bold">{{ auth()->user()->sites()->count() }}</div>
                                <div class="fs-sm fw-medium text-muted text-uppercase">Всего сайтов</div>
                            </div>
                            <div class="col-sm-4 py-2 border-start">
                                <div class="fs-3 fw-bold text-success">{{ auth()->user()->sites()->whereHas('activeSubscription')->count() }}</div>
                                <div class="fs-sm fw-medium text-muted text-uppercase">С тарифом</div>
                            </div>
                            <div class="col-sm-4 py-2 border-start">
                                <a class="btn btn-alt-primary mt-1" href="{{ route('cabinet.billing.plans.index') }}">
                                    <i class="fa fa-shopping-cart me-1"></i> Купить тариф
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="block block-rounded mt-4">
            <div class="block-header block-header-default">
                <h3 class="block-title">История операций</h3>
            </div>
            <div class="block-content">
                <table class="table table-striped table-hover table-vcenter">
                    <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Описание</th>
                        <th class="text-center">Тип</th>
                        <th class="d-none d-sm-table-cell">Дата</th>
                        <th class="text-end" style="width: 15%;">Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="fs-sm">{{ $transaction->description }}</td>
                            <td class="text-center">
                                <span class="badge {{ $transaction->amount > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $transaction->amount > 0 ? 'Пополнение' : 'Списание' }}
                                </span>
                            </td>
                            <td class="d-none d-sm-table-cell fs-sm text-muted">
                                {{ $transaction->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="text-end fw-semibold {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 0, '.', ' ') }} ₽
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">История операций пуста</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-voucher" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="form-activate-voucher">
                    @csrf
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Активация ваучера</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="block-content fs-sm" id="voucher-input-container">
                            <div class="mb-4">
                                <label class="form-label" for="code">Введите код ваучера</label>
                                <input type="text" class="form-control form-control-lg" id="code" name="code" placeholder="XXXX-XXXX-XXXX" required>
                            </div>
                            <div id="voucher-error" class="alert alert-danger d-none"></div>
                        </div>

                        <div class="block-content fs-sm d-none" id="voucher-success-container">
                            <div class="text-center py-3">
                                <i class="fa fa-3x fa-check-circle text-success mb-3"></i>
                                <h4 class="fw-bold">Успешно активировано!</h4>
                                <ul class="list-group list-group-flush text-start" id="activated-features-list">
                                </ul>
                            </div>
                        </div>

                        <div class="block-content block-content-full text-end bg-body-light" id="voucher-footer">
                            <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-dismiss="modal">Закрыть</button>
                            <button type="submit" class="btn btn-sm btn-primary" id="btn-submit-voucher">Активировать</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.getElementById('form-activate-voucher').onsubmit = function(e) {
            e.preventDefault();

            const btn = document.getElementById('btn-submit-voucher');
            const codeInput = document.getElementById('code');
            const errorBox = document.getElementById('voucher-error');
            const inputContainer = document.getElementById('voucher-input-container');
            const successContainer = document.getElementById('voucher-success-container');
            const featuresList = document.getElementById('activated-features-list');
            const footer = document.getElementById('voucher-footer');

            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            errorBox.classList.add('d-none');

            fetch('{{ route("cabinet.billing.voucher.activate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ code: codeInput.value })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 1. Скрываем ввод, показываем успех
                        inputContainer.classList.add('d-none');
                        successContainer.classList.remove('d-none');

                        // 2. Наполняем список функций
                        featuresList.innerHTML = '';
                        data.data.items.forEach(item => {
                            featuresList.innerHTML += `<li class="list-group-item fs-sm"><i class="fa fa-check text-success me-2"></i> ${item}</li>`;
                        });

                        // 3. Обновляем баланс в UI (в хедере и на странице)
                        document.querySelectorAll('.header-balance-value, .current-balance-display').forEach(el => {
                            el.innerText = data.data.balance;
                        });

                        // 4. Меняем кнопку
                        btn.classList.add('d-none');
                        const closeBtn = document.createElement('button');
                        closeBtn.className = 'btn btn-sm btn-success';
                        closeBtn.innerText = 'Отлично';
                        closeBtn.onclick = () => window.location.reload(); // Перезагрузка для обновления истории
                        footer.appendChild(closeBtn);

                    } else {
                        errorBox.innerText = data.message;
                        errorBox.classList.remove('d-none');
                        btn.disabled = false;
                        btn.innerText = 'Активировать';
                    }
                })
                .catch(error => {
                    errorBox.innerText = 'Системная ошибка. Попробуйте еще раз.';
                    errorBox.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerText = 'Активировать';
                });
        };
    </script>
@endpush

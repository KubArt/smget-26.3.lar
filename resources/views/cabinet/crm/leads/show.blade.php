@extends('cabinet.layouts.cabinet')

@php
    $labels = [
        'utm_source' => 'Источник',
        'utm_medium' => 'Тип трафика',
        'page_url'   => 'Страница захвата',
        'phone'      => 'Телефон',
        'name'      => 'Имя',
        // добавьте свои...
    ];
@endphp

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Информация о лиде</h3>
                    </div>
                    <div class="block-content pb-3">
                        <p><strong>Источник:</strong> {{ $lead->utm_source ?? 'Прямой заход' }} / {{ $lead->utm_campaign ?? '-' }}</p>
                        <p><strong>Страница:</strong> <small class="text-primary">{{ $lead->page_url }}</small></p>
                        <hr>
                        <p><strong>Данные формы:</strong></p>
                        @if($lead->form_data)
                            @foreach($lead->form_data as $key => $value)
                                <div class="mb-1">
                                    <span class="text-muted">{{ $labels[$key] ?? $key }}:</span>
                                    <span class="fw-semibold">
                                        {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted fs-sm">Дополнительные данные отсутствуют</p>
                        @endif
                    </div>
                </div>
                @php
                    $daysLeft = null;
                    if ($lead->prize && $lead->prize->expires_at) {
                        $daysLeft = now()->diffInDays($lead->prize->expires_at, false);
                    }
                @endphp
                @if($lead->prize)
                    <div class="block block-rounded border-start border-danger border-4 shadow-sm">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">
                                <i class="fa fa-gift me-2 text-danger"></i>Выигранный приз
                            </h3>
                            <div class="block-options">
                                @if($lead->prize->is_used)
                                    <span class="badge bg-success-light text-success fw-bold">Использован</span>
                                @elseif($daysLeft !== null && $daysLeft < 0)
                                    <span class="badge bg-flat-light text-flat fw-bold">Истек</span>
                                @else
                                    <span class="badge bg-warning-light text-warning fw-bold">Активен</span>
                                @endif
                            </div>
                        </div>
                        <div class="block-content">
                            <div class="row items-push">
                                <div class="col-sm-12">
                                    <div class="fw-bold text-dark fs-lg mb-1">{{ $lead->prize->name }}</div>
                                    <div class="text-muted fs-sm mb-3">{{ $lead->prize->description }}</div>
                                    {{-- Секция с промокодом --}}
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-body-dark rounded-3 mb-3">
                                        <div>
                                            <span class="fs-xs fw-bold text-uppercase text-muted d-block">Промокод</span>
                                            <strong class="fs-4 text-primary font-monospace">{{ $lead->prize->code }}</strong>
                                        </div>
                                        <button class="btn btn-sm btn-alt-secondary" onclick="navigator.clipboard.writeText('{{ $lead->prize->code }}')">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>

                                    {{-- Информация о сроках --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="fs-xs text-muted text-uppercase fw-bold">Выдан</div>
                                            <div class="fs-sm fw-semibold">{{ $lead->prize->created_at->format('d.m.Y') }}</div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div class="fs-xs text-muted text-uppercase fw-bold">Истекает</div>
                                            @if($lead->prize->expires_at)
                                                <div class="fs-sm fw-semibold {{ $daysLeft < 3 ? 'text-danger' : '' }}">
                                                    {{ $lead->prize->expires_at->format('d.m.Y') }}
                                                </div>
                                            @else
                                                <div class="fs-sm fw-semibold text-muted">∞</div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Счетчик дней --}}
                                    @if($daysLeft !== null && !$lead->prize->is_used)
                                        <div class="alert {{ $daysLeft < 3 ? 'alert-danger' : 'alert-info' }} d-flex align-items-center justify-content-between py-2 px-3" role="alert">
                                            <div class="flex-grow-1 fs-sm">
                                                <i class="fa fa-clock me-2"></i>
                                                @if($daysLeft > 0)
                                                    Осталось дней: <strong>{{ $daysLeft }}</strong>
                                                @elseif($daysLeft == 0)
                                                    <strong>Истекает сегодня!</strong>
                                                @else
                                                    <span class="fw-bold">Срок действия истек</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Кнопка действия --}}
                        <div class="block-content block-content-full block-content-sm bg-body-light text-center">
                            @if(!$lead->prize->is_used)
                                <button type="button" class="btn btn-alt-danger w-100 py-2" onclick="alert('Логика использования ваучера будет добавлена позже')">
                                    <i class="fa fa-check-circle me-1"></i> Использовать ваучер
                                </button>
                            @else
                                <button type="button" class="btn btn-light w-100 py-2 disabled">
                                    <i class="fa fa-history me-1"></i> Использован {{ $lead->prize->used_at ? $lead->prize->used_at->format('d.m.Y') : '' }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Карточка клиента</h3>
                    </div>
                    <div class="block-content pb-3 text-center">
                        <h4 class="mb-1">{{ $lead->client->name }}</h4>
                        <p class="text-muted">{{ $lead->phone }}</p>
                        <a href="{{ route('cabinet.crm.clients.show', $lead->client_id) }}" class="btn btn-sm btn-alt-primary">История всех обращений</a>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="block block-rounded border-start border-4 border-primary">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Этап воронки</h3>
                    </div>
                    <div class="block-content pb-3">
                        <form action="{{ route('cabinet.crm.leads.stage.update', $lead->id) }}" method="POST" id="stage-form">
                            @csrf
                            <div class="mb-3">
                                <select name="stage_code" id="stage-select" class="form-select form-select-sm">
                                    @foreach(\App\Models\Crm\FunnelStage::where('site_id', $lead->site_id)->orderBy('sort_order')->get() as $stage)
                                        <option value="{{ $stage->code }}"
                                                data-rejected="{{ in_array($stage->code, ['rejected', 'lost', 'refusal']) ? 'true' : 'false' }}"
                                            {{ $lead->status == $stage->code ? 'selected' : '' }}>
                                            {{ $stage->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="rejection-comment-block" style="display: none;" class="mb-3">
                                <label class="form-label fs-xs">Причина отказа <span class="text-danger">*</span></label>
                                <textarea name="comment" id="rejection-comment" class="form-control form-control-sm" rows="2" placeholder="Например: слишком дорого, ушли в другую клинику..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-sm btn-alt-primary w-100">
                                <i class="fa fa-check me-1"></i> Сохранить
                            </button>
                        </form>
                    </div>
                </div>
                @push('js')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const select = document.getElementById('stage-select');
                            const commentBlock = document.getElementById('rejection-comment-block');
                            const commentInput = document.getElementById('rejection-comment');

                            function toggleComment() {
                                const isRejected = select.options[select.selectedIndex].getAttribute('data-rejected') === 'true';
                                if (isRejected) {
                                    commentBlock.style.display = 'block';
                                    commentInput.setAttribute('required', 'required');
                                } else {
                                    commentBlock.style.display = 'none';
                                    commentInput.removeAttribute('required');
                                }
                            }

                            select.addEventListener('change', toggleComment);
                            toggleComment(); // Проверка при загрузке
                        });
                    </script>
                @endpush


                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">История перемещений</h3>
                    </div>
                    <div class="block-content">
                        <ul class="timeline timeline-alt timeline-sm">
                            @forelse($lead->stageHistories()->with('user')->latest()->get() as $history)
                                <li class="timeline-event">
                                    <div class="timeline-event-icon bg-smooth">
                                        <i class="fa fa-exchange-alt"></i>
                                    </div>
                                    <div class="timeline-event-block">
                                        <div class="timeline-event-time">{{ $history->created_at->format('d.m H:i') }}</div>
                                        <div class="fw-semibold">
                                            @php
                                                $from = \App\Models\Crm\FunnelStage::where('code', $history->from_stage)->where('site_id', $lead->site_id)->first();
                                                $to = \App\Models\Crm\FunnelStage::where('code', $history->to_stage)->where('site_id', $lead->site_id)->first();
                                            @endphp

                                            <span class="badge" style="background-color: {{ $from->color ?? '#ebebeb' }}; color: #333;">
                                                {{ $from->name ?? 'Начало' }}
                                            </span>
                                            <i class="fa fa-long-arrow-alt-right mx-2 text-muted"></i>
                                            <span class="badge" style="background-color: {{ $to->color ?? '#ebebeb' }}; color: #333;">
                                                {{ $to->name ?? 'Неизвестно' }}
                                            </span>
                                        </div>
                                        <div class="fs-xs text-muted">
                                            Изменил: {{ $history->user->name ?? 'Система' }}
                                        </div>
                                        @if($history->comment)
                                            <div class="fs-sm mt-1 text-italic">"{{ $history->comment }}"</div>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <p class="text-muted fs-sm">История перемещений пуста</p>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Заметки</h3>
                    </div>
                    <div class="block-content">
                        <ul class="nav-items push">
                            @foreach($lead->notes as $note)
                                <li>
                                    <div class="d-flex py-2">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $note->user->name }}</div>
                                            <div class="fs-sm">{{ $note->note }}</div>
                                            <div class="fs-xs text-muted">{{ $note->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <form action="{{ route('cabinet.crm.leads.notes.store', $lead->id) }}" method="POST" class="pb-3">
                            @csrf
                            <textarea name="note" class="form-control mb-2" rows="2" placeholder="Добавить заметку..."></textarea>
                            <button class="btn btn-sm btn-primary">Отправить</button>
                        </form>
                    </div>
                </div>

                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Напоминания</h3>
                    </div>
                    <div class="block-content">
                        @foreach($lead->tasks as $task)
                            <div class="d-flex align-items-center mb-3 p-2 bg-body-light rounded">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold {{ $task->due_date->isPast() ? 'text-danger' : '' }}">
                                        {{ $task->title }}
                                    </div>
                                    <div class="fs-xs text-muted">Срок: {{ $task->due_date->format('d.m.Y H:i') }}</div>
                                </div>
                                <span class="badge bg-secondary">{{ $task->status }}</span>
                            </div>
                        @endforeach

                        <button class="btn btn-sm btn-alt-primary mb-3" data-bs-toggle="collapse" data-bs-target="#addTask">
                            + Новое напоминание
                        </button>

                        <div class="collapse" id="addTask">
                            <form action="{{ route('cabinet.crm.leads.tasks.store', $lead->id) }}" method="POST" class="pb-3 border-top pt-3">
                                @csrf
                                <input type="text" name="title" class="form-control mb-2" placeholder="Что нужно сделать?" required>
                                <input type="datetime-local" name="due_date" class="form-control mb-2" required>
                                <button class="btn btn-sm btn-success">Создать</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

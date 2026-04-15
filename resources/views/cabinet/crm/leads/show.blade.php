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

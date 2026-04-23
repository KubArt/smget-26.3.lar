@extends('cabinet.layouts.cabinet')

@section('content')
    @php
        $isExceeded = app(App\Services\SubscriptionService::class)->isLeadsLimitExceeded($client->site);
    @endphp
    <div class="content">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-2">
                    Карточка пациента: {{ $client->last_name }} {{ $client->name }}
                </h1>
                <p class="text-muted mb-0">Общая история взаимодействий по всем сайтам</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="block block-rounded">
                    <div class="block-content block-content-full text-center bg-gray-light">
                        <div class="item item-circle bg-white text-primary mx-auto my-3">
                            <i class="fa fa-user fa-2x"></i>
                        </div>
                        <h4 class="mb-1">{{ $client->last_name }} {{ $client->name }}</h4>

                        @if($client->is_blocked)
                            <small class="fs-xs text-warning"><br>Контакт не доступен из-за превышения лимита по тарифу</small>
                        @else
                            <p class="text-muted">{{ $client->phone }}</p>
                        @endif
                    </div>
                    <div class="block-content">
                        <table class="table table-borderless table-sm fs-sm">
                            <tbody>
                            <tr>
                                <td class="fw-semibold" style="width: 30%;">Email:</td>
                                <td>{{ $client->email ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Сайт:</td>
                                <td><span class="badge bg-info-light text-info">{{ $client->site->name }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Создан:</td>
                                <td>{{ $client->created_at->format('d.m.Y') }}</td>
                            </tr>

                            <tr>
                                <td class="fw-semibold"></td>
                                <td>
                                    <a href="{{ route('cabinet.crm.clients.force-delete', $client->id) }}"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Вы уверены? Это ПОЛНОЕ удаление без возможности восстановления!')">
                                        <i class="fa fa-trash"></i> Техническая очистка
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-boxes me-1"></i> История подарков
                        </h3>
                    </div>
                    <div class="block-content">
                        @if($client->prizes->count() > 0)
                            <div class="list-group list-group-flush border-bottom mb-3">
                                @foreach($client->prizes as $prize)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div class="fw-bold fs-sm text-dark">{{ $prize->name }}</div>
                                            @if($prize->is_used)
                                                <span class="badge bg-success-light text-success">Использован</span>
                                            @elseif($prize->expires_at && $prize->expires_at->isPast())
                                                <span class="badge bg-flat-light text-flat">Истек</span>
                                            @else
                                                <span class="badge bg-warning-light text-warning">Активен</span>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <code class="text-primary fw-bold">{{ $prize->code }}</code>
                                            <span class="fs-xs text-muted">
                                @if($prize->expires_at)
                                                    до {{ $prize->expires_at->format('d.m.Y') }}
                                                @else
                                                    бессрочно
                                                @endif
                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fa fa-gift fa-2x text-gray-light mb-2"></i>
                                <p class="fs-sm text-muted">Подарков пока нет</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title text-warning">
                            <i class="fa fa-exclamation-triangle me-1"></i> Важные заметки
                        </h3>
                    </div>
                    <div class="block-content">
                        @forelse($client->notes as $note)
                            <div class="p-2 mb-2 bg-warning-light rounded border-start border-warning border-4">
                                <div class="fs-sm">{{ $note->note }}</div>
                                <div class="fs-xs text-muted mt-1">
                                    {{ $note->created_at->format('d.m.Y') }} — {{ $note->user->name }}
                                </div>
                            </div>
                        @empty
                            <p class="fs-sm text-muted">Критических заметок нет.</p>
                        @endforelse

                        <form action="{{ route('cabinet.crm.clients.notes.store', $client->id) }}" method="POST" class="py-3 border-top mt-3">
                            @csrf
                            <textarea name="note" class="form-control form-control-sm mb-2" rows="2" placeholder="Добавить особенность пациента..."></textarea>
                            <button type="submit" class="btn btn-sm btn-alt-warning w-100">Сохранить</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="block block-rounded">
                    <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-history">История обращений</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tasks">Напоминания</button>
                        </li>
                    </ul>
                    <div class="block-content tab-content overflow-hidden">
                        <div class="tab-pane active" id="tab-history" role="tabpanel">
                            <ul class="timeline timeline-alt py-0">
                                @foreach($client->leads->sortByDesc('created_at') as $lead)
                                    <li class="timeline-event">
                                        <div class="timeline-event-icon bg-default">
                                            <i class="fa fa-comment-alt"></i>
                                        </div>
                                        <div class="timeline-event-block">
                                            <div class="timeline-event-time fs-xs">{{ $lead->created_at->format('d.m.Y H:i') }}</div>
                                            <div class="fw-semibold">Лид #{{ $lead->id }} ({{ $lead->widget->custom_name ?? 'Виджет' }})</div>
                                            <div class="fs-sm mb-2">
                                                Статус: <span class="badge" style="background-color: {{ $lead->funnelStage->color ?? '#ccc' }}">
                                            {{ $lead->funnelStage->name ?? 'Новый' }}
                                        </span>
                                            </div>
                                            @if(!$lead->is_blocked)
                                                <div class="p-2 bg-body-light rounded fs-sm mb-2">
                                                    @foreach($lead->form_data as $label => $value)
                                                        <strong>{{ $label }}:</strong> {{ $value }}<br>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <a class="btn btn-sm btn-alt-secondary" href="{{ route('cabinet.crm.leads.show', $lead->id) }}">
                                                Подробнее о лиде
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="tab-pane" id="tab-tasks" role="tabpanel">
                            <div class="list-group push">
                                @php $hasTasks = false; @endphp
                                @foreach($client->leads as $lead)
                                    @foreach($lead->tasks as $task)
                                        @php $hasTasks = true; @endphp
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold {{ $task->due_date->isPast() && $task->status == 'pending' ? 'text-danger' : '' }}">
                                                    {{ $task->title }}
                                                </div>
                                                <div class="fs-xs text-muted">Срок: {{ $task->due_date->format('d.m.Y H:i') }} (Лид #{{ $lead->id }})</div>
                                            </div>
                                            <span class="badge bg-{{ $task->status == 'completed' ? 'success' : 'primary' }}">
                                            {{ $task->status }}
                                        </span>
                                        </div>
                                    @endforeach
                                @endforeach
                                @if(!$hasTasks)
                                    <div class="text-center py-4 text-muted">Нет запланированных дел.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0">Задачи и напоминания</h1>
            <div class="btn-group">
                <a href="{{ route('cabinet.crm.tasks.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
                   class="btn btn-sm {{ request('status', 'pending') == 'pending' ? 'btn-primary' : 'btn-alt-primary' }}">
                    В работе
                </a>
                <a href="{{ route('cabinet.crm.tasks.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
                   class="btn btn-sm {{ request('status') == 'completed' ? 'btn-primary' : 'btn-alt-primary' }}">
                    Выполнено
                </a>
                <a href="{{ route('cabinet.crm.tasks.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                   class="btn btn-sm {{ request('status') == 'all' ? 'btn-primary' : 'btn-alt-primary' }}">
                    Все
                </a>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-content block-content-full border-bottom">
                <form action="{{ route('cabinet.crm.tasks.index') }}" method="GET" class="row g-3 align-items-center">
                    <input type="hidden" name="status" value="{{ request('status', 'pending') }}">

                    <div class="col-auto">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('cabinet.crm.tasks.index', array_merge(request()->query(), ['assigned_to' => 'all'])) }}"
                               class="btn {{ request('assigned_to', 'all') == 'all' ? 'btn-dark' : 'btn-alt-dark' }}">Все</a>
                            <a href="{{ route('cabinet.crm.tasks.index', array_merge(request()->query(), ['assigned_to' => auth()->id()])) }}"
                               class="btn {{ request('assigned_to') == auth()->id() ? 'btn-dark' : 'btn-alt-dark' }}">Мои</a>
                        </div>
                    </div>

                    <div class="col-auto">
                        <select name="date_range" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" {{ request('date_range') == 'all' ? 'selected' : '' }}>За всё время</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Сегодня</option>
                            <option value="tomorrow" {{ request('date_range') == 'tomorrow' ? 'selected' : '' }}>Завтра</option>
                            <option value="7_days" {{ request('date_range') == '7_days' ? 'selected' : '' }}>На 7 дней</option>
                            <option value="30_days" {{ request('date_range') == '30_days' ? 'selected' : '' }}>На 30 дней</option>
                        </select>
                    </div>

                    <div class="col-auto">
                        <input type="date" name="date_custom" class="form-control form-select-sm"
                               value="{{ request('date_custom') }}" onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            <div class="block-content">
                <table class="table table-sm table-vcenter">
                    <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Задача</th>
                        <th>Клиент</th>
                        @if(request('assigned_to', 'all') == 'all')
                            <th>Ответственный</th>
                        @endif
                        <th>Срок</th>
                        <th class="text-center">Сайт</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tasks as $task)
                        <tr class="{{ $task->status == 'completed' ? 'opacity-50' : '' }}">
                            <td>
                                <form action="{{ route('cabinet.crm.tasks.toggle', $task) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-{{ $task->status == 'completed' ? 'success' : 'secondary' }} p-0">
                                        <i class="fa fa-{{ $task->status == 'completed' ? 'check-circle' : 'circle' }} fa-2x"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $task->title }}</div>
                                <div class="fs-xs text-muted">{{ $task->description }}</div>
                            </td>
                            <td>
                                <a href="{{ route('cabinet.crm.leads.show', $task->lead_id) }}" class="fw-medium">
                                    {{ $task->lead->client->name ?? 'Лид #'.$task->lead_id }}
                                </a>
                            </td>
                            @if(request('assigned_to', 'all') == 'all')
                                <td>
                                    <small class="badge bg-alt-primary text-primary">{{ $task->assignedTo->name ?? '—' }}</small>
                                </td>
                            @endif
                            <td>
                                <span class="fs-sm {{ $task->due_date->isPast() && $task->status == 'pending' ? 'text-danger fw-bold' : '' }}">
                                    {{ $task->due_date->format('d.m.Y H:i') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-light text-secondary">{{ $task->lead->site->name }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4">Задач не найдено</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="p-3">
                    {{ $tasks->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

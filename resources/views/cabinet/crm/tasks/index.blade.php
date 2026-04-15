@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-2">Мои напоминания</h1>
                <h2 class="h6 fw-medium text-muted mb-0">Список дел по лидам и клиентам</h2>
            </div>
        </div>

        <div class="row items-push">
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-shadow text-center {{ request('status', 'pending') == 'pending' ? 'block-content-full bg-primary-lighter' : '' }}"
                   href="{{ route('cabinet.crm.tasks.index', ['status' => 'pending']) }}">
                    <div class="block-content block-content-full">
                        <div class="fs-2 fw-semibold text-primary">
                            <i class="fa fa-clock"></i>
                        </div>
                    </div>
                    <div class="block-content py-2 bg-body-light">
                        <p class="fw-medium fs-sm text-muted mb-0">В работе</p>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a class="block block-rounded block-link-shadow text-center {{ request('status') == 'completed' ? 'block-content-full bg-success-light' : '' }}"
                   href="{{ route('cabinet.crm.tasks.index', ['status' => 'completed']) }}">
                    <div class="block-content block-content-full">
                        <div class="fs-2 fw-semibold text-success">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="block-content py-2 bg-body-light">
                        <p class="fw-medium fs-sm text-muted mb-0">Выполнено</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-content">
                <table class="table table-vcenter">
                    <thead>
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Что сделать</th>
                        <th>Пациент / Лид</th>
                        <th>Срок</th>
                        <th class="text-center">Сайт</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tasks as $task)
                        <tr class="{{ $task->due_date->isPast() && $task->status == 'pending' ? 'table-danger' : '' }}">
                            <td class="text-center">
                                <form action="{{ route('cabinet.crm.tasks.toggle', $task->id) }}" method="POST">
                                    @csrf
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                               style="cursor: pointer; width: 24px; height: 24px;"
                                               {{ $task->status == 'completed' ? 'checked' : '' }}
                                               onchange="this.form.submit()">
                                    </div>
                                </form>
                            </td>
                            <td>
                                <div class="fw-semibold {{ $task->status == 'completed' ? 'text-decoration-line-through text-muted' : '' }}">
                                    {{ $task->title }}
                                </div>
                                @if($task->description)
                                    <div class="fs-xs text-muted">{{ $task->description }}</div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('cabinet.crm.leads.show', $task->lead_id) }}" class="fw-medium">
                                    {{ $task->lead->client->name ?? 'Лид #'.$task->lead_id }}
                                </a>
                                <div class="fs-xs text-muted">{{ $task->lead->phone }}</div>
                            </td>
                            <td>
                            <span class="fs-sm {{ $task->due_date->isPast() && $task->status == 'pending' ? 'fw-bold text-danger' : '' }}">
                                {{ $task->due_date->format('d.m.Y H:i') }}
                            </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-light text-secondary">{{ $task->lead->site->name }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Задач на текущий период нет</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="pb-3">
                    {{ $tasks->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

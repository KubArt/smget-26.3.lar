@extends('cabinet.layouts.cabinet')

@section('title', 'Панель управления | CRM')

@section('content')
    <div class="content">
        <div class="block block-rounded mb-4 shadow-sm">
            <div class="block-content py-3">
                <form action="{{ route('cabinet.dashboard') }}" method="GET" id="dashboard-filter">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fs-sm fw-bold text-uppercase text-muted">Выберите проект</label>
                            <select name="site_id" class="form-select" onchange="this.form.submit()">
                                <option value="">📊 Все проекты (сводная статистика)</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 text-md-end mt-3 mt-md-0">
                            <div class="text-muted fs-sm">Обновлено: <span class="fw-semibold">{{ now()->format('H:i') }}</span></div>
                            <a class="btn btn-sm btn-alt-primary mt-2" href="{{ route('cabinet.sites.create') }}">
                                <i class="fa fa-plus me-1"></i> Добавить сайт
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-6 col-lg-3">
                <div class="block block-rounded shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <p class="fs-sm fw-semibold text-muted text-uppercase mb-1">Всего лидов</p>
                            <p class="fs-2 fw-bold mb-0">{{ $total_leads ?? 0 }}</p>
                        </div>
                        <div class="item item-rounded bg-body-light">
                            <i class="fa fa-users fs-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="block block-rounded shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <p class="fs-sm fw-semibold text-muted text-uppercase mb-1">Новые (New)</p>
                            <p class="fs-2 fw-bold text-success mb-0">{{ $new_leads ?? 0 }}</p>
                        </div>
                        <div class="item item-rounded bg-body-light">
                            <i class="fa fa-user-plus fs-3 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="block block-rounded shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <p class="fs-sm fw-semibold text-muted text-uppercase mb-1">Задачи (Today)</p>
                            <p class="fs-2 fw-bold text-warning mb-0">{{ count($urgent_tasks ?? []) }}</p>
                        </div>
                        <div class="item item-rounded bg-body-light">
                            <i class="fa fa-clock fs-3 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="block block-rounded shadow-sm">
                    <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                        <div>
                            <p class="fs-sm fw-semibold text-muted text-uppercase mb-1">Проектов</p>
                            <p class="fs-2 fw-bold text-info mb-0">{{ $sites->count() }}</p>
                        </div>
                        <div class="item item-rounded bg-body-light">
                            <i class="fa fa-globe fs-3 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-7">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">🔥 Требуют внимания</h3>
                    </div>
                    <div class="block-content pb-3">
                        <table class="table table-vcenter table-hover fs-sm">
                            <thead>
                            <tr>
                                <th>Лид / Клиент</th>
                                <th>Задача</th>
                                <th class="text-end">Срок</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($urgent_tasks ?? [] as $task)
                                <tr>
                                    <td>
                                        <a class="fw-semibold" href="{{ route('cabinet.crm.leads.show', $task->lead_id) }}">
                                            {{ $task->lead->client->full_name ?? $task->lead->phone }}
                                        </a>
                                    </td>
                                    <td>{{ $task->title }}</td>
                                    <td class="text-end text-danger fw-bold">
                                        {{ $task->due_date->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Нет просроченных задач. Отличная работа!</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">📉 Почему уходят клиенты?</h3>
                    </div>
                    <div class="block-content">
                        <ul class="list-group list-group-flush pb-3">
                            @php
                                // Берем последние отказы из истории
                                $rejections = \App\Models\Crm\LeadStageHistory::where('to_stage', 'rejected')
                                    ->with('lead')
                                    ->latest()
                                    ->limit(5)
                                    ->get();
                            @endphp
                            @forelse($rejections as $rejected)
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">{{ $rejected->lead->phone }}</span>
                                        <span class="text-muted fs-xs">{{ $rejected->created_at->format('d.m') }}</span>
                                    </div>
                                    <div class="text-italic fs-sm text-danger mt-1">
                                        «{{ $rejected->comment ?? 'Причина не указана' }}»
                                    </div>
                                </li>
                            @empty
                                <p class="text-center text-muted fs-sm py-3">Отказов за последнее время нет</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Управление лидами</h3>
            </div>
            <div class="block-content bg-body-light">
                <form action="{{ route('cabinet.crm.leads.index') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select name="site_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Все сайты</option>
                                @foreach($sites as $s)
                                    <option value="{{ $s->id }}" {{ request('site_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="От">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="До">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Применить</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="block-content">
                <table class="table table-hover table-vcenter">
                    <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Клиент / Телефон</th>
                        <th>Сайт</th>
                        <th>Статус</th>
                        <th class="text-center">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($leads as $lead)
                        <tr>
                            <td>{{ $lead->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $lead->client->name ?? 'Новый лид' }}</div>
                                <div class="fs-sm text-muted">{{ $lead->phone }}</div>
                            </td>
                            <td><span class="badge bg-primary-light text-primary">{{ $lead->site->name }}</span></td>
                            <td>
                             <span class="badge" style="background-color: {{ $lead->funnelStage->color ?? '#ccc' }}">
                                {{ $lead->funnelStage->name ?? 'Новый' }}
                             </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('cabinet.crm.leads.show', $lead->id) }}" class="btn btn-sm btn-alt-secondary">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $leads->links() }}
            </div>
        </div>
    </div>
@endsection

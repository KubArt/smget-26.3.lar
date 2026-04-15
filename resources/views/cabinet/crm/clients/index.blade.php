@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">База клиентов</h3>
            </div>
            <div class="block-content bg-body-light">
                <form action="{{ route('cabinet.crm.clients.index') }}" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Поиск по имени или телефону..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Искать</button>
                    </div>
                </form>
            </div>
            <div class="block-content">
                <table class="table table-striped table-vcenter">
                    <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Телефон / Email</th>
                        <th class="text-center">Обращений</th>
                        <th>Дата создания</th>
                        <th class="text-center">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td class="fw-semibold">{{ $client->last_name }} {{ $client->name }}</td>
                            <td>
                                <div>{{ $client->phone }}</div>
                                <div class="fs-xs text-muted">{{ $client->email }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-primary">{{ $client->leads_count }}</span>
                            </td>
                            <td>{{ $client->created_at->format('d.m.Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('cabinet.crm.clients.show', $client->id) }}" class="btn btn-sm btn-alt-secondary">
                                    <i class="fa fa-user-edit"></i> Профиль
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $clients->links() }}
            </div>
        </div>
    </div>
@endsection

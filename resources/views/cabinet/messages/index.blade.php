@extends('cabinet.layouts.cabinet')

@section('title', 'Входящие сообщения')

@section('content')
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Входящие уведомления</h3>
        </div>
        <div class="block-content">
            <table class="table table-hover table-vcenter">
                <thead>
                <tr>
                    <th style="width: 50px;"></th>
                    <th>Заголовок</th>
                    <th>Отправитель</th>
                    <th class="d-none d-sm-table-cell">Важность</th>
                    <th class="d-none d-md-table-cell text-end">Дата</th>
                </tr>
                </thead>
                <tbody>
                @foreach($notifications as $notification)
                    @php
                        // Декодируем данные, если они не были автоматически приведены к массиву
                        $data = $notification->data;
                    @endphp
                    <tr style="cursor: pointer;" onclick="window.location='{{ route('cabinet.messages.show', $notification->id) }}'">
                        <td class="text-center">
                            {{-- Проверка: если сообщение не прочитано (read_at == null) --}}
                            @if($notification->unread())
                                <i class="fa fa-circle text-primary fs-xs"></i>
                            @endif
                        </td>
                        <td class="fw-semibold fs-sm">
                            <a href="{{ route('cabinet.messages.show', $notification->id) }}">
                                {{ $data['title'] ?? 'Без заголовка' }}
                            </a>
                        </td>
                        <td class="fs-sm">{{ $data['sender'] ?? 'Система' }}</td>
                        <td class="d-none d-sm-table-cell">
                            @switch($data['type'] ?? '')
                                @case('danger')
                                <span class="badge bg-danger">Важно</span>
                                @break
                                @case('warning')
                                <span class="badge bg-warning">Внимание</span>
                                @break
                                @case('success')
                                <span class="badge bg-success">Успех</span>
                                @break
                                @default
                                <span class="badge bg-info">Инфо</span>
                            @endswitch
                        </td>
                        <td class="d-none d-md-table-cell text-end fs-sm text-muted">
                            {{ $notification->created_at->diffForHumans() }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection

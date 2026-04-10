@extends('cabinet.layouts.cabinet')

@section('title', 'Уведомления сайта: ' . $site->domain)

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-bell me-1"></i> История событий: {{ $site->domain }}
                </h3>
                <div class="block-options">
                    <a href="{{ route('cabinet.sites.show', $site) }}" class="btn btn-sm btn-alt-secondary">
                        <i class="fa fa-arrow-left me-1"></i> К проекту
                    </a>
                </div>
            </div>
            <div class="block-content">
                <table class="table table-hover table-vcenter">
                    <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Событие</th>
                        <th class="d-none d-sm-table-cell" style="width: 20%;">Дата</th>
                        <th class="text-center" style="width: 100px;">Тип</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($notifications as $notification)
                        <tr>
                            <td class="text-center">
                                <i class="{{ $notification->data['icon'] ?? 'fa fa-info-circle' }}"></i>
                            </td>
                            <td class="fw-semibold fs-sm">
                                {{-- Ссылка на просмотр конкретного сообщения --}}
                                <a href="{{ route('cabinet.messages.show', $notification->id) }}">
                                    {{ $notification->data['title'] }}
                                </a>
                                @if($notification->unread())
                                    <span class="badge rounded-pill bg-danger ms-1">Новое</span>
                                @endif
                            </td>
                            <td class="d-none d-sm-table-cell fs-sm">
                                {{ $notification->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $notification->data['type'] ?? 'info' }}">
                                    {{ $notification->data['type'] ?? 'system' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>
                </table>

                <div class="py-3">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

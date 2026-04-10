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
                        <tr style="cursor: pointer; {{ $notification->is_read_by_me ? 'background-color: rgba(6, 101, 208, 0.02);' : '' }}"
                            onclick="window.location='{{ route('cabinet.messages.show', $notification->id) }}'">

                            <td class="text-center">
                                {{-- Динамический цвет иконки в зависимости от типа --}}
                                @php
                                    $iconType = $notification->data['type'] ?? 'info';
                                    $iconClass = match($iconType) {
                                        'success' => 'text-success',
                                        'danger' => 'text-danger',
                                        'warning' => 'text-warning',
                                        default => 'text-info',
                                    };
                                @endphp
                                <i class="{{ $notification->data['icon'] ?? 'fa fa-info-circle' }} {{ $iconClass }}"></i>
                            </td>
                            <td class="{{ $notification->is_read_by_me != true ? 'fw-semibold' : '' }} fs-sm">
                                <a href="{{ route('cabinet.messages.show', $notification->id) }}">
                                    {{ $notification->data['title'] }}
                                </a>
                                <div class="text-muted fs-xs mt-1">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($data['message'] ?? ''), 80) }}
                                </div>
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
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">История уведомлений пуста</td>
                            </tr>
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

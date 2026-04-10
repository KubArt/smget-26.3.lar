@extends('cabinet.layouts.cabinet')

@section('title', 'Входящие сообщения')

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Входящие уведомления</h3>
                <div class="block-options">
                    <span class="badge bg-primary">{{ $notifications->total() }} всего</span>
                </div>
            </div>
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-hover table-vcenter">
                        <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;"></th>
                            <th>Сообщение</th>
                            <th>Источник</th>
                            <th class="d-none d-md-table-cell text-end" style="width: 150px;">Дата</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($notifications as $notification)
                            @php
                                $data = $notification->data;
                                // Определяем статус прочтения
                                $isUnread = ($notification->notifiable_type === 'App\Models\User')
                                    ? $notification->unread()
                                    : is_null($notification->is_custom_read);

                                // Настройка иконки и цвета на основе типа
                                $type = $data['type'] ?? 'info';
                                $contextClass = match($type) {
                                    'danger' => 'text-danger',
                                    'warning' => 'text-warning',
                                    'success' => 'text-success',
                                    default => 'text-info',
                                };
                                $bgClass = match($type) {
                                    'danger' => 'bg-danger-light',
                                    'warning' => 'bg-warning-light',
                                    'success' => 'bg-success-light',
                                    default => 'bg-info-light',
                                };
                            @endphp
                            <tr style="cursor: pointer; {{ $isUnread ? 'background-color: rgba(6, 101, 208, 0.02);' : '' }}"
                                onclick="window.location='{{ route('cabinet.messages.show', $notification->id) }}'">

                                <td class="text-center">
                                    <div class="item item-circle item-tiny {{ $bgClass }} {{ $contextClass }} mx-auto position-relative">
                                        <i class="{{ $data['icon'] ?? 'fa fa-bell' }} fs-sm"></i>
                                    </div>
                                </td>
                                <td>
                                    <div class="{{ $isUnread == true ? 'fw-semibold' : '' }} text-dark fs-sm">
                                        {{ $data['title'] ?? 'Без заголовка' }}
                                    </div>
                                    <div class="text-muted fs-xs mt-1">
                                        {{ Str::limit(strip_tags($data['message'] ?? ''), 80) }}
                                    </div>
                                </td>

                                <td class="fs-sm">
                                    @if($notification->notifiable_type === 'App\Models\Site')
                                        <span class="badge bg-secondary-light text-secondary px-2">
                                            <i class="fa fa-globe me-1"></i> {{ $notification->notifiable->domain }}
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            <i class="fa fa-user-shield me-1"></i> {{ $data['sender'] ?? 'Система' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="d-none d-md-table-cell text-end fs-xs text-muted">
                                    {{ $notification->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 py-3 border-top">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

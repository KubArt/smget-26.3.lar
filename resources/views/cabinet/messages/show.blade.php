@extends('cabinet.layouts.cabinet')

@section('title', $notification->data['title'])

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-xl-8">
                <div class="block block-rounded">
                    <div class="block-content block-content-full bg-body-light d-flex justify-content-between align-items-center">
                        <div>
                            <a class="btn btn-sm btn-alt-secondary" href="{{ route('cabinet.messages.index') }}">
                                <i class="fa fa-arrow-left me-1"></i> Назад
                            </a>
                        </div>
                        <div class="text-muted fs-sm">
                            <i class="fa fa-calendar-alt me-1"></i> {{ $notification->created_at->format('d.m.Y H:i') }}
                        </div>
                    </div>

                    <div class="block-content">
                        <div class="d-flex border-bottom pb-3 mb-4">
                            <div class="flex-shrink-0 me-3">
                                @if($notification->notifiable_type === 'App\Models\Site')
                                    <div class="item item-circle bg-info-light text-info">
                                        <i class="fa fa-globe"></i>
                                    </div>
                                @else
                                    <div class="item item-circle bg-primary-light text-primary">
                                        <i class="fa fa-user-shield"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold fs-lg text-dark mb-0">{{ $notification->data['title'] }}</div>
                                <div class="fs-sm text-muted">
                                    @if($notification->notifiable_type === 'App\Models\Site')
                                        Проект: <span class="fw-semibold text-primary">{{ $notification->notifiable->domain }}</span>
                                    @else
                                        Тип: <span class="fw-semibold">Личное уведомление</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="py-3 px-2 mb-4" style="line-height: 1.6; font-size: 1rem;">
                            {!! $notification->data['message'] ?? 'Текст сообщения отсутствует.' !!}
                        </div>

                        @if(isset($notification->data['url']))
                            <div class="alert alert-secondary d-flex align-items-center justify-content-between p-3 mb-4">
                                <div class="me-3">
                                    <p class="mb-0 fs-sm fw-medium">Связанное действие доступно по ссылке:</p>
                                </div>
                                <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-primary px-4">
                                    <i class="fa fa-external-link-alt me-1"></i> Перейти
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="block-content block-content-full bg-body-light text-end">
                        <button type="button" class="btn btn-sm btn-alt-danger" onclick="confirmDelete()">
                            <i class="fa fa-trash-alt me-1"></i> Удалить
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Ожидают прочтения</h3>
                        <div class="block-options">
                            <span class="badge bg-danger">{{ $unreadList->count() }}</span>
                        </div>
                    </div>
                    <div class="block-content p-0">
                        <div class="list-group list-group-flush">
                            @forelse($unreadList as $item)
                                <a class="list-group-item list-group-item-action d-flex align-items-start py-3" href="{{ route('cabinet.messages.show', $item->id) }}">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="{{ $item->data['icon'] ?? 'fa fa-envelope' }} text-{{ $item->data['type'] ?? 'primary' }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold fs-sm text-dark mb-1">{{ Str::limit($item->data['title'], 35) }}</div>
                                        <div class="text-muted fs-xs d-flex justify-content-between">
                                            <span>{{ $item->created_at->diffForHumans() }}</span>
                                            @if($item->notifiable_type === 'App\Models\Site')
                                                <span class="text-info">{{ $item->notifiable->domain }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-4 text-center">
                                    <i class="fa fa-coffee fa-2x text-muted mb-2"></i>
                                    <p class="mb-0 fs-sm text-muted">Все сообщения прочитаны</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if($unreadList->isNotEmpty())
                        <div class="block-content block-content-full bg-body-extra-light text-center">
                            <a class="fs-xs fw-bold text-uppercase" href="{{ route('cabinet.messages.index') }}">Смотреть все</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

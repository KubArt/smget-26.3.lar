<header id="page-header">
    <div class="content-header">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-alt-secondary me-2 d-lg-none" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-fw fa-bars"></i>
            </button>
        </div>

        <div class="d-flex align-items-center">

            <div class="d-none d-md-flex align-items-center me-3">
                <a class="btn btn-sm btn-alt-secondary" href="{{ route('cabinet.billing.index') }}">
                    <i class="fa fa-wallet me-1 text-success"></i>
                    <span class="fw-bold header-balance-value">{{ number_format($userBalance, 0, '.', ' ') }} ₽</span>
                </a>
            </div>

            <div class="dropdown d-inline-block ms-2">
                <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-fw fa-bell"></i>
                    <span class="badge rounded-pill bg-success ms-1">{{ $headerNotifications->count() }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 border-0 fs-sm" aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-2 bg-body-light border-bottom text-center rounded-top">
                        <h5 class="dropdown-header text-uppercase">Уведомления</h5>
                    </div>
                    <ul class="nav-items mb-0">
                        @forelse($headerNotifications as $notification)
                            <li>
                                {{-- Ссылка теперь ведет на контроллер прочтения --}}
                                <a class="text-dark d-flex py-2" href="{{ route('cabinet.messages.show', $notification->id) }}">
                                    <div class="flex-shrink-0 me-2 ms-3">
                                        @php
                                            $type = $notification->data['type'] ?? 'info';
                                            $iconColor = match($type) {
                                                'success' => 'text-success',
                                                'danger' => 'text-danger',
                                                'warning' => 'text-warning',
                                                default => 'text-info',
                                            };
                                        @endphp
                                        <i class="{{ $notification->data['icon'] ?? 'fa fa-info-circle' }} {{ $iconColor }}"></i>
                                    </div>
                                    <div class="flex-grow-1 pe-2">
                                        <div class="fw-semibold">{{ $notification->data['title'] }}</div>
                                        <div class="text-muted fs-xs">
                                            @if($notification->notifiable_type === 'App\Models\Site')
                                                <span class="badge bg-secondary-light text-secondary">
                                <i class="fa fa-globe me-1"></i> {{ $notification->notifiable->domain }}
                            </span>
                                            @endif
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-4 text-center">
                                <i class="fa fa-check-circle fa-2x text-light mb-2"></i>
                                <p class="mb-0 text-muted fs-sm">Новых уведомлений нет</p>
                            </li>
                        @endforelse
                    </ul>
                    <div class="p-2 border-top text-center">
                        <a class="d-inline-block fw-medium" href="{{ route('cabinet.messages.index') }}">
                            <i class="fa fa-fw fa-arrow-down me-1 opacity-50"></i> Смотреть все
                        </a>
                    </div>
                </div>
            </div>

            <div class="dropdown d-inline-block ms-2">
                <button type="button" class="btn btn-sm btn-alt-secondary d-flex align-items-center" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="d-none d-sm-inline-block ms-2">{{ auth()->user()->name }}</span>
                    <i class="fa fa-fw fa-angle-down d-none d-sm-inline-block opacity-50 ms-1"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0 border-0" aria-labelledby="page-header-user-dropdown">
                    <div class="p-2">
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('cabinet.profile.index') }}">
                            <span class="fs-sm fw-medium">Профиль</span>
                        </a>
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="{{ route('cabinet.billing.index') }}">
                            <span class="fs-sm fw-medium">Финансы</span>
                            <span class="badge rounded-pill bg-success-light text-success">{{ number_format($userBalance, 0, '.', ' ') }} ₽</span>
                        </a>
                        <div role="separator" class="dropdown-divider m-0"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between">
                                <span class="fs-sm fw-medium">Выйти</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<header id="page-header">
    <div class="content-header">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm btn-alt-secondary me-2 d-lg-none" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-fw fa-bars"></i>
            </button>
        </div>

        <div class="d-flex align-items-center">
            <div class="dropdown d-inline-block ms-2">
                <button type="button" class="btn btn-sm btn-alt-secondary" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-fw fa-bell"></i>
                    <span class="badge rounded-pill bg-success ms-1">{{ auth()->user()->unreadNotifications->count() }}</span>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 border-0 fs-sm" aria-labelledby="page-header-notifications-dropdown">
                    <div class="p-2 bg-body-light border-bottom text-center rounded-top">
                        <h5 class="dropdown-header text-uppercase">Уведомления</h5>
                    </div>
                    <ul class="nav-items mb-0">
                        @forelse(auth()->user()->unreadNotifications->take(3) as $notification)
                            <li>
                                <a class="text-dark d-flex py-2" href="{{ route('cabinet.messages.show', $notification->id) }}">
                                    <div class="flex-shrink-0 me-2 ms-3">
                                        <i class="fa fa-fw fa-info-circle text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 pe-2">
                                        <div class="fw-semibold">{{ $notification->data['title'] }}</div>
                                        <span class="fw-medium text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-3 text-center text-muted">Новых сообщений нет</li>
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
                        <a class="dropdown-item d-flex align-items-center justify-content-between" href="#">
                            <span class="fs-sm fw-medium">Профиль</span>
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

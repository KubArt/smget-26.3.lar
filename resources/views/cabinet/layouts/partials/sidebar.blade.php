<nav id="sidebar" aria-label="Main Navigation">
    <div class="content-header">
        <a class="fw-semibold text-dual" href="/">
            <span class="smini-visible">S</span>
            <span class="smini-hide fs-5 tracking-wider">SM<span class="fw-normal">GET</span></span>
        </a>
    </div>

    <div class="js-sidebar-scroll">
        <div class="content-side">
            <ul class="nav-main">
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.index') ? 'active' : '' }}" href="{{ route('cabinet.index') }}">
                        <i class="nav-main-link-icon si si-speedometer"></i>
                        <span class="nav-main-link-name">Главная</span>
                    </a>
                </li>

                <li class="nav-main-heading">CRM</li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.crm.tasks.*') ? 'active' : '' }}" href="{{ route('cabinet.crm.tasks.index') }}">
                        <i class="nav-main-link-icon si si-calendar"></i>
                        <span class="nav-main-link-name">Задачи</span>

                        @php
                            // 1. Получаем текущий кабинет пользователя
                            $currentWorkspace = auth()->user()->currentWorkspace();

                            // 2. Считаем задачи только тех сайтов, которые привязаны к этому кабинету
                            $pendingCount = \App\Models\Crm\LeadTask::where('status', 'pending')
                                ->whereHas('lead', function($query) use ($currentWorkspace) {
                                    $query->whereIn('site_id', $currentWorkspace->sites()->pluck('id'));
                                })
                                ->count();
                        @endphp

                        @if($pendingCount > 0)
                            <span class="nav-main-link-badge badge rounded-pill bg-danger">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.crm.leads.*') ? 'active' : '' }}" href="{{ route('cabinet.crm.leads.index') }}">
                        <i class="nav-main-link-icon si si-users"></i>
                        <span class="nav-main-link-name">Лиды</span>
                    </a>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.crm.clients.*') ? 'active' : '' }}" href="{{ route('cabinet.crm.clients.index') }}">
                        <i class="nav-main-link-icon si si-user-follow"></i>
                        <span class="nav-main-link-name">Клиенты</span>
                    </a>
                </li>

                <li class="nav-main-heading">Виджеты</li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.marketplace.*') ? 'active' : '' }}" href="{{ route('cabinet.marketplace.index') }}">
                        <i class="nav-main-link-icon si si-grid"></i>
                        <span class="nav-main-link-name">Маркетплейс</span>
                    </a>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.sites.integrations.*') ? 'active' : '' }}"
                       href="{{ isset($site) ? route('cabinet.sites.integrations.index', $site) : '#' }}"
                       @if(!isset($site)) style="opacity: 0.5; cursor: not-allowed;" title="Выберите сайт" @endif>
                        <i class="nav-main-link-icon si si-layers"></i>
                        <span class="nav-main-link-name">Интеграции</span>
                    </a>
                </li>

                <li class="nav-main-heading">Мои проекты</li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.sites.*') ? 'active' : '' }}" href="{{ route('cabinet.sites.index') }}">
                        <i class="nav-main-link-icon si si-globe"></i>
                        <span class="nav-main-link-name">Список сайтов</span>
                    </a>
                </li>

                <li class="nav-main-heading">Система</li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.team.*') ? 'active' : '' }}" href="{{ route('cabinet.team.index') }}">
                        <i class="nav-main-link-icon si si-users"></i>
                        <span class="nav-main-link-name">Сотрудники</span>
                    </a>
                </li>
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('cabinet.messages.*') ? 'active' : '' }}" href="{{ route('cabinet.messages.index') }}">
                        <i class="nav-main-link-icon si si-envelope"></i>
                        <span class="nav-main-link-name">Сообщения</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

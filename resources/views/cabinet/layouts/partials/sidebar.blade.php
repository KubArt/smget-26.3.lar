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

                <li class="nav-main-heading">Мои проекты</li>
                <li class="nav-main-item">
                    <a class="nav-main-link" href="#">
                        <i class="nav-main-link-icon si si-globe"></i>
                        <span class="nav-main-link-name">Список сайтов</span>
                    </a>
                </li>

                <li class="nav-main-heading">Система</li>
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

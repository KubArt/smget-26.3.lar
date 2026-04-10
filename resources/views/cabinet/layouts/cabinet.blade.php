<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title>@yield('title', 'SMGET - Кабинет')</title>

    <link rel="stylesheet" id="css-main" href="{{ asset('assets/css/oneui.min.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('css')
</head>
<body>
<div id="page-container" class="sidebar-o sidebar-dark enable-page-overlay side-scroll page-header-fixed main-content-narrow">

    @include('cabinet.layouts.partials.sidebar')

    @include('cabinet.layouts.partials.header')

    <main id="main-container">
        @yield('hero')

        <div class="content">
            @yield('content')
        </div>
    </main>

    <footer id="page-footer" class="bg-body-light">
        <div class="content py-3">
            <div class="row fs-sm">
                <div class="col-sm-6 order-sm-1 py-1 text-center text-sm-start">
                    <strong>SMGET</strong> &copy; 2026
                </div>
            </div>
        </div>
    </footer>
</div>

<script src="{{ asset('assets/js/oneui.app.min.js') }}"></script>

<script src="{{ asset('assets/js/lib/jquery.min.js') }}"></script>

@yield('js')
</body>
</html>

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

<script src="{{ asset('assets/js/lib/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

<script src="{{ asset('assets/js/oneui.app.min.js') }}"></script>

@include('cabinet.layouts.partials.messages')
<script>
    // Глобальные функции
    // 1. Делаем функцию глобальной, чтобы onclick её видел
    window.copyToClipboard = function(elementSelector) {
        let text = $(elementSelector).text().trim();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                One.helpers('jq-notify', {type: 'info', icon: 'fa fa-copy', message: 'Код скопирован!'});
            });
        } else {
            // Резервный метод для протоколов без SSL (http)
            let textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            One.helpers('jq-notify', {type: 'info', icon: 'fa fa-copy', message: 'Код скопирован!'});
        }
    };
</script>
@stack('js')

</body>
</html>

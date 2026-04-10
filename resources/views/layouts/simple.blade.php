<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>@yield('title') | SMGET</title>

    <link rel="stylesheet" id="css-main" href="{{ asset('assets/css/oneui.min.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="page-container">
    <main id="main-container">
        @yield('content')
    </main>
</div>
<script src="{{ asset('assets/js/oneui.app.min.js') }}"></script>
</body>
</html>

{{-- resources/views/cabinet/sites/metrics/instructions/google-analytics.blade.php --}}
<div class="alert alert-info d-flex align-items-start mb-4">
    <i class="fa fa-info-circle me-2 mt-1"></i>
    <div>
        <strong class="d-block mb-1">Как настроить Google Analytics 4</strong>
        <p class="mb-0 fs-sm">Для отправки конверсий вам понадобится ID потока данных (Measurement ID) и API Secret.</p>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 1. Создайте ресурс GA4</h5>
    <p class="fs-sm text-muted">
        Перейдите в
        <a href="https://analytics.google.com/" target="_blank">Google Analytics</a>
        и создайте ресурс GA4, если его еще нет.
    </p>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 2. Получите Measurement ID</h5>
    <p class="fs-sm text-muted">
        В ресурсе GA4 перейдите в <strong>Администратор → Потоки данных</strong>.
        Measurement ID выглядит как <code>G-XXXXXXXXXX</code>.
    </p>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 3. Получите API Secret</h5>
    <p class="fs-sm text-muted">
        В том же потоке данных нажмите <strong>Measurement Protocol API secrets</strong>
        и создайте новый секрет.
    </p>
    <div class="alert alert-warning fs-sm">
        <i class="fa fa-exclamation-triangle me-1"></i>
        <strong>Важно:</strong> API Secret нужен для отправки событий через Measurement Protocol
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 4. Что будет отправляться?</h5>
    <ul class="fs-sm text-muted mb-0">
        <li>generate_lead - отправка форм</li>
        <li>view_item - просмотр товаров</li>
        <li>add_to_cart - добавление в корзину</li>
        <li>purchase - покупки</li>
    </ul>
</div>

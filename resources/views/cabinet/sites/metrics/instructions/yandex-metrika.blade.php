{{-- resources/views/cabinet/sites/metrics/instructions/yandex-metrika.blade.php --}}
<div class="alert alert-info d-flex align-items-start mb-4">
    <i class="fa fa-info-circle me-2 mt-1"></i>
    <div>
        <strong class="d-block mb-1">Как настроить Яндекс.Метрику</strong>
        <p class="mb-0 fs-sm">Для отправки конверсий вам понадобится ID счетчика и OAuth-токен.</p>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 1. Получите ID счетчика</h5>
    <p class="fs-sm text-muted">
        ID счетчика можно найти в коде установки Яндекс.Метрики на вашем сайте.
        Обычно это число вида <code>12345678</code>.
    </p>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 2. Получите OAuth-токен</h5>
    <p class="fs-sm text-muted">Перейдите по ссылке и предоставьте доступ к счетчику:</p>
    <div class="bg-light p-3 rounded mb-2">
        <code class="text-break fs-xs">
            https://oauth.yandex.ru/authorize?response_type=token&client_id=23d7b8f8a6e44a2a9c9e5b8f8a6e44a2
        </code>
    </div>
    <div class="alert alert-warning fs-sm">
        <i class="fa fa-exclamation-triangle me-1"></i>
        <strong>Важно:</strong> Токен нужен с правами <code>Yandex Metrika</code> и <code>Yandex Metrika Edit</code>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 3. Что будет отправляться?</h5>
    <ul class="fs-sm text-muted mb-0">
        <li>Отправка форм (лиды)</li>
        <li>Целевые действия на сайте</li>
        <li>Покупки и бронирования</li>
    </ul>
</div>

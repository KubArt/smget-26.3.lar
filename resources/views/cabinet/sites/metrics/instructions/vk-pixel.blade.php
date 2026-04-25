{{-- resources/views/cabinet/sites/metrics/instructions/vk-pixel.blade.php --}}
<div class="alert alert-info d-flex align-items-start mb-4">
    <i class="fa fa-info-circle me-2 mt-1"></i>
    <div>
        <strong class="d-block mb-1">Как настроить VK Pixel</strong>
        <p class="mb-0 fs-sm">Для отправки конверсий вам понадобится ID пикселя VK Рекламы.</p>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 1. Создайте пиксель в VK Рекламе</h5>
    <p class="fs-sm text-muted">
        Перейдите в
        <a href="https://ads.vk.com/hq/pixels" target="_blank">кабинет VK Рекламы</a>
        и создайте новый пиксель, если его еще нет.
    </p>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 2. Получите ID пикселя</h5>
    <p class="fs-sm text-muted">
        ID пикселя можно найти в настройках пикселя. Обычно это число вида <code>1234567</code>.
    </p>
    <div class="bg-light p-3 rounded">
        <span class="badge bg-primary me-2">Пример</span>
        <code>1234567</code>
    </div>
</div>

<div class="mb-4">
    <h5 class="fw-semibold">Шаг 3. Что будет отправляться?</h5>
    <ul class="fs-sm text-muted mb-0">
        <li>Отправка форм (SubmitForm)</li>
        <li>Просмотр товаров (ViewContent)</li>
        <li>Добавление в корзину (AddToCart)</li>
        <li>Покупки (Purchase)</li>
    </ul>
</div>

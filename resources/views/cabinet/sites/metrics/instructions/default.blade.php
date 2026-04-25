{{-- resources/views/cabinet/sites/metrics/instructions/default.blade.php --}}
<div class="alert alert-info d-flex align-items-start">
    <i class="fa fa-info-circle me-2 mt-1"></i>
    <div>
        <strong class="d-block mb-1">Инструкция по настройке</strong>
        <p class="mb-0 fs-sm">
            Заполните необходимые поля выше и сохраните настройки.
            После сохранения система начнет отправлять конверсии в выбранный сервис.
        </p>
    </div>
</div>

<div class="mt-3">
    <h5 class="fw-semibold">Какие события будут отправляться?</h5>
    <ul class="fs-sm text-muted">
        <li><strong>lead_submit</strong> - отправка формы (лид)</li>
        <li><strong>widget_interaction</strong> - взаимодействие с виджетом</li>
        <li><strong>purchase</strong> - покупка (если есть)</li>
    </ul>
</div>

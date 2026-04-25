{{-- cabinet.sites.metrics.partials.yandex-metrika --}}
<div class="mb-4">
    <label class="form-label">ID счетчика <span class="text-danger">*</span></label>
    <input type="text" name="settings[counter_id]" class="form-control"
           value="{{ $settings['counter_id'] ?? '' }}"
           placeholder="Например: 12345678">
    <div class="form-text">ID вашего счетчика в Яндекс.Метрике</div>
</div>

<div class="mb-4">
    <label class="form-label">API Token <span class="text-danger">*</span></label>
    <input type="text" name="settings[token]" class="form-control"
           value="{{ $settings['token'] ?? '' }}"
           placeholder="OAuth-токен">
    <div class="form-text">
        <a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=..." target="_blank">
            Получить токен
        </a>
    </div>
</div>

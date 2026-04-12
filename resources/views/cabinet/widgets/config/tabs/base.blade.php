    <div class="row">
    <div class="col-md-7">
        <div class="mb-4">
            <label class="form-label">Внутреннее название</label>
            <input type="text" name="custom_name" class="form-control" value="{{ old('custom_name', $widget->custom_name) }}">
        </div>

        <div class="mb-4">
            <label class="form-label d-block">Статус виджета</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $widget->is_active ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Включен (отображается на сайте)</label>
            </div>
        </div>

        <hr>

        <h4 class="fw-normal h5 mb-3">Политика конфиденциальности</h4>
        <div class="mb-3">
            <div class="space-y-2">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="privacy_policy_type" id="policy_none" value="none" {{ ($widget->privacy_config['type'] ?? 'none') == 'none' ? 'checked' : '' }}>
                    <label class="form-check-label" for="policy_none">Не выводить ссылку</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="privacy_policy_type" id="policy_system" value="system" {{ ($widget->privacy_config['type'] ?? '') == 'system' ? 'checked' : '' }}>
                    <label class="form-check-label" for="policy_system">Использовать системную политику сайта</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="privacy_policy_type" id="policy_custom" value="custom" {{ ($widget->privacy_config['type'] ?? '') == 'custom' ? 'checked' : '' }}>
                    <label class="form-check-label" for="policy_custom">Своя ссылка (внешний ресурс)</label>
                </div>
            </div>
        </div>

        <div id="custom_policy_input" class="mb-4 {{ ($widget->privacy_config['type'] ?? '') == 'custom' ? '' : 'd-none' }}">
            <input type="url" name="privacy_policy_url" class="form-control" placeholder="https://example.com/policy" value="{{ $widget->privacy_config['url'] ?? '' }}">
        </div>
    </div>

    <div class="col-md-5">
        <div class="block block-rounded bg-body-light">
            <div class="block-content p-3">
                <h5 class="h6 mb-2 text-uppercase">О виджете</h5>
                <p class="fs-sm mb-2">
                    <strong>Тип:</strong> {{ $widget->widgetType->name }}<br>
                    <strong>Версия:</strong> 1.0.4<br>
                    <strong>ID объекта:</strong> <code>#{{ $widget->id }}</code>
                </p>
                <p class="fs-sm text-muted">
                    Этот виджет позволяет информировать пользователей об использовании файлов Cookie в соответствии с законом ФЗ-152.
                </p>
                <a href="#" class="btn btn-sm btn-alt-secondary w-100">
                    <i class="fa fa-book me-1"></i> Справка по настройке
                </a>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        // Переключение видимости поля для кастомной ссылки
        document.querySelectorAll('input[name="privacy_policy_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const customInput = document.getElementById('custom_policy_input');
                if(e.target.value === 'custom') {
                    customInput.classList.remove('d-none');
                } else {
                    customInput.classList.add('d-none');
                }
            });
        });
    </script>
@endpush

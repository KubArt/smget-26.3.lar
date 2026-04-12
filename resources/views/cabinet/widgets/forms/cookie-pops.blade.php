<div class="row items-push">
    <div class="col-lg-6">
        <div class="mb-4">
            <label class="form-label" for="settings-text">Текст сообщения</label>
            <textarea name="settings[text]" id="settings-text" class="form-control" rows="3" placeholder="Мы используем файлы cookie...">{{ $widget->settings['text'] ?? '' }}</textarea>
        </div>
        <div class="mb-4">
            <label class="form-label" for="settings-btn-text">Текст кнопки</label>
            <input type="text" name="settings[button_text]" id="settings-btn-text" class="form-control" value="{{ $widget->settings['button_text'] ?? 'Принять' }}">
        </div>
        <div class="mb-4">
            <label class="form-label" for="settings-delay">Задержка появления (сек)</label>
            <input type="number" name="settings[delay]" id="settings-delay" class="form-control" value="{{ $widget->settings['delay'] ?? 0 }}">
        </div>
    </div>

    <div class="col-lg-6">
        <div class="row">
            <div class="col-6 mb-4">
                <label class="form-label">Цвет фона</label>
                <div class="input-group">
                    <input type="color" name="settings[colors][bg]" class="form-control form-control-color w-100" value="{{ $widget->settings['colors']['bg'] ?? '#ffffff' }}">
                </div>
            </div>
            <div class="col-6 mb-4">
                <label class="form-label">Цвет текста</label>
                <div class="input-group">
                    <input type="color" name="settings[colors][text]" class="form-control form-control-color w-100" value="{{ $widget->settings['colors']['text'] ?? '#000000' }}">
                </div>
            </div>
            <div class="col-6 mb-4">
                <label class="form-label">Цвет кнопки</label>
                <div class="input-group">
                    <input type="color" name="settings[colors][btn_bg]" class="form-control form-control-color w-100" value="{{ $widget->settings['colors']['btn_bg'] ?? '#007bff' }}">
                </div>
            </div>
            <div class="col-6 mb-4">
                <label class="form-label">Текст на кнопке</label>
                <div class="input-group">
                    <input type="color" name="settings[colors][btn_text]" class="form-control form-control-color w-100" value="{{ $widget->settings['colors']['btn_text'] ?? '#ffffff' }}">
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Позиция на экране</label>
            <select name="settings[position]" class="form-select">
                <option value="bottom-right" {{ ($widget->settings['position'] ?? '') == 'bottom-right' ? 'selected' : '' }}>Справа внизу</option>
                <option value="bottom-left" {{ ($widget->settings['position'] ?? '') == 'bottom-left' ? 'selected' : '' }}>Слева внизу</option>
                <option value="top-center" {{ ($widget->settings['position'] ?? '') == 'top-center' ? 'selected' : '' }}>Сверху по центру</option>
            </select>
        </div>
    </div>
</div>

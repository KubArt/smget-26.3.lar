{{-- Выбор макета --}}
<div class="mb-4">
    <label class="form-label text-primary fw-bold">Выберите макет</label>
    <div class="row g-2">
        <template x-for="skin in skins" :key="skin.slug">
            <div class="col-6">
                <button type="button"
                        class="btn btn-sm w-100 border p-2 text-start transition-all"
                        :class="settings.template === skin.slug ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                        @click="applyTemplate(skin.slug)">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-circle me-2 fs-xs" :class="settings.template === skin.slug ? 'text-primary' : 'text-muted'"></i>
                        <span class="small fw-semibold" x-text="skin.name"></span>
                    </div>
                </button>
            </div>
        </template>
    </div>
    {{-- Скрытое поле для отправки ID выбранного шаблона --}}
    <input type="hidden" name="template" :value="settings.template">
</div>

<hr>

{{-- Настройка цветов --}}
<div class="row mb-4">
    <div class="col-4 text-center">
        <label class="form-label small">Фон</label>
        <input type="color" name="design[bg_color]"
               class="form-control form-control-color w-100"
               x-model="settings.design.bg_color">
    </div>
    <div class="col-4 text-center">
        <label class="form-label small">Текст</label>
        <input type="color" name="design[text_color]"
               class="form-control form-control-color w-100"
               x-model="settings.design.text_color">
    </div>
    <div class="col-4 text-center">
        <label class="form-label small">Кнопка</label>
        <input type="color" name="design[btn_color]"
               class="form-control form-control-color w-100"
               x-model="settings.design.btn_color">
    </div>
</div>

{{-- Информационное сообщение --}}
<div class="alert alert-info py-2 small">
    <i class="fa fa-info-circle me-1"></i>
    Тексты и ссылки настраиваются во вкладке "Конфигурация".
</div>

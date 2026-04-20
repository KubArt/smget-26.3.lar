@extends('cabinet.widgets.design')

@section('widget_editor')
    <div class="content" x-data="alertBarEditor({{ json_encode($config) }})">
        <div class="row">
            <div class="col-md-5">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Конструктор: {{ $widget->widgetType->name }}</h3>
                    </div>
                    <div class="block-content pb-4">
                        <form action="{{ route('cabinet.sites.widgets.design.update', [$site, $widget]) }}" method="POST">
                            @csrf

                            {{-- 1. Выбор позиции --}}
                            <div class="mb-4">
                                <label class="form-label text-primary fw-bold">Расположение</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                :class="settings.position === 'top' ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                @click="settings.position = 'top'; updatePreview()">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-circle me-2 fs-xs" :class="settings.position === 'top' ? 'text-primary' : 'text-muted'"></i>
                                                <span class="small fw-semibold">Сверху страницы</span>
                                            </div>
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn btn-sm w-100 border p-2 text-start transition-all"
                                                :class="settings.position === 'bottom' ? 'btn-alt-primary border-primary shadow-sm' : 'btn-alt-secondary opacity-75'"
                                                @click="settings.position = 'bottom'; updatePreview()">
                                            <div class="d-flex align-items-center">
                                                <i class="fa fa-circle me-2 fs-xs" :class="settings.position === 'bottom' ? 'text-primary' : 'text-muted'"></i>
                                                <span class="small fw-semibold">Снизу страницы</span>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="position" :value="settings.position">
                            </div>

                            <div class="mb-3" x-show="settings.position === 'top'">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="fixed_on_scroll"
                                           x-model="settings.fixed_on_scroll" @change="updatePreview()">
                                    <label class="form-check-label small">
                                        Фиксировать при прокрутке (всегда вверху экрана)
                                    </label>
                                    <small class="text-muted d-block fs-xs">Если отключено - полоса в потоке документа и прокручивается со страницей</small>
                                </div>
                            </div>

                            <hr>

                            {{-- 2. Редактирование контента --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                                    Текст и кнопка
                                </label>

                                <div class="mb-3">
                                    <label class="small text-muted">Текст объявления</label>
                                    <textarea name="text" class="form-control form-control-sm" rows="2"
                                              x-model="settings.text" @input="updatePreview()"></textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-12 mb-2">
                                        <label class="small text-muted">Текст кнопки</label>
                                        <input type="text" name="btn_text" class="form-control form-control-sm"
                                               x-model="settings.btn_text" @input="updatePreview()">
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="small text-muted mb-0">Ссылка кнопки (URL)</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="has_button"
                                                       x-model="settings.has_button" @change="updatePreview()">
                                                <label class="small ms-1">Показывать кнопку</label>
                                            </div>
                                        </div>
                                        <input type="text" name="link" class="form-control form-control-sm"
                                               x-show="settings.has_button"
                                               x-model="settings.link" @input="updatePreview()"
                                               placeholder="https://example.com">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            {{-- 3. Цвета --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Цветовая схема</label>
                                <div class="row g-2">
                                    <div class="col-4 text-center">
                                        <label class="form-label small">Фон</label>
                                        <input type="color" name="design[bg_color]" class="form-control form-control-color w-100"
                                               x-model="settings.design.bg_color" @change="updateColors(); updatePreview()">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="form-label small">Текст</label>
                                        <input type="color" name="design[text_color]" class="form-control form-control-color w-100"
                                               x-model="settings.design.text_color" @change="updateColors(); updatePreview()">
                                    </div>
                                    <div class="col-4 text-center">
                                        <label class="form-label small">Кнопка</label>
                                        <input type="color" name="design[btn_color]" class="form-control form-control-color w-100"
                                               x-model="settings.design.btn_color" @change="updateColors(); updatePreview()">
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-alt-primary w-100" @click="saveConfig()">
                                <i class="fa fa-save opacity-50 me-1"></i> Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Предпросмотр --}}
            <div class="col-md-7" x-data="{ previewMode: 'desktop' }" x-init="$watch('previewMode', () => $dispatch('preview-mode-changed', previewMode))">
                @include("widgets.configuration.preview")
            </div>
        </div>
    </div>
@endsection
@include("widgets.alert-bar.configuration_js")


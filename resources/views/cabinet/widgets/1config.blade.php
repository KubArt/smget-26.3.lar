@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Настройка виджета: {{ $widget->widgetType->name }}</h3>
            </div>

            <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="base-tab" data-bs-toggle="tab" data-bs-target="#tab-base">Базовые настройки</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="target-tab" data-bs-toggle="tab" data-bs-target="#tab-target">Таргетинг</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#tab-stats">Статистика</button>
                </li>
            </ul>

            <form action="{{ route('cabinet.sites.widgets.config.update', [$site, $widget]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="block-content tab-content">
                    <div class="tab-pane active" id="tab-base" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label">Название для внутреннего использования</label>
                                <input type="text" name="custom_name" class="form-control" value="{{ $widget->custom_name }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Состояние</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_enabled" {{ $widget->is_enabled ? 'checked' : '' }}>
                                    <label class="form-check-label">Виджет активен</label>
                                </div>
                            </div>
                        </div>
                        <h4>Контент виджета</h4>
                        @include('cabinet.widgets.forms.' . $widget->widgetType->slug)
                    </div>

                    <div class="tab-pane" id="tab-target" role="tabpanel">
                        <div class="mb-4">
                            <h5>Показ на страницах</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label text-success">Разрешить на (через новую строку)</label>
                                    <textarea name="target_paths[allow]" class="form-control" rows="5">{{ implode("\n", $widget->target_paths['allow'] ?? []) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-danger">Исключить на</label>
                                    <textarea name="target_paths[disallow]" class="form-control" rows="5">{{ implode("\n", $widget->target_paths['disallow'] ?? []) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h5>UTM-таргетинг</h5>
                        <div id="utm-container">
                            @foreach($widget->target_utm ?? [] as $index => $group)
                                <div class="utm-group border p-3 mb-2 bg-light">
                                    <div class="row">
                                        <div class="col-5">
                                            <input type="text" name="target_utm[{{$index}}][key]" class="form-control" placeholder="Ключ (utm_source)" value="{{ $group['key'] ?? '' }}">
                                        </div>
                                        <div class="col-5">
                                            <input type="text" name="target_utm[{{$index}}][value]" class="form-control" placeholder="Значение" value="{{ $group['value'] ?? '' }}">
                                        </div>
                                        <div class="col-2 text-end">
                                            <button type="button" class="btn btn-danger btn-sm">×</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary mt-2">Добавить условие ИЛИ</button>
                    </div>

                    <div class="tab-pane" id="tab-stats" role="tabpanel">
                        <div class="text-center p-5">
                            <p class="text-muted">Графики показов и кликов за последние 30 дней</p>
                        </div>
                    </div>
                </div>

                <div class="block-content block-content-full block-header-default text-end">
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
@endsection

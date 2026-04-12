@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        {{-- Список ошибок --}}
        @if($errors->any())
            <div class="alert alert-danger shadow-sm" role="alert">
                <div class="d-flex">
                    <i class="fa fa-fw fa-times-circle me-2 mt-1"></i>
                    <div>
                        <h4 class="alert-heading fs-sm fw-bold mb-1">Ошибка сохранения!</h4>
                        <ul class="ps-3 mb-0 fs-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Настройка виджета: {{ $widget->widgetType->name }}</h3>
            </div>

            <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-base">Базовые настройки</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-target">Таргетинг</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-custom">Контент и оформление</button>
                </li>
            </ul>

            <form action="{{ route('cabinet.sites.widgets.config.update', [$site, $widget]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="block-content tab-content overflow-hidden">
                    <div class="tab-pane active" id="tab-base" role="tabpanel">
                        @include('cabinet.widgets.config.tabs.base')
                    </div>

                    <div class="tab-pane" id="tab-target" role="tabpanel">
                        @include('cabinet.widgets.config.tabs.target')
                    </div>

                    <div class="tab-pane" id="tab-custom" role="tabpanel">
                        <h4 class="fw-normal border-bottom pb-2 mb-4">Настройки внешнего вида</h4>
                        @include('cabinet.widgets.forms.' . $widget->widgetType->slug)
                    </div>
                </div>

                <div class="block-content block-content-full block-content-sm bg-body-light text-end">
                    <button type="submit" class="btn btn-alt-primary">
                        <i class="fa fa-check opacity-50 me-1"></i> Сохранить настройки
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

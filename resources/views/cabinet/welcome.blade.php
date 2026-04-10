@extends('cabinet.layouts.cabinet')

@section('title', 'Начало работы | SMGET')

@section('hero')
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">
                        Добро пожаловать, {{ auth()->user()->name }}!
                    </h1>
                    <h2 class="h6 fw-medium fw-medium text-muted mb-0">
                        Давайте настроим ваш первый проект.
                    </h2>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7 col-xl-5">
            <div class="block block-rounded block-bordered text-center py-5 px-3">
                <div class="block-content">
                    <div class="py-3">
                        <div class="mb-4">
                            <span class="d-inline-block bg-primary-lighter p-4 rounded-circle">
                                <i class="si si-globe fa-3x text-primary"></i>
                            </span>
                        </div>
                        <h3 class="fw-bold mb-2">У вас еще нет сайтов</h3>
                        <p class="text-muted mb-4">
                            Для того чтобы использовать возможности <strong>SMGET</strong>, добавьте домен вашего сайта.
                            После этого вы сможете создавать виджеты и отслеживать лиды.
                        </p>
                        <a class="btn btn-lg btn-alt-primary" href="{{ route('cabinet.sites.create') }}">
                            <i class="fa fa-plus me-1"></i> Добавить первый сайт
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="fs-sm fw-bold text-uppercase text-muted">Шаг 1</div>
                        <div class="fs-sm mt-1">Добавьте домен сайта</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="fs-sm fw-bold text-uppercase text-muted">Шаг 2</div>
                        <div class="fs-sm mt-1">Выберите виджет</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="fs-sm fw-bold text-uppercase text-muted">Шаг 3</div>
                        <div class="fs-sm mt-1">Установите код</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

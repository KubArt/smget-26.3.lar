@extends('cabinet.layouts.cabinet')

@section('title', 'Панель управления | SMGET')

@section('hero')
    <div class="bg-body-light">
        <div class="content content-full">
            <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
                <div class="flex-grow-1">
                    <h1 class="h3 fw-bold mb-2">Обзор проектов</h1>
                    <h2 class="h6 fw-medium text-muted mb-0">Статистика по вашим сайтам и виджетам</h2>
                </div>
                <div class="mt-3 mt-sm-0 ms-sm-3">
                    <a class="btn btn-primary" href="{{ route('cabinet.sites.create') }}">
                        <i class="fa fa-plus me-1"></i> Добавить сайт
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold text-primary">{{ $sites->count() }}</div>
                </div>
                <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">Сайтов</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold text-success">{{ $sites->sum('widgets_count') }}</div>
                </div>
                <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">Активных виджетов</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold text-dark">0</div>
                </div>
                <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">Лидов (24ч)</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
                <div class="block-content block-content-full">
                    <div class="fs-2 fw-semibold text-warning">0%</div>
                </div>
                <div class="block-content py-2 bg-body-light">
                    <p class="fw-medium fs-sm text-muted mb-0">Конверсия</p>
                </div>
            </a>
        </div>
    </div>

    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Ваши площадки</h3>
        </div>
        <div class="block-content">
            <div class="table-responsive">
                <table class="table table-striped table-vcenter">
                    <thead>
                    <tr>
                        <th>Название</th>
                        <th>Домен</th>
                        <th class="text-center">Виджеты</th>
                        <th>Статус</th>
                        <th class="text-center" style="width: 100px;">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sites as $site)
                        <tr>
                            <td class="fw-semibold fs-sm">{{ $site->name }}</td>
                            <td class="fs-sm">{{ $site->domain }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $site->widgets_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-success">Активен</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="#" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Настройки">
                                        <i class="fa fa-fw fa-pencil-alt"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Код установки">
                                        <i class="fa fa-fw fa-code"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Мои проекты (сайты)</h3>
                <div class="block-options">
                    <button type="button" class="btn btn-sm btn-alt-primary" data-bs-toggle="modal" data-bs-target="#modal-add-site">
                        <i class="fa fa-plus me-1"></i> Добавить сайт
                    </button>
                </div>
            </div>
            <div class="block-content">
                <table class="table table-striped table-vcenter">
                    <thead>
                    <tr>
                        <th>Название</th>
                        <th>Домен</th>
                        <th class="d-none d-sm-table-cell">Статус</th>
                        <th class="text-center">События</th>
                        <th class="text-center">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($sites as $site)
                        <tr>
                            <td class="fw-semibold">
                                <a href="{{ route('cabinet.sites.show', $site->id) }}">{{ $site->name }}</a>
                            </td>
                            <td>{{ $site->domain }}</td>
                            <td class="d-none d-sm-table-cell">
                                @if($site->is_verified)
                                    <span class="badge bg-success">Подтвержден</span>
                                @else
                                    <span class="badge bg-warning">Требует проверки</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('cabinet.sites.notifications', $site) }}" class="btn btn-sm btn-alt-secondary">
                                    <i class="fa fa-bell me-1"></i>
                                    @if($site->unreadNotifications->count() > 0)
                                        <span class="badge rounded-pill bg-danger">{{ $site->unreadNotifications->count() }}</span>
                                    @else
                                        0
                                    @endif
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('cabinet.sites.edit', $site->id) }}" class="btn btn-sm btn-alt-secondary" title="Редактировать">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-alt-secondary" onclick="confirmDelete({{ $site->id }})" title="Удалить">
                                        <i class="fa fa-times text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $sites->links() }}
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-add-site" tabindex="-1" role="dialog" aria-labelledby="modal-add-site" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('cabinet.sites.store') }}" method="POST">
                    @csrf
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Новый проект</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <div class="mb-4">
                                <label class="form-label">Название проекта</label>
                                <input type="text" name="name" class="form-control" placeholder="Например: Стоматология Смайл">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Домен</label>
                                <input type="text" name="domain" class="form-control" placeholder="smile.ru">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Email для уведомлений</label>
                                <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}">
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body-light">
                            <button type="button" class="btn btn-sm btn-alt-secondary me-1" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" class="btn btn-sm btn-primary">Создать проект</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

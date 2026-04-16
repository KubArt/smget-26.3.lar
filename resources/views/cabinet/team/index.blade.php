@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="content-heading mb-0">Команда: {{ $workspace->name }}</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-member">
                <i class="fa fa-plus me-1"></i> Добавить сотрудника
            </button>
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Список сотрудников</h3>
            </div>
            <div class="block-content">
                <table class="table table-striped table-vcenter">
                    <thead>
                    <tr>
                        <th>Сотрудник</th>
                        <th>Роль</th>
                        <th class="d-none d-sm-table-cell">Доступ к сайтам</th>
                        <th class="text-center">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($members as $member)
                        <tr>
                            <td class="fw-semibold">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($member->profile && $member->profile->avatar)
                                            <img class="img-avatar img-avatar32" src="{{ $member->profile->avatar }}" alt="">
                                        @else
                                            <div class="item item-circle bg-info-light text-info fw-bold" style="width: 32px; height: 32px; line-height: 32px; font-size: 12px;">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        {{ $member->name }}
                                        <div class="fs-sm text-muted">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($member->pivot->role) {
                                        'owner' => 'bg-danger',
                                        'admin' => 'bg-primary',
                                        'manager' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ strtoupper($member->pivot->role) }}</span>
                            </td>
                            <td class="d-none d-sm-table-cell">
                                <span class="text-muted fs-sm">Весь кабинет ({{ $workspace->sites_count ?? $workspace->sites()->count() }} сайтов)</span>
                            </td>
                            <td class="text-center">
                                @if($member->pivot->role !== 'owner')
                                    <form action="{{ route('cabinet.team.destroy', $member->id) }}" method="POST" onsubmit="return confirm('Удалить сотрудника из кабинета?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-alt-danger">
                                            <i class="fa fa-user-times"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted fs-xs">Владелец</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-add-member" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('cabinet.team.store') }}" method="POST">
                    @csrf
                    <div class="block block-rounded block-transparent mb-0">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Пригласить сотрудника</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="block-content fs-sm">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label class="form-label">Фамилия</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="form-label">Имя</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="form-label">Отчество</label>
                                    <input type="text" name="patronymic" class="form-control">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Телефон</label>
                                    <input type="text" name="phone" class="form-control" placeholder="79001234567">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Пароль</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Роль</label>
                                    <select class="form-select" name="role">
                                        <option value="manager">Менеджер</option>
                                        <option value="admin">Администратор</option>
                                        <option value="specialist">Специалист</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="block-content block-content-full text-end bg-body-light">
                            <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" class="btn btn-sm btn-primary">Создать сотрудника</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

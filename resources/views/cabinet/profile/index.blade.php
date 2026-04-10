@extends('cabinet.layouts.cabinet')

@section('title', 'Мой профиль')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Персональная информация</h3>
                    </div>
                    <div class="block-content">
                        <form action="{{ route('cabinet.profile.update') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Email (Системный)</label>
                                <input type="text" class="form-control" value="{{ $user->email }}" readonly disabled>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="phone">Телефон</label>
                                <input type="text" name="phone" id="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $user->phone) }}" placeholder="+7 (___) ___ __ __">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row mb-4">
                                <div class="col-4">
                                    <label class="form-label">Имя</label>
                                    <input type="text" name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $user->profile?->name) }}">
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Отчество</label>
                                    <input type="text" name="patronymic"
                                           class="form-control @error('patronymic') is-invalid @enderror"
                                           value="{{ old('patronymic', $user->profile?->patronymic) }}">
                                    @error('patronymic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-4">
                                    <label class="form-label">Фамилия</label>
                                    <input type="text" name="last_name"
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           value="{{ old('last_name', $user->profile?->last_name) }}">
                                    @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-alt-primary mb-4">Сохранить</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Безопасность</h3>
                    </div>
                    <div class="block-content">
                        <form action="{{ route('cabinet.profile.password') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Текущий пароль</label>
                                <input type="password" name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Новый пароль</label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Подтверждение</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-alt-danger mb-4">Изменить пароль</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

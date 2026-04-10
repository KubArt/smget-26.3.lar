@extends('layouts.simple')

@section('title', 'Установка нового пароля')

@section('content')
    <div class="bg-image" style="background-image: url('{{ asset('assets/media/photos/photo28@2x.jpg') }}');">
        <div class="row g-0 bg-primary-dark-op">
            <div class="hero-static col-lg-8 d-flex flex-column align-items-center bg-body-extra-light mx-auto">
                <div class="p-3 w-100 mw-100 flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="col-md-8 col-xl-6">
                        <div class="mb-4 text-center">
                            <h1 class="h3 fw-bold mt-4 mb-2">Новый пароль</h1>
                            <p class="text-muted">Пожалуйста, введите ваш новый пароль ниже.</p>
                        </div>

                        <form action="{{ route('password.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">
                            <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                            <div class="mb-4">
                                <input type="password" class="form-control form-control-lg form-control-alt" name="password" placeholder="Новый пароль" required>
                            </div>
                            <div class="mb-4">
                                <input type="password" class="form-control form-control-lg form-control-alt" name="password_confirmation" placeholder="Подтвердите пароль" required>
                            </div>
                            <button type="submit" class="btn btn-alt-primary w-100">
                                Обновить пароль
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

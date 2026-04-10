@extends('layouts.simple')

@section('title', 'Авторизация')

@section('content')
    <div class="bg-image" style="background-image: url('{{ asset('assets/media/photos/photo28@2x.jpg') }}');">
        <div class="row g-0 bg-primary-dark-op">
            <div class="hero-static col-lg-4 d-none d-lg-flex flex-column justify-content-center">
                <div class="p-4 p-xl-5 flex-grow-1 d-flex align-items-center">
                    <div class="w-100">
                        <a class="link-fx fw-semibold fs-2 text-white" href="/">
                            SM<span class="fw-normal">GET</span>
                        </a>
                        <p class="text-white-75 me-xl-8 mt-2">
                            Добро пожаловать в вашу панель управления виджетами. Все лиды под контролем.
                        </p>
                    </div>
                </div>
                <div class="p-4 p-xl-5 d-xl-flex justify-content-between align-items-center fs-sm">
                    <p class="fw-medium text-white-50 mb-0">
                        <strong>SMGET</strong> &copy; 2026
                    </p>
                </div>
            </div>

            <div class="hero-static col-lg-8 d-flex flex-column align-items-center bg-body-extra-light">
                <div class="p-3 w-100 mw-100 flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="col-md-8 col-xl-6">
                        <div class="mb-2 text-center">
                            <a class="link-fx fw-bold fs-1" href="/">
                                <span class="text-dark">SM</span><span class="text-primary">GET</span>
                            </a>
                            <p class="text-uppercase fw-bold fs-sm text-muted">Вход в кабинет</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2">
                                <ul class="mb-0 fs-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="py-3">
                                <div class="mb-4">
                                    <input type="email" class="form-control form-control-lg form-control-alt" id="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus>
                                </div>
                                <div class="mb-4">
                                    <input type="password" class="form-control form-control-lg form-control-alt" id="password" name="password" placeholder="Пароль" required>
                                </div>
                                <div class="mb-4">
                                    <div class="d-md-flex align-items-center justify-content-between">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="remember_me" name="remember">
                                            <label class="form-check-label" for="remember_me">Запомнить меня</label>
                                        </div>
                                        <div class="py-2">
                                            <a class="fs-sm fw-medium" href="{{ route('password.request') }}">Забыли пароль?</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6 col-xl-5">
                                    <button type="submit" class="btn w-100 btn-alt-primary">
                                        <i class="fa fa-fw fa-sign-in-alt me-1 opacity-50"></i> Войти
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="fs-sm text-muted">
                            Нет аккаунта? <a href="{{ route('register') }}">Регистрация</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.simple')

@section('title', 'Регистрация')

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
                            Создайте аккаунт и начните собирать лиды уже через 5 минут.
                        </p>
                    </div>
                </div>
            </div>

            <div class="hero-static col-lg-8 d-flex flex-column align-items-center bg-body-extra-light">
                <div class="p-3 w-100 mw-100 flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="col-md-8 col-xl-6">

                        <div class="mb-4 text-center">
                            <a class="link-fx fw-bold fs-1" href="/">
                                <span class="text-dark">SM</span><span class="text-primary">GET</span>
                            </a>
                            <p class="text-uppercase fw-bold fs-sm text-muted mb-3">Создание аккаунта</p>

                            <div class="block block-rounded block-bordered bg-body-light">
                                <div class="block-content block-content-full text-start fs-sm text-muted">
                                    <p class="mb-2">
                                        <strong>SMGET</strong> — это единая платформа для управления интерактивными виджетами на ваших сайтах.
                                    </p>
                                    <ul class="fa-ul list-sm mb-0">
                                        <li>
                                            <span class="fa-li"><i class="fa fa-check text-success"></i></span>
                                            Увеличивайте конверсию с помощью умных механик.
                                        </li>
                                        <li>
                                            <span class="fa-li"><i class="fa fa-check text-success"></i></span>
                                            Получайте лиды прямо в личный кабинет.
                                        </li>
                                        <li>
                                            <span class="fa-li"><i class="fa fa-check text-success"></i></span>
                                            Управляйте всеми проектами в одном окне.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger py-2 mb-4">
                                <ul class="mb-0 fs-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('register') }}" method="POST">
                            @csrf
                            <div class="py-3">
                                <div class="mb-4">
                                    <input type="text" class="form-control form-control-lg form-control-alt" name="name" placeholder="Ваше имя" value="{{ old('name') }}" required autofocus>
                                </div>
                                <div class="mb-4">
                                    <input type="email" class="form-control form-control-lg form-control-alt" name="email" placeholder="Email" value="{{ old('email') }}" required>
                                </div>
                                <div class="mb-4">
                                    <input type="password" class="form-control form-control-lg form-control-alt" name="password" placeholder="Пароль" required>
                                </div>
                                <div class="mb-4">
                                    <input type="password" class="form-control form-control-lg form-control-alt" name="password_confirmation" placeholder="Подтвердите пароль" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6 col-xl-5">
                                    <button type="submit" class="btn w-100 btn-alt-success">
                                        <i class="fa fa-fw fa-plus me-1 opacity-50"></i> Создать
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="fs-sm text-muted">
                            Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

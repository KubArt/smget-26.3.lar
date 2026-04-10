@extends('layouts.simple')

@section('title', 'Восстановление пароля')

@section('content')
    <div class="bg-image" style="background-image: url('{{ asset('assets/media/photos/photo28@2x.jpg') }}');">
        <div class="row g-0 bg-primary-dark-op">
            <div class="hero-static col-lg-4 d-none d-lg-flex flex-column justify-content-center">
                <div class="p-4 p-xl-5">
                    <a class="link-fx fw-semibold fs-2 text-white" href="/">
                        SM<span class="fw-normal">GET</span>
                    </a>
                    <p class="text-white-75 mt-2">
                        Ничего страшного, мы поможем вам вернуть доступ к аккаунту.
                    </p>
                </div>
            </div>

            <div class="hero-static col-lg-8 d-flex flex-column align-items-center bg-body-extra-light">
                <div class="p-3 w-100 mw-100 flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="col-md-8 col-xl-6">
                        <div class="mb-4 text-center">
                            <p class="text-uppercase fw-bold fs-sm text-muted">Сброс пароля</p>
                            <p class="fs-sm text-muted">
                                Введите адрес электронной почты, указанный при регистрации, и мы отправим вам инструкции.
                            </p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success py-2 mb-4 fs-sm">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form action="{{ route('password.email') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <input type="email" class="form-control form-control-lg form-control-alt" name="email" value="{{ old('email') }}" placeholder="Ваш Email" required autofocus>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-8 col-xl-7">
                                    <button type="submit" class="btn w-100 btn-alt-primary">
                                        <i class="fa fa-fw fa-envelope me-1 opacity-50"></i> Отправить ссылку
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div class="fs-sm text-muted">
                            Вспомнили пароль? <a href="{{ route('login') }}">Вернуться ко входу</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

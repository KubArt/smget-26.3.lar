@extends('layouts.simple')

@section('title', 'Подтверждение почты')

@section('content')
    <div class="bg-image" style="background-image: url('{{ asset('assets/media/photos/photo28@2x.jpg') }}');">
        <div class="row g-0 bg-primary-dark-op">
            <div class="hero-static col-lg-12 d-flex flex-column align-items-center bg-body-extra-light">
                <div class="p-3 w-100 mw-100 flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="col-md-8 col-xl-4 text-center">
                        <div class="mb-4">
                            <i class="fa fa-6x fa-envelope-open-text text-primary"></i>
                        </div>
                        <h1 class="h2 fw-bold mb-2">Проверьте почту</h1>
                        <p class="fs-sm text-muted mb-4">
                            Спасибо за регистрацию! Прежде чем начать, подтвердите свой Email, перейдя по ссылке, которую мы только что отправили вам на почту.
                        </p>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success py-2 mb-4 fs-sm">
                                Новая ссылка для подтверждения была отправлена на ваш адрес.
                            </div>
                        @endif

                        <div class="d-flex justify-content-center gap-2">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-alt-primary">
                                    <i class="fa fa-fw fa-sync me-1 opacity-50"></i> Отправить повторно
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-alt-secondary">
                                    <i class="fa fa-fw fa-sign-out-alt me-1 opacity-50"></i> Выйти
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

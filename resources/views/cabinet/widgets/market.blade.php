@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="row">
            @foreach($widgetTypes as $type)
                <div class="col-md-4 col-xl-3">
                    <div class="block block-rounded text-center">
                        <div class="block-content block-content-full">
                            <div class="item item-circle bg-primary-lighter mx-auto my-3">
                                <i class="fa fa-puzzle-piece text-primary"></i>
                            </div>
                            <div class="text-black fw-bold">{{ $type->name }}</div>
                            <div class="text-muted fs-sm">{{ $type->category }}</div>
                            <div class="mt-2">
                                @if($type->is_free)
                                    <span class="badge bg-success">Бесплатно</span>
                                @else
                                    <span class="badge bg-warning">PRO</span>
                                @endif
                            </div>
                        </div>
                        <div class="block-content block-content-full bg-body-light">
                            @if(isset($preSelectedSite))
                                {{-- Если мы пришли со страницы конкретного сайта --}}
                                <form action="{{ route('cabinet.sites.widgets.store', $preSelectedSite) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="widget_type_id" value="{{ $type->id }}">
                                    <button type="submit" class="btn btn-sm btn-alt-primary">
                                        <i class="fa fa-plus me-1"></i> Установить на {{ $preSelectedSite->domain }}
                                    </button>
                                </form>
                            @elseif(count(auth()->user()->sites) > 0)
                                {{-- Общий вход в маркетплейс: выбор из списка всех сайтов --}}
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm btn-alt-primary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Установить на...
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        @foreach(auth()->user()->sites as $userSite)
                                            <form action="{{ route('cabinet.sites.widgets.store', $userSite) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="widget_type_id" value="{{ $type->id }}">
                                                <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between">
                                                    <span>{{ $userSite->domain }}</span>
                                                    <i class="fa fa-plus opacity-50 ms-1"></i>
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Если у пользователя еще нет сайтов --}}
                                <a href="{{ route('cabinet.sites.index') }}" class="btn btn-sm btn-alt-secondary">
                                    Сначала добавьте сайт
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

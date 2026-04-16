@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="content-heading mb-0">Интеграции сайта: {{ $site->domain }}</h2>
        </div>

        <div class="row">
            @foreach($services as $service)
                <div class="col-md-6 col-xl-4">
                    <div class="block block-rounded text-center">
                        <div class="block-content block-content-full">
                            <div class="item item-circle bg-gray-lighter mx-auto my-3">
                                <i class="{{ $service->icon ?? 'fa fa-plug' }} text-primary"></i>
                            </div>
                            <div class="text-black fw-semibold">{{ $service->name }}</div>
                            <div class="text-muted fs-sm mt-1">{{ $service->description }}</div>
                        </div>
                        <div class="block-content block-content-full bg-body-light">
                            @if(in_array($service->id, $connectedServices))
                                <span class="badge bg-success mb-2">Подключено</span>
                                <br>
                                <a href="{{ route('cabinet.sites.integrations.config', [$site, $service]) }}" class="btn btn-sm btn-alt-primary">
                                    <i class="fa fa-cog me-1"></i> Настроить
                                </a>
                            @else
                                <a href="{{ route('cabinet.sites.integrations.config', [$site, $service]) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-plus me-1"></i> Подключить
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

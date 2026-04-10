@extends('cabinet.layouts.cabinet')

@section('title', $notification->data['title'])

@section('content')
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">{{ $notification->data['title'] }}</h3>
            <div class="block-options">
                <span class="text-muted fs-sm">{{ $notification->created_at->format('d.m.Y H:i') }}</span>
            </div>
        </div>
        <div class="block-content fs-sm">
            <div class="mb-4">
                {!! $notification->data['message'] !!}
            </div>

            @if(isset($notification->data['action_url']))
                <div class="mb-4">
                    <a href="{{ $notification->data['action_url'] }}" class="btn btn-alt-primary">
                        Перейти к действию
                    </a>
                </div>
            @endif
        </div>
        <div class="block-content block-content-full block-content-sm bg-body-light fs-sm">
            <a class="fw-medium" href="{{ route('cabinet.messages.index') }}">
                <i class="fa fa-arrow-left me-1"></i> Назад к списку
            </a>
        </div>
    </div>
@endsection

@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        @include('cabinet.sites.form', ['site' => $site])
    </div>
@endsection

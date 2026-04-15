@extends('cabinet.layouts.cabinet')

@section('content')
    <div class="content">
        <div class="block block-rounded mb-4">
            <div class="block-content py-3">
                <form action="{{ route('cabinet.dashboard') }}" method="GET">
                    <select name="site_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Все проекты</option>
                        @foreach($sites as $s)
                            <option value="{{ $s->id }}" {{ request('site_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {!! $content !!}
    </div>
@endsection

@php
    $isEdit = isset($site);
    $action = $isEdit ? route('cabinet.sites.update', $site) : route('cabinet.sites.store');
@endphp

<div class="block block-rounded">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <i class="fa {{ $isEdit ? 'fa-pencil-alt' : 'fa-plus' }} me-1"></i>
            {{ $isEdit ? 'Редактирование проекта' : 'Добавление нового сайта' }}
        </h3>
    </div>
    <div class="block-content">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="row items-push">
                <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                        Основные данные проекта. Название поможет вам ориентироваться в списке сайтов, а домен необходим для работы виджетов.
                    </p>
                </div>
                <div class="col-lg-8 col-xl-5">
                    <div class="mb-4">
                        <label class="form-label" for="site-name">Название проекта</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="site-name" name="name"
                               value="{{ old('name', $site->name ?? '') }}"
                               placeholder="Например: Клиника в Краснодаре">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="site-domain">Домен</label>
                        <input type="text" class="form-control @error('domain') is-invalid @enderror"
                               id="site-domain" name="domain"
                               value="{{ old('domain', $site->domain ?? '') }}"
                               {{ $isEdit ? 'readonly' : '' }}
                               placeholder="example.com">
                        @if($isEdit)
                            <div class="form-text text-warning fs-xs">Домен нельзя изменить после создания проекта.</div>
                        @endif
                        @error('domain')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="site-email">Email для лидов</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="site-email" name="email"
                               value="{{ old('email', $site->email ?? auth()->user()->email) }}"
                               placeholder="admin@example.com">
                        <div class="form-text fs-xs">На этот адрес будут приходить заявки с виджетов.</div>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($isEdit)
                        <div class="mb-4">
                            <label class="form-label">Ваш API Key</label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-alt" value="{{ $site->api_key }}" readonly>
                                <button type="button" class="btn btn-alt-secondary" onclick="One.helpers('jq-notify', {type: 'info', icon: 'fa fa-info-circle', message: 'Ключ скопирован!'});">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <button type="submit" class="btn btn-alt-primary">
                            {{ $isEdit ? 'Обновить проект' : 'Создать проект' }}
                        </button>
                        <a href="{{ route('cabinet.sites.index') }}" class="btn btn-alt-secondary ms-2">Отмена</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

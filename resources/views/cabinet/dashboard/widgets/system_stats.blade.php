<div class="row">
    <div class="col-6 col-md-4">
        <a class="block block-rounded block-link-shadow text-center" href="{{ route('cabinet.sites.index') }}">
            <div class="block-content block-content-full">
                <div class="fs-2 fw-semibold text-primary">{{ $sites_count }}</div>
            </div>
            <div class="block-content py-2 bg-body-light">
                <p class="fw-medium fs-sm text-muted mb-0">Сайтов</p>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
            <div class="block-content block-content-full">
                <div class="fs-2 fw-semibold text-dark">{{ $widgets_count }}</div>
            </div>
            <div class="block-content py-2 bg-body-light">
                <p class="fw-medium fs-sm text-muted mb-0">Виджетов</p>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-4">
        <a class="block block-rounded block-link-shadow text-center" href="javascript:void(0)">
            <div class="block-content block-content-full">
                <div class="fs-2 fw-semibold text-success">{{ $active_sessions }}</div>
            </div>
            <div class="block-content py-2 bg-body-light">
                <p class="fw-medium fs-sm text-muted mb-0">Активные сессии</p>
            </div>
        </a>
    </div>
</div>

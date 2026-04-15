<div class="row">
    <div class="col-6 col-lg-3">
        <div class="block block-rounded shadow-sm">
            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                <div>
                    <p class="fs-sm fw-semibold text-muted text-uppercase mb-1">Всего лидов</p>
                    <p class="fs-2 fw-bold mb-0">{{ $total_leads }}</p>
                </div>
                <div class="item item-rounded bg-primary-light">
                    <i class="fa fa-users fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="block block-rounded shadow-sm">
            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                <div>
                    <p class="fs-sm fw-semibold text-muted text-uppercase mb-1">Новые</p>
                    <p class="fs-2 fw-bold text-success mb-0">{{ $new_leads }}</p>
                </div>
                <div class="item item-rounded bg-success-light">
                    <i class="fa fa-user-plus fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="block block-rounded shadow-sm">
            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                <div>
                    <p class="fs-sm fw-semibold text-muted text-uppercase mb-1">Сайтов</p>
                    <p class="fs-2 fw-bold text-info mb-0">{{ $sites_count }}</p>
                </div>
                <div class="item item-rounded bg-info-light">
                    <i class="fa fa-globe fs-3 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

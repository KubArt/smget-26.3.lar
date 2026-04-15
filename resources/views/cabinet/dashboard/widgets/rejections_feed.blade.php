<div class="block block-rounded shadow-sm">
    <div class="block-header block-header-default">
        <h3 class="block-title">📉 Почему уходят клиенты?</h3>
    </div>
    <div class="block-content">
        <ul class="list-group list-group-flush pb-3">
            @forelse($rejections as $rejected)
                <li class="list-group-item px-0">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">{{ $rejected->lead->phone }}</span>
                        <span class="text-muted fs-xs">{{ $rejected->created_at->format('d.m H:i') }}</span>
                    </div>
                    <div class="text-italic fs-sm text-danger mt-1">
                        «{{ $rejected->comment ?? 'Причина не указана' }}»
                    </div>
                </li>
            @empty
                <p class="text-center text-muted fs-sm py-3">Отказов не зафиксировано</p>
            @endforelse
        </ul>
    </div>
</div>

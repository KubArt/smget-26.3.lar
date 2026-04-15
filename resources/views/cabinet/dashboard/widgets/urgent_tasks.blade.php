<div class="block block-rounded shadow-sm mt-3">
    <div class="block-header block-header-default">
        <h3 class="block-title text-danger">
            <i class="fa fa-exclamation-triangle me-1"></i> Требуют внимания
        </h3>
    </div>
    <div class="block-content pb-3">
        <table class="table table-vcenter table-hover fs-sm">
            <thead>
            <tr>
                <th>Клиент</th>
                <th>Задача</th>
                <th class="text-end">Просрочено на</th>
            </tr>
            </thead>
            <tbody>
            @forelse($urgent_tasks as $task)
                <tr>
                    <td>
                        <a class="fw-semibold" href="{{ route('cabinet.crm.leads.show', $task->lead_id) }}">
                            {{ $task->lead->client->full_name ?? $task->lead->phone }}
                        </a>
                    </td>
                    <td>{{ $task->title }}</td>
                    <td class="text-end text-danger fw-bold">
                        {{ $task->due_date->diffForHumans() }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">Все задачи выполнены!</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

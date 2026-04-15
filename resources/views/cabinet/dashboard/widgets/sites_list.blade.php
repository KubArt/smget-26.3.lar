<div class="block block-rounded shadow-sm mt-4">
    <div class="block-header block-header-default">
        <h3 class="block-title">Обзор проектов</h3>
    </div>
    <div class="block-content">
        <table class="table table-striped table-vcenter">
            <thead>
            <tr>
                <th>Название</th>
                <th>Домен</th>
                <th class="text-center">Виджеты</th>
                <th>Статус</th>
                <th class="text-center">Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sites as $site)
                <tr>
                    <td class="fw-semibold fs-sm">{{ $site->name }}</td>
                    <td class="fs-sm">{{ $site->domain }}</td>
                    <td class="text-center">
                        <span class="badge bg-info">{{ $site->widgets_count ?? 0 }}</span>
                    </td>
                    <td><span class="badge bg-success">Активен</span></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="{{ route('cabinet.sites.show', $site->id) }}" class="btn btn-sm btn-alt-secondary">
                                <i class="fa fa-pencil-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

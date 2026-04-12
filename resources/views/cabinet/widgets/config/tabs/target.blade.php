<div class="row">
    <div class="col-md-8">
        <h4 class="fw-normal border-bottom pb-2">Нацеливание по страницам</h4>
        <div class="mb-4">
            <label class="form-label text-success">Разрешенные страницы</label>
            <textarea name="target_paths[allow]" class="form-control" rows="3" placeholder="/about*">{{ implode("\n", $widget->target_paths['allow'] ?? []) }}</textarea>
        </div>
        <div class="mb-4">
            <label class="form-label text-danger">Запрещенные страницы</label>
            <textarea name="target_paths[disallow]" class="form-control" rows="3" placeholder="/admin/*">{{ implode("\n", $widget->target_paths['disallow'] ?? []) }}</textarea>
        </div>

        <h4 class="fw-normal border-bottom pb-2 mt-5">Нацеливание по UTM-Меткам</h4>
        <div id="utm-groups-container">
            @php $utmGroups = $widget->target_utm ?? [[]]; @endphp
            @foreach($utmGroups as $gIndex => $group)
                <div class="utm-group block block-bordered block-rounded mb-3" data-group="{{ $gIndex }}">
                    <div class="block-header bg-body-light">
                        <h3 class="block-title fs-sm">Группа условий (И)</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option text-danger" onclick="this.closest('.utm-group').remove()">
                                <i class="fa fa-times"></i> Удалить группу
                            </button>
                        </div>
                    </div>
                    <div class="block-content pb-3">
                        <div class="utm-rules-list">
                            @foreach($group ?: [['key' => 'utm_source', 'val' => '']] as $rIndex => $rule)
                                <div class="row g-2 mb-2 utm-rule">
                                    <div class="col-5">
                                        <select name="target_utm[{{$gIndex}}][{{$rIndex}}][key]" class="form-select form-select-sm">
                                            <option value="utm_source" @selected($rule['key'] == 'utm_source')>utm_source</option>
                                            <option value="utm_medium" @selected($rule['key'] == 'utm_medium')>utm_medium</option>
                                            <option value="utm_campaign" @selected($rule['key'] == 'utm_campaign')>utm_campaign</option>
                                            <option value="custom" @selected(!in_array($rule['key'], ['utm_source', 'utm_medium', 'utm_campaign']))>Свой ключ...</option>
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" name="target_utm[{{$gIndex}}][{{$rIndex}}][val]" class="form-control form-control-sm" value="{{ $rule['val'] }}" placeholder="Значение">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('.utm-rule').remove()">И</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-alt-secondary py-0 mt-2" onclick="addUtmRule(this)">+ добавить И</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-alt-primary" onclick="addUtmGroup()">+ Добавить условие ИЛИ</button>
    </div>

    <div class="col-md-4">
        <div class="alert alert-info fs-sm shadow-sm">
            <h5 class="alert-heading h6 mb-3">
                <i class="fa fa-info-circle me-1"></i> Правила работы таргетинга
            </h5>

            <p class="mb-2"><strong>Маски путей (URL):</strong></p>
            <ul class="ps-3 mb-3">
                <li><code>/about</code> — строгое соответствие адресу.</li>
                <li><code>/blog*</code> — адрес начинается с /blog и все вложенные.</li>
                <li><code>*cart*</code> — слово "cart" в любой части адреса.</li>
                <li><code>*/prices/</code> — раздел /prices/ в любой части пути.</li>
            </ul>

            <div class="bg-white-10 p-2 rounded mb-3 border-start border-3 border-warning">
                <strong>Важно:</strong> Нацеливание по URL и UTM объединяется условием <b>«И»</b>.
                Виджет покажется только если совпали и страница, и метки.
            </div>

            <p class="mb-1"><strong>Логика UTM:</strong></p>
            <ul class="ps-3 mb-3">
                <li>Внутри группы — <b>«И»</b> (все метки сразу).</li>
                <li>Между группами — <b>«ИЛИ»</b> (хотя бы одна группа).</li>
            </ul>

            <div class="alert alert-warning py-2 mb-0 border-0">
                <i class="fa fa-exclamation-triangle me-1"></i>
                Если настроен таргет по UTM, виджет <b>не будет</b> показан на страницах без этих меток.
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        function addUtmRule(btn) {
            const group = btn.closest('.utm-group');
            const gIdx = group.dataset.group;
            const rIdx = group.querySelectorAll('.utm-rule').length;
            const html = `
        <div class="row g-2 mb-2 utm-rule">
            <div class="col-5">
                <input type="text" name="target_utm[${gIdx}][${rIdx}][key]" class="form-control form-control-sm" placeholder="Ключ">
            </div>
            <div class="col-5">
                <input type="text" name="target_utm[${gIdx}][${rIdx}][val]" class="form-control form-control-sm" placeholder="Значение">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('.utm-rule').remove()">И</button>
            </div>
        </div>`;
            group.querySelector('.utm-rules-list').insertAdjacentHTML('beforeend', html);
        }

        function addUtmGroup() {
            const container = document.getElementById('utm-groups-container');
            const gIdx = container.querySelectorAll('.utm-group').length;
            // Логика аналогична, вставляем пустую группу
        }
        function addUtmGroup() {
            const container = document.getElementById('utm-groups-container');
            const gIdx = container.querySelectorAll('.utm-group').length;

            const groupHtml = `
            <div class="utm-group block block-bordered block-rounded mb-3" data-group="${gIdx}">
                <div class="block-header bg-body-light">
                    <h3 class="block-title fs-sm text-primary">Новая группа условий (ИЛИ)</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option text-danger" onclick="this.closest('.utm-group').remove()">
                            <i class="fa fa-times me-1"></i>Удалить
                        </button>
                    </div>
                </div>
                <div class="block-content pb-3">
                    <div class="utm-rules-list">
                        <div class="row g-2 mb-2 utm-rule">
                            <div class="col-5">
                                <select name="target_utm[${gIdx}][0][key]" class="form-select form-select-sm">
                                    <option value="utm_source">utm_source</option>
                                    <option value="utm_medium">utm_medium</option>
                                    <option value="utm_campaign">utm_campaign</option>
                                    <option value="custom">Свой ключ...</option>
                                </select>
                            </div>
                            <div class="col-5">
                                <input type="text" name="target_utm[${gIdx}][0][val]" class="form-control form-control-sm" placeholder="Значение">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-alt-secondary py-0 mt-2" onclick="addUtmRule(this)">+ добавить И</button>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', groupHtml);
        }

        // Обработка выбора "Свой ключ..."
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('form-select') && e.target.value === 'custom') {
                const parent = e.target.parentElement;
                const name = e.target.name;
                parent.innerHTML = `<input type="text" name="${name}" class="form-control form-control-sm" placeholder="Введите ключ (напр. gclid)">`;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const allowArea = document.querySelector('textarea[name="target_paths[allow]"]');
            const disallowArea = document.querySelector('textarea[name="target_paths[disallow]"]');

            function checkIntersections() {
                // Получаем массивы строк, очищаем от пробелов и пустых строк
                const allowLines = allowArea.value.split('\n').map(s => s.trim()).filter(s => s !== "");
                const disallowLines = disallowArea.value.split('\n').map(s => s.trim()).filter(s => s !== "");

                // Ищем пересечения
                const conflicts = allowLines.filter(line => disallowLines.includes(line));

                if (conflicts.length > 0) {
                    allowArea.classList.add('is-invalid');
                    disallowArea.classList.add('is-invalid');

                    // Если нужно, можно выводить сообщение под полем
                    console.warn('Конфликт путей: ', conflicts);
                } else {
                    allowArea.classList.remove('is-invalid');
                    disallowArea.classList.remove('is-invalid');
                }
            }

            allowArea.addEventListener('input', checkIntersections);
            disallowArea.addEventListener('input', checkIntersections);
        });
    </script>
@endpush

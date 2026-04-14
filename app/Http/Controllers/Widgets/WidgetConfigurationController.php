<?php


namespace App\Http\Controllers\Widgets;


use App\Http\Controllers\Cabinet\BaseCabinetController;
use App\Models\Site;
use App\Models\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class WidgetConfigurationController extends BaseCabinetController
{
    public function edit(Site $site, Widget $widget)
    {
        // Проверка прав доступа (владеет ли юзер этим сайтом)
        // $this->authorize('update', $site);

        return view('cabinet.widgets.config', [
            'site' => $site,
            'widget' => $widget,
            'config' => $widget->settings,
            'target' => $widget->target_paths ?? ['allow' => [], 'disallow' => []],
        ]);
    }

    public function update(Request $request, Site $site, Widget $widget)
    {
        // 1. Предварительная очистка UTM от пустых значений, чтобы не срабатывала валидация
        $utmData = $request->input('target_utm', []);
        foreach ($utmData as $gIdx => $group) {
            foreach ($group as $rIdx => $rule) {
                if (empty($rule['val']) && ($rule['key'] == 'utm_source' || empty($rule['key']))) {
                    unset($utmData[$gIdx][$rIdx]);
                }
            }
            if (empty($utmData[$gIdx])) unset($utmData[$gIdx]);
        }
        $request->merge(['target_utm' => $utmData]);

        // 2. Валидация
        $validated = $request->validate([
            'custom_name' => 'nullable|string|max:255',
            'is_active' => 'nullable',
            'privacy_policy_type' => 'required|in:system,custom,none',
            'privacy_policy_url' => 'required_if:privacy_policy_type,custom|nullable|url',

//            'settings' => 'required|array',
            //'settings.delay' => 'required|integer|min:0',

            'target_paths.allow' => 'nullable|string',
            'target_paths.disallow' => 'nullable|string',
            'target_utm' => 'nullable|array',
            'target_utm.*.*.key' => 'required_with:target_utm.*.*.val|string',
            'target_utm.*.*.val' => 'required_with:target_utm.*.*.key|string',
        ], [
            'custom_name.required' => 'Укажите внутреннее название виджета.',
            'privacy_policy_url.required_if' => 'Укажите ссылку на вашу политику конфиденциальности.',
            'privacy_policy_url.url' => 'Ссылка на политику должна быть валидным URL.',

            'settings.text.required' => 'Текст сообщения обязателен.',
            'settings.button_text.required' => 'Текст кнопки обязателен.',
            'target_utm.*.*.val.required_with' => 'Вы выбрали ключ UTM, но не указали его значение.',
        ], [
            'custom_name' => '"Внутреннее название"',
            'settings.text' => '"Текст сообщения"',
            'settings.delay' => '"Задержка"',
        ]);

        try {
            DB::beginTransaction();

            // Обработка текстовых путей в массив
            $processPaths = function($str) {
                return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $str)))));
            };

            $widget->update([
                'custom_name' => $validated['custom_name'],
                'is_active' => $request->has('is_active'),
                // Сохраняем настройки политики в отдельный JSON или поля
                'privacy_config' => [
                    'type' => $request->input('privacy_policy_type'),
                    'url' => $request->input('privacy_policy_url'),
                ],
                //'settings' => $validated['settings'],
                'target_paths' => [
                    'allow' => $processPaths($request->input('target_paths.allow')),
                    'disallow' => $processPaths($request->input('target_paths.disallow')),
                ],
                'target_utm' => $utmData,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Настройки успешно сохранены');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Widget Save Error: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Ошибка при сохранении данных.');
        }
    }
}

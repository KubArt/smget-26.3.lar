<?php


namespace App\Widgets;

use App\Models\Widget;

interface WidgetContract
{
    /** Возвращает путь к Blade-форме настроек дизайна */
    public function getDesignForm(): string;

    /** Возвращает массив данных для JS-редактора (превью) */
    public function getEditorConfig(Widget $widget): array;

    /** Логика валидации и сохранения специфичных полей дизайна */
    public function updateDesign(Widget $widget, array $data): bool;
}

<?php


namespace App\Widgets;

use App\Models\Widget;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WidgetFactory
{
    public static function make(Widget $widget): WidgetContract
    {
        $class = "App\\Widgets\\" . Str::studly($widget->widgetType->slug) . "Widget";
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Класс для виджета [{$widget->widgetType->slug}] не найден.");
        }
        return new $class();
    }
}

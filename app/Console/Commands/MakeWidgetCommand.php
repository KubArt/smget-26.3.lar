<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeWidgetCommand extends Command
{
    // Название команды: php artisan make:widget CookiePops
    protected $signature = 'make:widget {name}';
    protected $description = 'Создать структуру нового виджета по паттерну Абстрактная фабрика';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $slug = Str::kebab($name);

        $this->info("Создание виджета: {$name}...");

        // 1. Создаем класс виджета в app/Widgets
        $this->createWidgetClass($name, $slug);

        // 2. Создаем Blade-форму настроек
        $this->createBladeForm($slug);

        // 3. Создаем структуру скинов в public
        $this->createSkinStructure($slug);

        $this->info("Виджет {$name} успешно создан!");
        $this->line("Не забудьте добавить роуты и обновить WidgetFactory.");
    }

    protected function createWidgetClass($name, $slug)
    {
        $path = app_path("Widgets/{$name}Widget.php");

        $content = "<?php\n\nnamespace App\Widgets;\n\nuse App\Models\Widget;\n\nclass {$name}Widget implements WidgetContract\n{\n    public function getDesignForm(): string\n    {\n        return 'cabinet.widgets.forms.{$slug}';\n    }\n\n    public function getEditorConfig(Widget \$widget): array\n    {\n        return [\n            'slug' => '{$slug}',\n            'settings' => \$widget->settings,\n        ];\n    }\n\n    public function updateDesign(Widget \$widget, array \$data): bool\n    {\n        \$settings = \$widget->settings;\n        \$settings['design'] = \$data['design'] ?? [];\n        \$settings['template'] = \$data['template'] ?? 'default';\n        \n        return \$widget->update(['settings' => \$settings]);\n    }\n}\n";

        File::put($path, $content);
        $this->comment("Класс создан: {$path}");
    }

    protected function createBladeForm($slug)
    {
        $path = resource_views_path("cabinet/widgets/forms/{$slug}.blade.php");
        File::ensureDirectoryExists(dirname($path));

        $content = "\n<div class=\"mb-4\">\n    <label class=\"form-label\">Выберите шаблон</label>\n    <select name=\"template\" class=\"form-select\" x-model=\"settings.template\" @change=\"applyTemplate(\$event.target.value)\">\n        <template x-for=\"skin in skins\" :key=\"skin.slug\">\n            <option :value=\"skin.slug\" x-text=\"skin.name\"></option>\n        </template>\n    </select>\n</div>";

        File::put($path, $content);
        $this->comment("Blade-форма создана: {$path}");
    }

    protected function createSkinStructure($slug)
    {
        $path = public_path("widgets/{$slug}/skins/default");
        File::ensureDirectoryExists($path);

        File::put("{$path}/template.html", "<div>New Widget {text}</div>");
        File::put("{$path}/style.css", ".sp-skin-default { position: fixed; bottom: 20px; }");

        $this->comment("Папка скинов создана: {$path}");
    }
}

// Хелпер, если функция не определена (зависит от версии Laravel)
function resource_views_path($path) {
    return base_path('resources/views/' . $path);
}

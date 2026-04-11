Вызвать `WidgetManager` можно несколькими способами в зависимости от того, где тебе нужно запустить синхронизацию: вручную через консоль, автоматически при деплое или прямо в коде.

Вот основные варианты:

### Вариант 1: Через консольную команду (Artisan)
Это самый правильный способ для разработки. Ты создал папку с новым виджетом и просто запускаешь команду в терминале.

Сначала обнови саму команду в `app/Console/Commands/SyncWidgets.php`:

```php
namespace App\Console\Commands;

use App\Services\WidgetManager; // Импортируем наш сервис
use Illuminate\Console\Command;

class SyncWidgets extends Command
{
    protected $signature = 'app:sync-widgets';
    protected $description = 'Сканирует папку Widgets и синхронизирует типы виджетов с БД';

    public function handle(WidgetManager $manager)
    {
        $this->info('Начинаю синхронизацию виджетов...');
        
        $manager->sync(); // Вызываем метод синхронизации
        
        $this->info('Готово! Все виджеты актуализированы в базе данных.');
    }
}
```

**Запуск в терминале:**
```bash
php artisan app:sync-widgets
```

---

### Вариант 2: Вызов в любом месте кода (Контроллер или Тест)
Если тебе нужно вызвать синхронизацию программно (например, в админке нажали кнопку "Обновить список доступных виджетов"), используй **Dependency Injection** (внедрение зависимостей) или `app()`.

**Через контроллер:**
```php
use App\Services\WidgetManager;

public function updateWidgets(WidgetManager $manager)
{
    $manager->sync();
    return back()->with('success', 'Список виджетов обновлен');
}
```

**Через фасад или хелпер (в любом месте):**
```php
app(\App\Services\WidgetManager::class)->sync();
```

---

### Вариант 3: Автоматически при миграции (Seeder)
Чтобы при установке проекта на новый сервер (или у коллеги) виджеты сразу появились в базе, добавь вызов в `DatabaseSeeder.php`.

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    app(\App\Services\WidgetManager::class)->sync();
}
```
Тогда при выполнении `php artisan db:seed` или `php artisan migrate --seed` база сразу наполнится доступными типами виджетов.

---

### Что произойдет при вызове:
1. Сервис найдет твою новую папку `app/Widgets/CookiePops`.
2. Обратится к `Manifest::config()`.
3. Создаст или обновит запись в таблице `widget_types`.
4. Теперь в кабинете пользователя в списке "Добавить виджет" появится **Cookie Pops**, так как он уже есть в БД.

**Рекомендация:** На этапе активной разработки лучше всего использовать **Вариант 1** (Artisan команду). Сделал изменения в манифесте — прописал команду в консоли — проверил результат.

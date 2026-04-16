<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearAllCommand extends Command
{
    protected $signature = 'clear:all';
    protected $description = 'Полная очистка всех видов кэша приложения';

    public function handle()
    {
        $this->warn('Начинаю полную очистку кэша...');

        // Очистка стандартного кэша Laravel
        Artisan::call('optimize:clear');
        $this->line('✓ Сброс конфигурации, роутов, вьюх и кэша данных');

        // Очистка кэша сессий (если файловые)
        //Artisan::call('cache:forget', ['key' => 'spatie.permission.cache']); // Если есть роли

        // Дополнительно: если используете Redis или специфичный тег
        // Artisan::call('cache:clear', ['--tags' => 'widgets']);

        $this->info('Система полностью очищена.');
    }
}

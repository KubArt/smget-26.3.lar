<?php

namespace App\Console\Commands;

use App\Services\WidgetManager;
use Illuminate\Console\Command;

class SyncWidgets extends Command
{
    // php artisan app:sync-widgets
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-widgets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(WidgetManager $manager)
    {
        $this->info('Начинаю синхронизацию виджетов...');
        $manager->discoverAndSync();
        $this->info('Виджеты успешно синхронизированы с БД!');
    }
}

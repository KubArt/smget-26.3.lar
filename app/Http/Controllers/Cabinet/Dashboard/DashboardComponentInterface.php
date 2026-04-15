<?php

namespace App\Http\Controllers\Cabinet\Dashboard;

use App\Models\User;
use App\Models\Site;

interface DashboardComponentInterface
{
    /**
     * Возвращает массив данных для вывода в Blade
     */
    public function getData(User $user, ?Site $site = null): array;
    public function getTemplate(): string;
}

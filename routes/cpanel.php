<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Это панель управления всей системой (CPANEL)';
})->name('index');

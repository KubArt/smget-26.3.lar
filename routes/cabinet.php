<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Это главная страница кабинета клиента';
})->name('index');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cabinet\Crm\LeadController;
use App\Http\Controllers\Cabinet\Crm\TaskController;
use App\Http\Controllers\Cabinet\Crm\FunnelController;
use App\Http\Controllers\Cabinet\Crm\ClientController;

Route::prefix('crm')->name('crm.')->group(function () {

    // Лиды
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::post('/leads/{lead}/notes', [LeadController::class, 'storeNote'])->name('leads.notes.store');
    Route::post('/leads/{lead}/tasks', [LeadController::class, 'storeTask'])->name('leads.tasks.store');

    // Клиенты
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::post('/clients/{client}/notes', [ClientController::class, 'storeNote'])->name('clients.notes.store');

    // Полное удаление физически
    Route::get('/clients/{client}/force-delete', [ClientController::class, 'forceDestroy'])
        ->name('clients.force-delete');

    // Задачи и напоминания
    Route::get('/tasks', [TaskController::class, 'tasks'])->name('tasks.index');
    Route::post('/tasks/{task}/toggle', [TaskController::class, 'toggleTask'])->name('tasks.toggle');

    // Смена стадии воронки
    Route::post('/leads/{lead}/stage', [FunnelController::class, 'updateStage'])->name('leads.stage.update');



});

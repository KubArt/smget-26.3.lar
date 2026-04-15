<?php

use Illuminate\Support\Facades\Route;

Route::get('/v1/get-widgets', [\App\Http\Controllers\Widgets\WidgetDeliveryController::class, 'getPayload']);

Route::post('/v1/track', [\App\Http\Controllers\Widgets\WidgetDeliveryController::class, 'track']);



Route::post('/v1/capture/{source}', [\App\Http\Controllers\Api\LeadCaptureController::class, 'capture']);

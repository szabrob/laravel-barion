<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Http\Controllers\BarionController;

Route::controller(BarionController::class)->name('workbench.barion.')->group(function (): void {
    Route::get('/', 'index')->name('index');
    Route::post('/payments', 'start')->name('start');
    Route::post('/payments/lookup', 'lookup')->name('lookup');
    Route::post('/payments/clear', 'clear')->name('clear');
    Route::get('/return', 'return')->name('return');
    Route::get('/payments/{paymentId}', 'show')->name('show');
    Route::post('/payments/{paymentId}/callback', 'callback')->name('callback');
    Route::post('/payments/{paymentId}/refund', 'refund')->name('refund');
    Route::post('/payments/{paymentId}/finish', 'finish')->name('finish');
    Route::post('/payments/{paymentId}/capture', 'capture')->name('capture');
    Route::post('/payments/{paymentId}/cancel', 'cancel')->name('cancel');
    Route::post('/payments/{paymentId}/complete', 'complete')->name('complete');
    Route::post('/payments/{paymentId}/schedule', 'schedule')->name('schedule');
    Route::post('/schedules/{scheduleId}/charge', 'chargeSchedule')->name('schedules.charge');
    Route::post('/schedules/{scheduleId}/delete', 'deleteSchedule')->name('schedules.delete');
});

<?php

use App\Modules\Core\Notification\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->middleware('throttle:120,1')->name('index');
    Route::patch('/read-all', [NotificationController::class, 'readAll'])->middleware('throttle:60,1')->name('read-all');
    Route::patch('/{notification}/read', [NotificationController::class, 'read'])
        ->whereUuid('notification')->middleware('throttle:60,1')->name('read');
});

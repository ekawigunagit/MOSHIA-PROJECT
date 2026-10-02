<?php

use App\Modules\Core\Identity\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->whereNumber('user')->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->whereNumber('user')
        ->middleware('throttle:30,1')->name('users.update');
});

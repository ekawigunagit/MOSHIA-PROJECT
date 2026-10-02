<?php

use App\Modules\Core\Billing\Http\Controllers\PlanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:super-admin'])->prefix('admin/plans')->name('admin.plans.')->group(function () {
    Route::get('/', [PlanController::class, 'index'])->name('index');
    Route::get('/create', [PlanController::class, 'create'])->name('create');
    Route::post('/', [PlanController::class, 'store'])->middleware('throttle:30,1')->name('store');
    Route::get('/{plan}/edit', [PlanController::class, 'edit'])->whereNumber('plan')->name('edit');
    Route::patch('/{plan}', [PlanController::class, 'update'])->whereNumber('plan')->middleware('throttle:30,1')->name('update');
});

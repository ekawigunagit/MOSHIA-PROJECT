<?php

use App\Modules\Core\Billing\Http\Controllers\ManualPaymentController;
use App\Modules\Core\Billing\Http\Controllers\PlanController;
use App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:super-admin'])->prefix('admin/plans')->name('admin.plans.')->group(function () {
    Route::get('/', [PlanController::class, 'index'])->name('index');
    Route::get('/create', [PlanController::class, 'create'])->name('create');
    Route::post('/', [PlanController::class, 'store'])->middleware('throttle:30,1')->name('store');
    Route::get('/{plan}/edit', [PlanController::class, 'edit'])->whereNumber('plan')->name('edit');
    Route::patch('/{plan}', [PlanController::class, 'update'])->whereNumber('plan')->middleware('throttle:30,1')->name('update');
});

Route::middleware([DevelopmentPayments::class, 'auth', 'verified'])->group(function () {
    Route::prefix('workspaces/{tenant}/billing')->whereNumber('tenant')->name('billing.')->group(function () {
        Route::get('/', [ManualPaymentController::class, 'index'])->name('index');
        Route::post('/', [ManualPaymentController::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::post('/{order}/cancel', [ManualPaymentController::class, 'cancel'])->whereNumber('order')->middleware('throttle:20,1')->name('cancel');
    });
    Route::middleware('role:super-admin')->prefix('admin/payments')->name('admin.payments.')->group(function () {
        Route::get('/', [ManualPaymentController::class, 'admin'])->name('index');
        Route::post('/{order}/accept', [ManualPaymentController::class, 'accept'])->whereNumber('order')->middleware('throttle:30,1')->name('accept');
    });
});

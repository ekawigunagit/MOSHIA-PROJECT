<?php

use App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments;
use App\Modules\Wedding\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;

Route::middleware([DevelopmentPayments::class, 'auth', 'verified'])
    ->prefix('workspaces/{tenant}/wedding')->whereNumber('tenant')->name('wedding.')->group(function () {
        Route::get('/', [InvitationController::class, 'edit'])->name('edit');
        Route::patch('/', [InvitationController::class, 'update'])->middleware('throttle:30,1')->name('update');
        Route::get('/preview', [InvitationController::class, 'preview'])->name('preview');
        Route::post('/publish', [InvitationController::class, 'publish'])->middleware('throttle:20,1')->name('publish');
    });

Route::get('/invitation/{slug}', [InvitationController::class, 'show'])
    ->middleware(DevelopmentPayments::class)->whereUuid('slug')->name('wedding.public');

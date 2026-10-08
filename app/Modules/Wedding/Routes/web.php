<?php
use App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments;
use App\Modules\Wedding\Http\Controllers\InvitationController;
use App\Modules\Wedding\Http\Controllers\MediaController;
use App\Modules\Wedding\Http\Controllers\ResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware([DevelopmentPayments::class, 'auth', 'verified'])
    ->prefix('workspaces/{tenant}/wedding')->whereNumber('tenant')->name('wedding.')->group(function () {
        Route::get('/', [InvitationController::class, 'edit'])->name('edit');
        Route::patch('/', [InvitationController::class, 'update'])->middleware('throttle:30,1')->name('update');
        Route::get('/preview', [InvitationController::class, 'preview'])->name('preview');
        Route::post('/publish', [InvitationController::class, 'publish'])->middleware('throttle:20,1')->name('publish');
        Route::post('/unpublish', [InvitationController::class, 'unpublish'])->middleware('throttle:20,1')->name('unpublish');
        Route::post('/media', [MediaController::class, 'store'])->middleware('throttle:20,1')->name('media.store');
        Route::get('/media/{media}', [MediaController::class, 'privateFile'])->whereNumber('media')->name('media.private');
        Route::patch('/responses/{response}', [ResponseController::class, 'moderate'])->whereNumber('response')->middleware('throttle:60,1')->name('responses.moderate');
    });
Route::middleware(DevelopmentPayments::class)->prefix('invitation/{slug}')->whereUuid('slug')->group(function () {
    Route::get('/', [InvitationController::class, 'show'])->name('wedding.public');
    Route::get('/media/{media}', [MediaController::class, 'publicFile'])->whereNumber('media')->name('wedding.media.public');
    Route::post('/responses', [ResponseController::class, 'store'])->middleware('throttle:10,1')->name('wedding.responses.store');
});

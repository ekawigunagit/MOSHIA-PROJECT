<?php
use App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments;
use App\Modules\Products\Wedding\Http\Controllers\InvitationController;
use App\Modules\Products\Wedding\Http\Controllers\MediaController;
use App\Modules\Products\Wedding\Http\Controllers\ResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware([DevelopmentPayments::class, 'auth', 'verified'])
    ->prefix('workspaces/{tenant}/wedding')->whereNumber('tenant')->name('wedding.')->group(function () {
        Route::get('/', [InvitationController::class, 'edit'])->middleware('can:wedding.invitations.view,tenant')->name('edit');
        Route::patch('/', [InvitationController::class, 'update'])->middleware('throttle:30,1')->middleware('can:wedding.invitations.update,tenant')->name('update');
        Route::get('/preview', [InvitationController::class, 'preview'])->middleware('can:wedding.invitations.view,tenant')->name('preview');
        Route::post('/publish', [InvitationController::class, 'publish'])->middleware('throttle:20,1')->middleware('can:wedding.invitations.publish,tenant')->name('publish');
        Route::post('/unpublish', [InvitationController::class, 'unpublish'])->middleware('throttle:20,1')->middleware('can:wedding.invitations.publish,tenant')->name('unpublish');
        Route::post('/media', [MediaController::class, 'store'])->middleware('throttle:20,1')->middleware('can:wedding.media.upload,tenant')->name('media.store');
        Route::get('/media/{media}', [MediaController::class, 'privateFile'])->whereNumber('media')->middleware('can:wedding.invitations.view,tenant')->name('media.private');
        Route::patch('/responses/{response}', [ResponseController::class, 'moderate'])->whereNumber('response')->middleware('throttle:60,1')->middleware('can:wedding.responses.moderate,tenant')->name('responses.moderate');
    });
$publicRoutes = function () {
    Route::get('/', function (\Illuminate\Http\Request $request, string $slug) {
        if (config('wedding_editor.public_domain') && $request->routeIs('wedding.legacy.public')) {
            return redirect()->to(route('wedding.public', ['slug' => $slug, ...$request->query()]));
        }
        return app(InvitationController::class)->show($slug, app(\App\Modules\Products\Wedding\Services\PublishedInvitation::class));
    })->name('public');
    Route::get('/media/{media}', [MediaController::class, 'publicFile'])->whereNumber('media')->name('media.public');
    Route::post('/responses', [ResponseController::class, 'store'])->middleware('throttle:10,1')->name('responses.store');
};
if ($domain = config('wedding_editor.public_domain')) {
    Route::domain($domain)->middleware(DevelopmentPayments::class)->prefix('{slug}')->whereUuid('slug')->name('wedding.')->group($publicRoutes);
}
Route::middleware(DevelopmentPayments::class)->prefix('invitation/{slug}')->whereUuid('slug')
    ->name(config('wedding_editor.public_domain') ? 'wedding.legacy.' : 'wedding.')->group($publicRoutes);

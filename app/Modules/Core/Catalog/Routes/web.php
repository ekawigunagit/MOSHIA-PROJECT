<?php

use App\Modules\Core\Catalog\Http\Controllers\DashboardController;
use App\Modules\Core\Catalog\ProductCatalog;
use App\Modules\Core\Tenancy\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->middleware('throttle:10,1')->name('workspaces.store');
    Route::post('/workspaces/{tenant}/select', [WorkspaceController::class, 'select'])->whereNumber('tenant')->name('workspaces.select');
});

Route::get('/admin', fn (ProductCatalog $catalog) => Inertia::render('Admin/Index', [
    'products' => $catalog->all(),
    'stats' => [
        'users' => \App\Models\User::count(),
        'workspaces' => \App\Modules\Core\Tenancy\Models\Tenant::count(),
        'products' => count($catalog->all()),
    ],
]))->middleware(['auth', 'verified', 'role:super-admin'])->name('admin.index');

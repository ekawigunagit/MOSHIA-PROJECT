<?php

use App\Models\User;
use App\Modules\Core\Catalog\Http\Controllers\DashboardController;
use App\Modules\Core\Catalog\Http\Controllers\ProductController;
use App\Modules\Core\Catalog\ProductCatalog;
use App\Modules\Core\Tenancy\Http\Controllers\WorkspaceController;
use App\Modules\Core\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/workspaces/create', fn () => Inertia::render('Workspaces/Create'))->name('workspaces.create');
    Route::get('/workspaces/{tenant}/edit', [WorkspaceController::class, 'edit'])->whereNumber('tenant')->name('workspaces.edit');
    Route::patch('/workspaces/{tenant}', [WorkspaceController::class, 'update'])->whereNumber('tenant')->middleware('throttle:30,1')->name('workspaces.update');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->middleware('throttle:10,1')->name('workspaces.store');
    Route::post('/workspaces/{tenant}/select', [WorkspaceController::class, 'select'])->whereNumber('tenant')->name('workspaces.select');
});

Route::get('/admin', fn (ProductCatalog $catalog) => Inertia::render('Admin/Index', [
    'products' => $catalog->all(includeHidden: true),
    'stats' => [
        'users' => User::count(),
        'workspaces' => Tenant::count(),
        'products' => count($catalog->all(includeHidden: true)),
    ],
]))->middleware(['auth', 'verified', 'role:super-admin'])->name('admin.index');

Route::middleware(['auth', 'verified', 'role:super-admin'])->prefix('admin/products')->name('admin.products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::patch('/{product}', [ProductController::class, 'update'])->middleware('throttle:30,1')->name('update');
});

Route::get('/products/{slug}', \App\Modules\Core\Catalog\Http\Controllers\ProductPlansController::class)->middleware(['auth', 'verified'])->name('products.plans');

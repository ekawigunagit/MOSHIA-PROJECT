<?php

namespace App\Modules\Core\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Catalog\ProductCatalog;
use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Tenancy\CurrentWorkspace;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ProductCatalog $catalog, CurrentWorkspace $current, ProductAccess $access): Response
    {
        $tenant = $current->resolve($request);

        return Inertia::render('Dashboard', [
            'workspaces' => $request->user()->tenants()->get(['core_tenants.id', 'core_tenants.name'])
                ->map(fn ($item) => $item->only(['id', 'name'])),
            'activeWorkspace' => $tenant?->only(['id', 'name']),
            'products' => array_map(fn ($product) => [
                ...$product,
                'hasAccess' => $tenant ? $access->allows($request->user(), $tenant, $product['id']) : false,
            ], $catalog->all()),
        ]);
    }
}

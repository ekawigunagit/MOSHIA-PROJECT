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
        $settledProducts = [];
        if ($tenant && $tenant->owner_id === $request->user()->id) {
            $billing = app(\App\Modules\Core\Billing\Actions\ManualWeddingBilling::class);
            $latest = $billing->latest($tenant);
            $order = \App\Modules\Core\Billing\Models\PurchaseOrder::where('tenant_id', $tenant->id)
                ->where('product_id', \App\Modules\Core\Catalog\Models\Product::where('slug', 'wedding')->value('id'))
                ->whereNotNull('paid_at')->whereNull('cancelled_at')->latest('id')->first();
            if ($order) {
                $settledProducts[] = [
                    'id' => $order->id, 'title' => 'Wedding Invitation',
                    'package' => $order->terms['label'] ?? 'Wedding',
                    'paid_at' => $order->paid_at, 'ends_at' => $order->ends_at,
                    'months' => $order->terms['validity_months'],
                    'status' => $order->statusAt(\Carbon\CarbonImmutable::now())->value,
                    'can_manage' => \App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments::enabled()
                        && $latest?->id === $order->id
                        && $order->lifecycle()->permitsEditorAt(\Carbon\CarbonImmutable::now())
                        && $access->allows($request->user(), $tenant, 'wedding'),
                ];
            }
        }

        return Inertia::render('Dashboard', [
            'workspaces' => $request->user()->tenants()->get(['core_tenants.id', 'core_tenants.name'])
                ->map(fn ($item) => $item->only(['id', 'name'])),
            'activeWorkspace' => $tenant?->only(['id', 'name']),
            'canManageWorkspace' => $tenant && $tenant->owner_id === $request->user()->id,
            'settledProducts' => $settledProducts,
            'products' => array_map(fn ($product) => [
                ...$product,
                'hasAccess' => $tenant ? $access->allows($request->user(), $tenant, $product['slug']) : false,
            ], $catalog->all()),
        ]);
    }
}

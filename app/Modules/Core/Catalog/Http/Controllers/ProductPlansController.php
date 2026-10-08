<?php

namespace App\Modules\Core\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Tenancy\CurrentWorkspace;
use App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductPlansController extends Controller
{
    public function __invoke(Request $request, string $slug, CurrentWorkspace $current): Response
    {
        $names = ['wedding' => 'Wedding Invitation', 'jastip' => 'Jastip Manager',
            'photobooth' => 'Photo Booth System', 'restaurant' => 'Restaurant Manager'];
        abort_unless(isset($names[$slug]), 404);
        $product = Product::where('slug', $slug)->where('status', 'planned')->firstOrFail();
        $workspace = $current->resolve($request);

        return Inertia::render('Products/Plans', [
            'product' => ['slug' => $slug, 'title' => $names[$slug], 'summary' => $product->summary],
            'packages' => $slug === 'wedding' ? array_values(config('wedding_plans.packages', [])) : [],
            'workspace' => $workspace?->only('id', 'name'),
            'canPurchase' => $workspace && $workspace->owner_id === $request->user()->id && DevelopmentPayments::enabled(),
        ]);
    }
}

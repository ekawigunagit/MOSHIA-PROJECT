<?php

namespace App\Modules\Core\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Catalog\Http\Requests\UpdateProductRequest;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Catalog\ProductCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(ProductCatalog $catalog): Response
    {
        Gate::authorize('viewAny', Product::class);

        return Inertia::render('Admin/Products/Index', [
            'products' => $catalog->all(includeHidden: true),
            'statuses' => config('moshia.catalog.statuses'),
        ]);
    }

    public function edit(Product $product): Response
    {
        Gate::authorize('update', $product);

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->catalogData(),
            'statuses' => config('moshia.catalog.statuses'),
            'icons' => config('moshia.catalog.icons'),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return to_route('admin.products.edit', $product)->with('success', 'Katalog produk berhasil diperbarui.');
    }
}

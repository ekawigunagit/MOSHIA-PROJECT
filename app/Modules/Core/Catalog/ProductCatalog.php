<?php

namespace App\Modules\Core\Catalog;

use App\Modules\Core\Catalog\Models\Product;

class ProductCatalog
{
    public function all(bool $includeHidden = false): array
    {
        return Product::query()
            ->when(! $includeHidden, fn ($query) => $query->where('status', 'planned'))
            ->orderBy('sort_order')->orderBy('slug')->get()
            ->map(fn (Product $product) => $product->catalogData())->all();
    }

    public function find(string $slug): ?array
    {
        // Visibility is not entitlement revocation; stable product slugs stay valid.
        return Product::where('slug', $slug)->first()?->catalogData();
    }
}

<?php

namespace App\Modules\Core\Catalog;

class ProductCatalog
{
    public function all(): array
    {
        return array_values(config('moshia.products', []));
    }

    public function find(string $slug): ?array
    {
        return config("moshia.products.$slug");
    }
}

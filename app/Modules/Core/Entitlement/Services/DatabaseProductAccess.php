<?php

namespace App\Modules\Core\Entitlement\Services;

use App\Models\User;
use App\Modules\Core\Catalog\ProductCatalog;
use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Models\Tenant;

class DatabaseProductAccess implements ProductAccess
{
    public function __construct(private ProductCatalog $catalog) {}

    public function allows(User $user, Tenant $tenant, string $product): bool
    {
        $catalogProduct = $this->catalog->find($product);
        if (! $catalogProduct || ! $tenant->members()->whereKey($user->id)->exists()) {
            return false;
        }

        $now = now();

        return Entitlement::query()
            ->where('tenant_id', $tenant->id)
            ->where('product_id', $catalogProduct['id'])
            ->whereIn('status', ['active', 'trial'])
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->exists();
    }
}

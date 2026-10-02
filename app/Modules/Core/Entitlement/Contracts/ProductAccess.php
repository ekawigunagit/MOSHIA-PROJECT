<?php

namespace App\Modules\Core\Entitlement\Contracts;

use App\Models\User;
use App\Modules\Core\Tenancy\Models\Tenant;

interface ProductAccess
{
    public function allows(User $user, Tenant $tenant, string $product): bool;
}

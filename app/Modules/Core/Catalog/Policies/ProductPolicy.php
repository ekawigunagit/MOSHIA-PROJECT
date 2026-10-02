<?php

namespace App\Modules\Core\Catalog\Policies;

use App\Models\User;
use App\Modules\Core\Catalog\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->hasRole('super-admin');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }
}

<?php

namespace App\Modules\Core\Identity\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->hasRole('super-admin');
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->hasRole('super-admin');
    }

    public function updateRoles(User $actor, User $target): bool
    {
        return $actor->hasRole('super-admin') && ! $actor->is($target);
    }
}

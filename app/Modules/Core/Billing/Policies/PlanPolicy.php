<?php

namespace App\Modules\Core\Billing\Policies;

use App\Models\User;
use App\Modules\Core\Billing\Models\Plan;

class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->hasRole('super-admin');
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Plan $plan): bool
    {
        return $this->viewAny($user) && $plan->status === 'draft';
    }
}

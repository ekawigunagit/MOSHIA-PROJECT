<?php

namespace App\Modules\Products\Wedding\Providers;

use App\Models\User;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class WeddingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Workspace-scoped abilities. Global roles never bypass ownership or entitlement.
        foreach (['invitations.view', 'invitations.update', 'invitations.publish',
            'media.upload', 'responses.moderate'] as $ability) {
            Gate::define('wedding.'.$ability, function (User $user, Tenant $tenant): bool {
                app(ManualWeddingBilling::class)->editorOrder($user, $tenant);
                return true;
            });
        }
    }
}

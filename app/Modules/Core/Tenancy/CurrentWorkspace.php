<?php

namespace App\Modules\Core\Tenancy;

use App\Modules\Core\Tenancy\Models\Tenant;
use Illuminate\Http\Request;

class CurrentWorkspace
{
    public function resolve(Request $request): ?Tenant
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        // Never trust a session tenant ID without checking current membership.
        $selected = $user->tenants()->find($request->session()->get('tenant_id'));

        return $selected ?? $user->tenants()->orderBy('core_tenants.id')->first();
    }
}

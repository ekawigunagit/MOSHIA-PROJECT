<?php

namespace App\Modules\Core\Tenancy\Actions;

use App\Models\User;
use App\Modules\Core\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;

class CreateWorkspace
{
    public function handle(User $owner, string $name): Tenant
    {
        return DB::transaction(function () use ($owner, $name) {
            // Coordinate workspace creation with account deletion.
            $owner = User::lockForUpdate()->findOrFail($owner->id);
            $tenant = Tenant::create(['owner_id' => $owner->id, 'name' => $name]);
            $tenant->members()->attach($owner->id);
            app(\App\Modules\Core\Notification\Actions\RecordNotification::class)->handle(
                $owner, 'Workspace berhasil dibuat', 'Workspace "'.$tenant->name.'" siap digunakan.'
            );

            return $tenant;
        });
    }
}

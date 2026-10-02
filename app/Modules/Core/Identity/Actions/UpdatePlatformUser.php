<?php

namespace App\Modules\Core\Identity\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UpdatePlatformUser
{
    public function handle(User $actor, User $target, array $data): void
    {
        DB::transaction(function () use ($actor, $target, $data) {
            // Serialize admin changes, then recheck the actor's current role.
            // Concurrent demotions must not authorize each other using stale roles.
            Role::where('guard_name', 'web')->where('name', 'super-admin')->lockForUpdate()->firstOrFail();
            $actor = User::findOrFail($actor->id);
            $target = User::lockForUpdate()->findOrFail($target->id);
            Gate::forUser($actor)->authorize('update', $target);

            if (array_key_exists('roles', $data)) {
                Gate::forUser($actor)->authorize('updateRoles', $target);
                $roles = Role::where('guard_name', 'web')->whereIn('name', $data['roles'])->get();
                if ($roles->count() !== count(array_unique($data['roles'])) || $roles->isEmpty()) {
                    throw ValidationException::withMessages(['roles' => 'Role yang dipilih tidak tersedia.']);
                }
                $target->syncRoles($roles);
            }

            $target->fill(['name' => $data['name'], 'email' => $data['email']]);
            if ($target->isDirty('email')) {
                $target->email_verified_at = null;
            }
            $target->save();
        }, 3);
    }
}

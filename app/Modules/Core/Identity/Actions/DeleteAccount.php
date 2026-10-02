<?php

namespace App\Modules\Core\Identity\Actions;

use App\Models\User;
use App\Modules\Core\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DeleteAccount
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Use the same lock order as role updates to serialize admin removal.
            $role = Role::where('guard_name', 'web')->where('name', 'super-admin')->lockForUpdate()->first();
            $account = User::lockForUpdate()->findOrFail($user->id);

            if ($role && $account->roles()->whereKey($role->id)->exists()
                && ! User::whereKeyNot($account->id)->whereHas('roles', fn ($query) => $query->whereKey($role->id))->exists()) {
                throw ValidationException::withMessages(['account' => 'Superadmin terakhir tidak dapat menghapus akun. Siapkan superadmin lain terlebih dahulu.']);
            }

            if (Tenant::where('owner_id', $account->id)->exists()) {
                throw ValidationException::withMessages(['account' => 'Akun masih memiliki workspace. Penghapusan ditolak agar data workspace tetap aman. Pengalihan atau penutupan workspace belum tersedia.']);
            }

            $account->delete();
        }, 3);
    }
}

<?php

namespace App\Modules\Core\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Identity\Actions\UpdatePlatformUser;
use App\Modules\Core\Identity\Http\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', Rule::exists(Role::class, 'name')->where('guard_name', 'web')],
        ]);
        $search = trim($filters['search'] ?? '');
        $role = $filters['role'] ?? '';

        $users = User::query()->select(['id', 'name', 'email', 'email_verified_at', 'created_at'])
            ->with('roles:id,name,guard_name')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->when($role !== '', fn ($query) => $query->whereHas('roles', fn ($query) => $query
                ->where('name', $role)->where('guard_name', 'web')))
            ->orderByDesc('id')->paginate(15)->withQueryString()
            ->through(fn (User $user) => $this->serialize($user));

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => ['search' => $search, 'role' => $role],
            'roles' => Role::where('guard_name', 'web')->orderBy('name')->get(['name']),
        ]);
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('Admin/Users/Edit', [
            'account' => $this->serialize($user),
            'roles' => Role::where('guard_name', 'web')->orderBy('name')->get(['name']),
            'canUpdateRoles' => Gate::allows('updateRoles', $user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, UpdatePlatformUser $update): RedirectResponse
    {
        $update->handle($request->user(), $user, $request->validated());

        return to_route('admin.users.edit', $user)->with('success', 'Data pengguna dan role berhasil disimpan.');
    }

    private function serialize(User $user): array
    {
        return [
            ...$user->only(['id', 'name', 'email', 'email_verified_at', 'created_at']),
            'roles' => $user->roles->where('guard_name', 'web')->pluck('name')->values(),
        ];
    }
}

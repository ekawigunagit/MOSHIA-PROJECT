<?php

namespace App\Modules\Core\Tenancy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function edit(Request $request, int $tenant): \Inertia\Response
    {
        $workspace = $request->user()->tenants()->where('owner_id', $request->user()->id)->findOrFail($tenant);

        return \Inertia\Inertia::render('Workspaces/Edit', ['workspace' => $workspace->only(['id', 'name'])]);
    }

    public function update(Request $request, int $tenant): RedirectResponse
    {
        $workspace = $request->user()->tenants()->where('owner_id', $request->user()->id)->findOrFail($tenant);
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $workspace->update(['name' => $data['name']]);

        return to_route('workspaces.edit', $workspace->id)->with('success', 'Nama workspace berhasil diperbarui.');
    }

    public function store(Request $request, CreateWorkspace $create): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $tenant = $create->handle($request->user(), $data['name']);
        $request->session()->put('tenant_id', $tenant->id);

        return to_route('dashboard')->with('success', 'Workspace berhasil dibuat.');
    }

    public function select(Request $request, int $tenant): RedirectResponse
    {
        $workspace = $request->user()->tenants()->findOrFail($tenant);
        $request->session()->put('tenant_id', $workspace->id);

        return to_route('dashboard')->with('success', 'Workspace aktif telah diganti.');
    }
}

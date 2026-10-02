<?php

namespace App\Modules\Core\Tenancy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
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

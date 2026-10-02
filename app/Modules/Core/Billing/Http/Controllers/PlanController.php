<?php

namespace App\Modules\Core\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Billing\Http\Requests\SavePlanRequest;
use App\Modules\Core\Billing\Models\Plan;
use App\Modules\Core\Catalog\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Plan::class);

        return Inertia::render('Admin/Plans/Index', [
            'plans' => Plan::with('product:id,slug,title')->orderByDesc('id')->paginate(15),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Plan::class);

        return $this->form();
    }

    public function edit(Plan $plan): Response
    {
        Gate::authorize('update', $plan);

        return $this->form($plan);
    }

    public function store(SavePlanRequest $request): RedirectResponse
    {
        $plan = Plan::create($request->safe()->only(['product_id', 'name', 'description']));

        return to_route('admin.plans.edit', $plan)->with('success', 'Draft paket berhasil dibuat.');
    }

    public function update(SavePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->safe()->only(['product_id', 'name', 'description']));

        return to_route('admin.plans.edit', $plan)->with('success', 'Draft paket berhasil diperbarui.');
    }

    private function form(?Plan $plan = null): Response
    {
        return Inertia::render('Admin/Plans/Form', [
            'plan' => $plan,
            'products' => Product::orderBy('sort_order')->orderBy('slug')->get(['id', 'slug', 'title', 'status']),
        ]);
    }
}

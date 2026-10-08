<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'workspaceNavigation' => fn () => $request->user()
                ? $request->user()->tenants()->get(['core_tenants.id', 'core_tenants.name'])->map(fn ($tenant) => $tenant->only(['id', 'name']))
                : [],
            'selectedWorkspaceId' => fn () => $request->session()->get('tenant_id'),
            'developmentPayments' => \App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments::enabled(),
            'auth' => [
                'homeUrl' => $request->user()
                    ? app(\App\Modules\Core\Catalog\DashboardDestination::class)->home($request->user())
                    : null,
                'user' => $request->user(),

                'roles' => $request->user()
                    ? $request->user()->getRoleNames()
                    : [],
            ],

            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
        ];
    }
}

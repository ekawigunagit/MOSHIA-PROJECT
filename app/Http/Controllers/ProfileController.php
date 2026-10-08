<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Modules\Core\Identity\Actions\DeleteAccount;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'subscriptions' => $request->query('section') === 'billing'
                ? \App\Modules\Core\Billing\Models\PurchaseOrder::query()
                    ->whereHas('tenant', fn ($query) => $query->where('owner_id', $request->user()->id))
                    ->whereNotNull('paid_at')->whereNull('cancelled_at')
                    ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                    ->whereExists(function ($query) {
                        $query->selectRaw('1')->from('core_entitlements')
                            ->whereColumn('core_entitlements.tenant_id', 'core_purchase_orders.tenant_id')
                            ->whereColumn('core_entitlements.product_id', 'core_purchase_orders.product_id')
                            ->where('status', 'active')
                            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
                    })
                    ->with('tenant:id,name')->latest('id')->paginate(10, ['*'], 'subscriptions_page')->withQueryString()
                    ->through(fn ($order) => [
                        'id' => $order->id, 'tenant_id' => $order->tenant_id,
                        'workspace' => $order->tenant->name,
                        'package' => $order->terms['label'] ?? 'Wedding',
                        'months' => $order->terms['validity_months'],
                        'starts_at' => $order->first_published_at, 'ends_at' => $order->ends_at,
                        'status' => $order->statusAt(\Carbon\CarbonImmutable::now())->value,
                    ]) : null,
            'billingHistory' => $request->query('section') === 'billing'
                ? \App\Modules\Core\Billing\Models\PurchaseOrder::query()
                    ->whereHas('tenant', fn ($query) => $query->where('owner_id', $request->user()->id))
                    ->with('tenant:id,name')
                    ->latest('id')->paginate(10)->withQueryString()
                    ->through(fn ($order) => [
                        'id' => $order->id,
                        'tenant_id' => $order->tenant_id,
                        'workspace' => $order->tenant->name,
                        'package' => $order->terms['label'] ?? $order->terms['key'] ?? 'Wedding',
                        'amount' => $order->terms['price_amount'],
                        'paid_at' => $order->paid_at,
                        'status' => $order->statusAt(\Carbon\CarbonImmutable::now())->value,
                    ])
                : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, DeleteAccount $deleteAccount): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $deleteAccount->handle($user);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

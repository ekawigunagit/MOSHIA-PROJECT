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
            'billingHistory' => $request->query('section') === 'billing'
                ? \App\Modules\Core\Billing\Models\PurchaseOrder::query()
                    ->whereHas('tenant', fn ($query) => $query->where('owner_id', $request->user()->id))
                    ->with('tenant:id,name')
                    ->latest('id')->paginate(10)->withQueryString()
                    ->through(fn ($order) => [
                        'id' => $order->id,
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

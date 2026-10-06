<?php

namespace App\Modules\Wedding\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Models\Tenant;
use App\Modules\Wedding\Models\Invitation;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function edit(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $order = $billing->editorOrder($request->user(), $tenant);
        $invitation = Invitation::where('tenant_id', $tenant->id)->firstOrFail();

        return Inertia::render('Wedding/Edit', [
            'workspace' => $tenant->only('id', 'name'),
            'invitation' => $invitation->only('draft_content', 'published_at'),
            'order' => $order->only('id', 'terms', 'first_published_at', 'ends_at'),
            'publicUrl' => route('wedding.public', $invitation->slug),
        ]);
    }

    public function update(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->authorizeOwner($request->user(), $tenant);
        $content = $request->validate([
            'partner_one' => ['required', 'string', 'max:100'],
            'partner_two' => ['required', 'string', 'max:100'],
            'event_date' => ['required', 'date_format:Y-m-d'],
            'venue' => ['required', 'string', 'max:300'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);
        $billing->saveContent($request->user(), $tenant, $content);

        return back()->with('success', 'Draft tersimpan. Perubahan tampil publik setelah Publish.');
    }

    public function publish(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->publish($request->user(), $tenant);

        return back()->with('success', 'Undangan berhasil dipublikasikan.');
    }

    public function preview(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->editorOrder($request->user(), $tenant);
        $invitation = Invitation::where('tenant_id', $tenant->id)->firstOrFail();
        abort_unless($invitation->draft_content, 404);

        return $this->render($invitation->draft_content, true);
    }

    public function show(string $slug)
    {
        $invitation = Invitation::with('publishedOrder')->where('slug', $slug)->firstOrFail();
        $order = $invitation->publishedOrder;
        abort_unless($invitation->published_content && $order, 404);
        abort_unless($order->tenant_id === $invitation->tenant_id
            && $order->lifecycle()->isWithinPublicLifetimeAt(CarbonImmutable::now()), 404);
        // A newly paid reactivation must not reopen the previous publication.
        abort_unless(Entitlement::where('tenant_id', $invitation->tenant_id)->where('product_id', $order->product_id)
            ->where('status', 'active')->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->exists(), 404);

        return $this->render($invitation->published_content, false);
    }

    private function render(array $content, bool $preview)
    {
        return response()->view('wedding.invitation', compact('content', 'preview'))
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}

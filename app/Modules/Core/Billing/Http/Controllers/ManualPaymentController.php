<?php

namespace App\Modules\Core\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Billing\Models\PurchaseOrder;
use App\Modules\Core\Tenancy\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ManualPaymentController extends Controller
{
    public function index(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->authorizeOwner($request->user(), $tenant);

        return Inertia::render('Billing/Index', [
            'serverNow' => CarbonImmutable::now()->toIso8601String(),
            'workspace' => $tenant->only('id', 'name'),
            'packages' => array_values(config('wedding_plans.packages')),
            'bank' => config('billing.manual_bank'),
            'orders' => PurchaseOrder::where('tenant_id', $tenant->id)->latest('id')->paginate(10)
                ->through(fn ($order) => $this->present($order)),
        ]);
    }

    public function store(Request $request, Tenant $tenant, ManualWeddingBilling $billing)
    {
        $billing->authorizeOwner($request->user(), $tenant);
        $validated = $request->validate([
            'package' => ['required', 'string', Rule::in(array_keys(config('wedding_plans.packages')))],
            'terms' => ['prohibited'], 'price_amount' => ['prohibited'], 'paid_at' => ['prohibited'],
            'accepted_by' => ['prohibited'], 'tenant_id' => ['prohibited'], 'status' => ['prohibited'],
            'payment_expires_at' => ['prohibited'], 'created_at' => ['prohibited'],
        ]);
        $billing->create($request->user(), $tenant, $validated['package']);

        return to_route('billing.index', $tenant)->with('success', 'Pesanan dibuat. Tunggu verifikasi pembayaran oleh admin.');
    }

    public function cancel(Request $request, Tenant $tenant, PurchaseOrder $order, ManualWeddingBilling $billing)
    {
        $billing->cancel($request->user(), $tenant, $order);

        return back()->with('success', 'Pesanan dibatalkan.');
    }

    public function admin()
    {
        return Inertia::render('Admin/Payments/Index', [
            'serverNow' => CarbonImmutable::now()->toIso8601String(),
            'orders' => PurchaseOrder::with(['tenant:id,name,owner_id', 'acceptedBy:id,name'])
                ->latest('id')->paginate(15)->through(fn ($order) => [
                    ...$this->present($order),
                    'workspace' => $order->tenant->only('id', 'name'),
                    'acceptedBy' => $order->acceptedBy?->only('id', 'name'),
                ]),
            'bank' => config('billing.manual_bank'),
        ]);
    }

    public function accept(Request $request, PurchaseOrder $order, ManualWeddingBilling $billing)
    {
        $billing->accept($request->user(), $order);

        return back()->with('success', 'Payment accepted. Masa aktif undangan dimulai saat pengguna menekan Publish.');
    }

    private function present(PurchaseOrder $order): array
    {
        return [
            ...$order->only('id', 'terms', 'created_at', 'paid_at', 'first_published_at', 'ends_at', 'cancelled_at'),
            'status' => $order->statusAt(CarbonImmutable::now())->value,
            'payment_expires_at' => $order->paymentExpiresAt()->toIso8601String(),
        ];
    }
}

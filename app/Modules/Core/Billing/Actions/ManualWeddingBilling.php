<?php

namespace App\Modules\Core\Billing\Actions;

use App\Models\User;
use App\Modules\Core\Billing\Domain\WeddingPurchase;
use App\Modules\Core\Billing\Domain\WeddingPurchaseStatus as Status;
use App\Modules\Core\Billing\Http\Middleware\DevelopmentPayments;
use App\Modules\Core\Billing\Models\PurchaseOrder;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Notification\Actions\RecordNotification;
use App\Modules\Core\Tenancy\Models\Tenant;
use App\Modules\Wedding\Models\Invitation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManualWeddingBilling
{
    public function authorizeOwner(User $user, Tenant $tenant): void
    {
        abort_unless(DevelopmentPayments::enabled(), 404);
        abort_unless($user->hasVerifiedEmail() && $tenant->owner_id === $user->id
            && $tenant->members()->whereKey($user->id)->exists(), 403);
    }

    public function latest(Tenant $tenant): ?PurchaseOrder
    {
        return PurchaseOrder::where('tenant_id', $tenant->id)
            ->where('product_id', Product::where('slug', 'wedding')->value('id'))
            ->whereNull('cancelled_at')->latest('id')->first();
    }

    public function create(User $user, Tenant $tenant, string $package): PurchaseOrder
    {
        return DB::transaction(function () use ($user, $tenant, $package) {
            $tenant = Tenant::lockForUpdate()->findOrFail($tenant->id);
            $this->authorizeOwner($user, $tenant);
            $terms = config('wedding_plans.packages')[$package] ?? null;
            if (! $terms) {
                throw ValidationException::withMessages(['package' => 'Paket tidak tersedia.']);
            }
            WeddingPurchase::pending($terms, CarbonImmutable::now());
            $existing = $this->latest($tenant);
            if ($existing && ! in_array($existing->statusAt(CarbonImmutable::now()), [Status::Expired, Status::PaymentExpired], true)) {
                if (! $existing->paid_at && $existing->terms['key'] === $package) {
                    return $existing;
                }
                throw ValidationException::withMessages(['package' => 'Workspace sudah memiliki pesanan atau paket aktif. Batalkan pesanan belum dibayar untuk mengganti paket.']);
            }

            return PurchaseOrder::create([
                'tenant_id' => $tenant->id,
                'product_id' => Product::where('slug', 'wedding')->firstOrFail()->id,
                'terms' => $terms,
            ]);
        }, 3);
    }

    public function accept(User $admin, PurchaseOrder $order): void
    {
        abort_unless(DevelopmentPayments::enabled(), 404);
        abort_unless($admin->hasVerifiedEmail() && $admin->hasRole('super-admin'), 403);
        DB::transaction(function () use ($admin, $order) {
            $tenant = Tenant::lockForUpdate()->findOrFail($order->tenant_id);
            $order = PurchaseOrder::lockForUpdate()->findOrFail($order->id);
            if ($order->paid_at) {
                return;
            }
            if ($order->cancelled_at || $this->latest($tenant)?->id !== $order->id) {
                throw ValidationException::withMessages(['payment' => 'Pesanan ini sudah dibatalkan atau digantikan.']);
            }
            $at = CarbonImmutable::now();
            if ($order->statusAt($at) === Status::PaymentExpired) {
                throw ValidationException::withMessages(['payment' => 'Batas pembayaran 24 jam sudah berakhir. Pesanan kedaluwarsa; pelanggan perlu membuat pesanan baru.']);
            }
            $purchase = $order->lifecycle()->recordVerifiedPayment('manual:'.$order->id, $at);
            $order->update(['paid_at' => $purchase->paidAt, 'accepted_by' => $admin->id]);
            Invitation::firstOrCreate(['tenant_id' => $tenant->id], ['slug' => (string) Str::uuid()]);
            Entitlement::updateOrCreate(
                ['tenant_id' => $tenant->id, 'product_id' => $order->product_id],
                ['status' => 'active', 'starts_at' => $purchase->paidAt, 'ends_at' => null],
            );
            app(RecordNotification::class)->handle(User::findOrFail($tenant->owner_id),
                'Pembayaran diterima', 'Pesanan #'.$order->id.' diterima. Siapkan undangan; masa aktif dimulai saat Publish.');
        }, 3);
    }

    public function cancel(User $user, Tenant $tenant, PurchaseOrder $order): void
    {
        DB::transaction(function () use ($user, $tenant, $order) {
            $tenant = Tenant::lockForUpdate()->findOrFail($tenant->id);
            $this->authorizeOwner($user, $tenant);
            $order = PurchaseOrder::where('tenant_id', $tenant->id)->lockForUpdate()->findOrFail($order->id);
            if ($order->paid_at) {
                throw ValidationException::withMessages(['payment' => 'Pesanan yang sudah diterima tidak dapat dibatalkan di sini.']);
            }
            if (! $order->cancelled_at) {
                if ($order->statusAt(CarbonImmutable::now()) === Status::PaymentExpired) {
                    throw ValidationException::withMessages(['payment' => 'Pesanan sudah kedaluwarsa. Silakan buat pesanan baru.']);
                }
                $order->update(['cancelled_at' => $order->lifecycle()->cancelUnpaid(CarbonImmutable::now())->cancelledAt]);
            }
        }, 3);
    }

    public function editorOrder(User $user, Tenant $tenant): PurchaseOrder
    {
        $this->authorizeOwner($user, $tenant);
        $order = $this->latest($tenant);
        abort_unless($order && $order->lifecycle()->permitsEditorAt(CarbonImmutable::now()), 403, 'Paket harus sudah dibayar dan belum kedaluwarsa.');
        abort_unless(Entitlement::where('tenant_id', $tenant->id)->where('product_id', $order->product_id)
            ->where('status', 'active')->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->exists(), 403);

        return $order;
    }

    public function saveContent(User $user, Tenant $tenant, array $content): void
    {
        DB::transaction(function () use ($user, $tenant, $content) {
            $tenant = Tenant::lockForUpdate()->findOrFail($tenant->id);
            $this->editorOrder($user, $tenant);
            Invitation::where('tenant_id', $tenant->id)->firstOrFail()->update(['draft_content' => $content]);
        }, 3);
    }

    public function unpublish(User $user, Tenant $tenant): void
    {
        DB::transaction(function () use ($user, $tenant) {
            $tenant = Tenant::lockForUpdate()->findOrFail($tenant->id);
            $this->authorizeOwner($user, $tenant);
            $invitation = Invitation::where('tenant_id', $tenant->id)->lockForUpdate()->firstOrFail();
            // Keep both snapshots and the original paid period; only close public access.
            if ($invitation->published_at !== null) {
                $invitation->update(['published_at' => null]);
            }
        }, 3);
    }

    public function publish(User $user, Tenant $tenant): void
    {
        DB::transaction(function () use ($user, $tenant) {
            $tenant = Tenant::lockForUpdate()->findOrFail($tenant->id);
            $order = $this->editorOrder($user, $tenant);
            $invitation = Invitation::where('tenant_id', $tenant->id)->lockForUpdate()->firstOrFail();
            if (! $invitation->draft_content) {
                throw ValidationException::withMessages(['publish' => 'Simpan konten undangan sebelum Publish.']);
            }
            $at = CarbonImmutable::now();
            $purchase = $order->lifecycle()->recordSuccessfulPublish($at);
            $order->update(['first_published_at' => $purchase->firstPublishedAt, 'ends_at' => $purchase->endsAt]);
            $invitation->update([
                'published_content' => $invitation->draft_content,
                'published_order_id' => $order->id,
                'first_published_at' => $invitation->first_published_at ?? $purchase->firstPublishedAt,
                'published_at' => $at,
            ]);
            Entitlement::where('tenant_id', $tenant->id)->where('product_id', $order->product_id)
                ->update(['starts_at' => $purchase->paidAt, 'ends_at' => $purchase->endsAt]);
        }, 3);
    }
}

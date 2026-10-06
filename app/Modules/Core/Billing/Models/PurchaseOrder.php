<?php

namespace App\Modules\Core\Billing\Models;

use App\Models\User;
use App\Modules\Core\Billing\Domain\WeddingPurchase;
use App\Modules\Core\Billing\Domain\WeddingPurchaseStatus;
use App\Modules\Core\Tenancy\Models\Tenant;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrder extends Model
{
    protected $table = 'core_purchase_orders';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'terms' => 'array', 'created_at' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime', 'first_published_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime', 'cancelled_at' => 'immutable_datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function lifecycle(): WeddingPurchase
    {
        $purchase = WeddingPurchase::pending($this->terms, $this->created_at);
        if ($this->paid_at) {
            $purchase = $purchase->recordVerifiedPayment('manual:'.$this->id, $this->paid_at);
        }
        if ($this->first_published_at) {
            $purchase = $purchase->recordSuccessfulPublish($this->first_published_at);
        }
        if ($this->cancelled_at) {
            $purchase = $purchase->cancelUnpaid($this->cancelled_at);
        }

        return $purchase;
    }

    public function paymentExpiresAt(): CarbonImmutable
    {
        return $this->created_at->utc()->addHours(24);
    }

    public function statusAt(CarbonImmutable $at): WeddingPurchaseStatus
    {
        if (! $this->paid_at && ! $this->cancelled_at && $at->greaterThanOrEqualTo($this->paymentExpiresAt())) {
            return WeddingPurchaseStatus::PaymentExpired;
        }

        return $this->lifecycle()->statusAt($at);
    }
}

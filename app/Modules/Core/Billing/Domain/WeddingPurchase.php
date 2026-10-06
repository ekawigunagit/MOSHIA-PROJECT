<?php

namespace App\Modules\Core\Billing\Domain;

use Carbon\CarbonImmutable;
use DomainException;
use InvalidArgumentException;

/**
 * Internal lifecycle model, not a payment verifier or access grant.
 * ManualWeddingBilling handles persistence, authorization and manual acceptance.
 */
final readonly class WeddingPurchase
{
    private function __construct(
        public array $terms,
        public CarbonImmutable $createdAt,
        public ?string $paymentReference = null,
        public ?CarbonImmutable $paidAt = null,
        public ?CarbonImmutable $firstPublishedAt = null,
        public ?CarbonImmutable $endsAt = null,
        public ?CarbonImmutable $cancelledAt = null,
    ) {}

    /** Terms must be a server-side snapshot belonging to a Wedding plan. */
    public static function pending(array $terms, CarbonImmutable $at): self
    {
        if (($terms['billing_type'] ?? null) !== 'one_time'
            || ($terms['starts_on'] ?? null) !== 'first_publish'
            || ($terms['invitations_per_workspace'] ?? null) !== 1
            || ! is_int($terms['validity_months'] ?? null)
            || $terms['validity_months'] < 1
            || ($terms['currency'] ?? null) !== 'IDR'
            || ($terms['amount_unit'] ?? null) !== 'whole_rupiah'
            || ! is_int($terms['price_amount'] ?? null)
            || $terms['price_amount'] < 1) {
            throw new InvalidArgumentException('A complete Wedding purchase snapshot is required.');
        }

        return new self($terms, $at->utc());
    }

    public function statusAt(CarbonImmutable $at): WeddingPurchaseStatus
    {
        $this->assertChronological($at);

        return match (true) {
            $this->cancelledAt !== null => WeddingPurchaseStatus::Cancelled,
            $this->paidAt === null => WeddingPurchaseStatus::PendingPayment,
            $this->firstPublishedAt === null => WeddingPurchaseStatus::AwaitingPublish,
            $at->greaterThanOrEqualTo($this->endsAt) => WeddingPurchaseStatus::Expired,
            default => WeddingPurchaseStatus::Active,
        };
    }

    /** Call only after the trusted payment adapter has verified and matched its order. */
    public function recordVerifiedPayment(string $reference, CarbonImmutable $at): self
    {
        $this->assertChronological($at);
        if (trim($reference) === '') {
            throw new InvalidArgumentException('Payment reference is required.');
        }
        if ($this->paymentReference !== null) {
            if ($this->paymentReference === $reference) {
                return $this; // A duplicate event must never reset dates, even after expiry.
            }
            throw new DomainException('A second payment requires a separate purchase.');
        }
        if ($this->cancelledAt !== null) {
            throw new DomainException('A cancelled purchase cannot be activated.');
        }

        return new self($this->terms, $this->createdAt, $reference, $at->utc());
    }

    /** Record successful publication only; a preview or failed publish must not call this. */
    public function recordSuccessfulPublish(CarbonImmutable $at): self
    {
        if (! in_array($this->statusAt($at), [WeddingPurchaseStatus::AwaitingPublish, WeddingPurchaseStatus::Active], true)) {
            throw new DomainException('Publication requires a paid, unexpired purchase.');
        }
        if ($this->firstPublishedAt !== null) {
            return $this;
        }

        $publishedAt = $at->utc();

        return new self(
            $this->terms, $this->createdAt, $this->paymentReference, $this->paidAt,
            $publishedAt, $publishedAt->addMonthsNoOverflow($this->terms['validity_months']),
        );
    }

    /** Only unpaid cancellation is defined; refund/revocation remains a separate decision. */
    public function cancelUnpaid(CarbonImmutable $at): self
    {
        $status = $this->statusAt($at);
        if ($status === WeddingPurchaseStatus::Cancelled) {
            return $this;
        }
        if ($status !== WeddingPurchaseStatus::PendingPayment) {
            throw new DomainException('Paid cancellation requires an agreed refund policy.');
        }

        return new self($this->terms, $this->createdAt, cancelledAt: $at->utc());
    }

    /** Billing prerequisite only; callers still need identity, workspace and product policies. */
    public function permitsEditorAt(CarbonImmutable $at): bool
    {
        return in_array($this->statusAt($at), [WeddingPurchaseStatus::AwaitingPublish, WeddingPurchaseStatus::Active], true);
    }

    /** Not a public-route authorization: the invitation must also actually be published. */
    public function isWithinPublicLifetimeAt(CarbonImmutable $at): bool
    {
        return $this->statusAt($at) === WeddingPurchaseStatus::Active;
    }

    private function assertChronological(CarbonImmutable $at): void
    {
        $latest = $this->cancelledAt ?? $this->firstPublishedAt ?? $this->paidAt ?? $this->createdAt;
        if ($at->lessThan($latest)) {
            throw new DomainException('Use the current processing time; historical state queries are unsupported.');
        }
    }
}

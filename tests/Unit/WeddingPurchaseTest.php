<?php

namespace Tests\Unit;

use App\Modules\Core\Billing\Domain\WeddingPurchase;
use App\Modules\Core\Billing\Domain\WeddingPurchaseStatus as Status;
use Carbon\CarbonImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WeddingPurchaseTest extends TestCase
{
    private function purchase(string $package = 'gold'): WeddingPurchase
    {
        $config = require __DIR__.'/../../config/wedding_plans.php';

        return WeddingPurchase::pending($config['packages'][$package], CarbonImmutable::parse('2026-01-01', 'UTC'));
    }

    public function test_payment_opens_preparation_without_starting_invitation_lifetime(): void
    {
        $at = CarbonImmutable::parse('2026-01-02', 'UTC');
        $pending = $this->purchase();
        $this->assertSame(Status::PendingPayment, $pending->statusAt($at));
        $this->assertFalse($pending->permitsEditorAt($at));
        $this->assertFalse($pending->isWithinPublicLifetimeAt($at));
        $paid = $pending->recordVerifiedPayment('payment-1', $at);
        $this->assertSame(Status::AwaitingPublish, $paid->statusAt($at->addYear()));
        $this->assertTrue($paid->permitsEditorAt($at));
        $this->assertFalse($paid->isWithinPublicLifetimeAt($at));
        $this->assertNull($paid->firstPublishedAt);
        $this->assertNull($paid->endsAt);
        $this->assertNull($pending->paidAt);
    }

    public static function packages(): array
    {
        return [['gold', '2027-02-28'], ['emerald', '2027-08-31'], ['diamond', '2027-08-31']];
    }

    #[DataProvider('packages')]
    public function test_first_publish_starts_calendar_months_and_republish_never_extends(string $package, string $end): void
    {
        $at = CarbonImmutable::parse('2026-08-31', 'UTC');
        $paid = $this->purchase($package)->recordVerifiedPayment('payment-1', $at->subMonths(2));
        $active = $paid->recordSuccessfulPublish($at);
        $this->assertSame($end, $active->endsAt->toDateString());
        $this->assertTrue($active->isWithinPublicLifetimeAt($at));
        $this->assertSame($active, $active->recordSuccessfulPublish($at->addDay()));
        $this->assertSame($active, $active->recordVerifiedPayment('payment-1', $at->addDay()));
        $this->assertNull($paid->endsAt);
    }

    public function test_expiry_boundary_denies_access_without_deleting_snapshot(): void
    {
        $at = CarbonImmutable::parse('2026-01-02', 'UTC');
        $active = $this->purchase()->recordVerifiedPayment('payment-1', $at)->recordSuccessfulPublish($at);
        $this->assertSame(Status::Active, $active->statusAt($active->endsAt->subSecond()));
        $this->assertSame(Status::Expired, $active->statusAt($active->endsAt));
        $this->assertFalse($active->permitsEditorAt($active->endsAt));
        $this->assertFalse($active->isWithinPublicLifetimeAt($active->endsAt));
        $this->assertSame(149000, $active->terms['price_amount']);
        $this->assertSame($active, $active->recordVerifiedPayment('payment-1', $active->endsAt));
        $this->expectException(DomainException::class);
        $active->recordSuccessfulPublish($active->endsAt);
    }

    public function test_unpaid_purchase_cannot_publish(): void
    {
        $this->expectException(DomainException::class);
        $this->purchase()->recordSuccessfulPublish(CarbonImmutable::parse('2026-01-02', 'UTC'));
    }

    public function test_unpaid_cancellation_is_idempotent_and_blocks_payment(): void
    {
        $at = CarbonImmutable::parse('2026-01-02', 'UTC');
        $cancelled = $this->purchase()->cancelUnpaid($at);
        $this->assertSame(Status::Cancelled, $cancelled->statusAt($at));
        $this->assertSame($cancelled, $cancelled->cancelUnpaid($at));
        $this->assertFalse($cancelled->permitsEditorAt($at));
        $this->expectException(DomainException::class);
        $cancelled->recordVerifiedPayment('payment-1', $at);
    }

    public function test_paid_cancellation_requires_refund_decision(): void
    {
        $at = CarbonImmutable::parse('2026-01-02', 'UTC');
        $paid = $this->purchase()->recordVerifiedPayment('payment-1', $at);
        $this->expectException(DomainException::class);
        $paid->cancelUnpaid($at);
    }

    public function test_a_second_payment_requires_a_new_purchase_instead_of_overwriting_history(): void
    {
        $at = CarbonImmutable::parse('2026-01-02', 'UTC');
        $active = $this->purchase()->recordVerifiedPayment('payment-1', $at)->recordSuccessfulPublish($at);
        $this->expectException(DomainException::class);
        $active->recordVerifiedPayment('payment-2', $active->endsAt);
    }

    public function test_backdated_publish_is_rejected(): void
    {
        $at = CarbonImmutable::parse('2026-01-02', 'UTC');
        $paid = $this->purchase()->recordVerifiedPayment('payment-1', $at);
        $this->expectException(DomainException::class);
        $paid->recordSuccessfulPublish($at->subSecond());
    }

    public function test_blank_payment_reference_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->purchase()->recordVerifiedPayment(' ', CarbonImmutable::parse('2026-01-02', 'UTC'));
    }

    public function test_snapshot_is_independent_of_later_plan_changes(): void
    {
        $terms = $this->purchase()->terms;
        $purchase = WeddingPurchase::pending($terms, CarbonImmutable::parse('2026-01-01', 'UTC'));
        $terms['price_amount'] = 1;
        $terms['validity_months'] = 100;
        $this->assertSame(149000, $purchase->terms['price_amount']);
        $this->assertSame(6, $purchase->terms['validity_months']);
    }

    public static function invalidTerms(): array
    {
        return [
            [['validity_months' => 0]], [['validity_months' => '6']],
            [['price_amount' => 1.5]], [['price_amount' => 0]],
            [['currency' => 'USD']], [['amount_unit' => 'cents']],
            [['billing_type' => 'recurring']], [['starts_on' => 'payment']],
            [['invitations_per_workspace' => 2]],
        ];
    }

    #[DataProvider('invalidTerms')]
    public function test_invalid_snapshot_is_rejected(array $overrides): void
    {
        $this->expectException(InvalidArgumentException::class);
        WeddingPurchase::pending(array_replace($this->purchase()->terms, $overrides), CarbonImmutable::now());
    }

    public function test_timezone_and_leap_day_use_same_utc_instant(): void
    {
        $at = CarbonImmutable::parse('2028-02-29 07:00:00', 'Asia/Bangkok');
        $purchase = $this->purchase('diamond')->recordVerifiedPayment('payment-1', $at)->recordSuccessfulPublish($at);
        $this->assertSame('2028-02-29T00:00:00+00:00', $purchase->firstPublishedAt->toIso8601String());
        $this->assertSame('2029-02-28T00:00:00+00:00', $purchase->endsAt->toIso8601String());
    }
}

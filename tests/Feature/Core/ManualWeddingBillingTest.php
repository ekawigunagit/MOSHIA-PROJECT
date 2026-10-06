<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Billing\Models\PurchaseOrder;
use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Notification\Actions\RecordNotification;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use App\Modules\Core\Tenancy\Models\Tenant;
use App\Modules\Wedding\Models\Invitation;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class ManualWeddingBillingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $admin;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $this->owner = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');
        $this->tenant = app(CreateWorkspace::class)->handle($this->owner, 'Wedding workspace');
        $this->travelTo(now()->startOfSecond());
    }

    private function order(string $package = 'gold'): PurchaseOrder
    {
        $this->actingAs($this->owner)->post(route('billing.store', $this->tenant), ['package' => $package])
            ->assertSessionHasNoErrors()->assertRedirect();

        return PurchaseOrder::latest('id')->firstOrFail();
    }

    private function accept(PurchaseOrder $order): void
    {
        $this->actingAs($this->admin)->post(route('admin.payments.accept', $order))
            ->assertSessionHasNoErrors()->assertRedirect();
    }

    private function content(array $overrides = []): array
    {
        return array_replace([
            'partner_one' => 'Eka', 'partner_two' => 'Ayu', 'event_date' => '2027-01-20',
            'venue' => 'Jakarta', 'message' => 'Dengan bahagia kami mengundang Anda.',
        ], $overrides);
    }

    private function saveAndPublish(): Invitation
    {
        $this->actingAs($this->owner)->patch(route('wedding.update', $this->tenant), $this->content())
            ->assertSessionHasNoErrors();
        $this->post(route('wedding.publish', $this->tenant))->assertSessionHasNoErrors();

        return Invitation::firstOrFail();
    }

    public function test_owner_sees_dummy_account_and_server_side_packages(): void
    {
        $this->actingAs($this->owner)->get(route('billing.index', $this->tenant))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Billing/Index')
            ->where('bank.bank', 'BCA')->where('bank.account_number', '12345678')
            ->where('bank.account_name', 'MOSHIA CORPORATE')->has('packages', 3));
        $order = $this->order();
        $this->assertSame(149000, $order->terms['price_amount']);
        config(['wedding_plans.packages.gold.price_amount' => 500000]);
        $this->assertSame(149000, $order->fresh()->terms['price_amount']);
        $this->assertDatabaseCount('core_entitlements', 0);
        $this->assertDatabaseCount('wedding_invitations', 0);
        $this->get(route('wedding.edit', $this->tenant))->assertForbidden();
    }

    public function test_acceptance_is_audited_idempotent_and_does_not_start_lifetime(): void
    {
        $order = $this->order();
        $notifications = $this->owner->notifications()->count();
        $this->accept($order);
        $paidAt = $order->fresh()->paid_at;
        $this->travel(2)->days();
        $this->accept($order);
        $this->assertTrue($paidAt->equalTo($order->fresh()->paid_at));
        $this->assertSame($this->admin->id, $order->fresh()->accepted_by);
        $this->assertNull($order->fresh()->first_published_at);
        $this->assertNull($order->fresh()->ends_at);
        $this->assertNull(Entitlement::firstOrFail()->ends_at);
        $this->assertDatabaseCount('wedding_invitations', 1);
        $this->assertDatabaseCount('core_entitlements', 1);
        $this->assertSame($notifications + 1, $this->owner->notifications()->count());
        $this->actingAs($this->owner)->get(route('wedding.edit', $this->tenant))->assertOk();
        $this->get(route('wedding.public', Invitation::firstOrFail()->slug))->assertNotFound();
        $this->actingAs($this->admin)->get(route('admin.payments.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('orders.data.0.acceptedBy.id', $this->admin->id));
    }

    public function test_publish_starts_selected_duration_and_republish_keeps_original_deadline(): void
    {
        $order = $this->order('emerald');
        $this->accept($order);
        $this->travel(10)->days();
        $invitation = $this->saveAndPublish();
        $order->refresh();
        $this->assertTrue(now()->equalTo($order->first_published_at));
        $end = $order->first_published_at->addMonthsNoOverflow(12);
        $this->assertTrue($end->equalTo($order->ends_at));
        $this->assertTrue($end->equalTo(Entitlement::first()->ends_at));
        $this->travel(3)->days();
        $this->post(route('wedding.publish', $this->tenant))->assertSessionHasNoErrors();
        $this->assertTrue($end->equalTo($order->fresh()->ends_at));
        $this->assertTrue($invitation->first_published_at->equalTo($invitation->fresh()->first_published_at));
        $this->app['auth']->forgetGuards();
        $this->get(route('wedding.public', $invitation->slug))->assertOk()->assertSee('Eka')->assertSee('Ayu');
    }

    public function test_preview_and_unsaved_publication_do_not_start_clock_and_drafts_stay_private(): void
    {
        $order = $this->order();
        $this->accept($order);
        $this->actingAs($this->owner)->post(route('wedding.publish', $this->tenant))->assertSessionHasErrors('publish');
        $this->assertNull($order->fresh()->first_published_at);
        $this->patch(route('wedding.update', $this->tenant), $this->content())->assertSessionHasNoErrors();
        $this->get(route('wedding.preview', $this->tenant))->assertOk()->assertSee('Eka');
        $this->assertNull($order->fresh()->ends_at);
        $this->post(route('wedding.publish', $this->tenant))->assertSessionHasNoErrors();
        $invitation = Invitation::firstOrFail();
        $this->patch(route('wedding.update', $this->tenant), $this->content(['partner_one' => 'Unpublished-name']))
            ->assertSessionHasNoErrors();
        $this->get(route('wedding.preview', $this->tenant))->assertSee('Unpublished-name');
        $this->get(route('wedding.public', $invitation->slug))->assertSee('Eka')->assertDontSee('Unpublished-name');
    }

    public function test_expiry_and_repurchase_keep_content_and_require_new_publish(): void
    {
        $order = $this->order();
        $this->accept($order);
        $invitation = $this->saveAndPublish();
        $originalFirstPublish = $invitation->first_published_at;
        $end = $order->fresh()->ends_at;
        $this->travelTo($end->subSecond());
        $this->get(route('wedding.public', $invitation->slug))->assertOk();
        $this->travelTo($end);
        $this->get(route('wedding.public', $invitation->slug))->assertNotFound();
        $this->get(route('wedding.edit', $this->tenant))->assertForbidden();
        $this->assertFalse(app(ProductAccess::class)->allows($this->owner, $this->tenant, 'wedding'));
        $renewal = $this->order('diamond');
        $this->accept($renewal);
        $this->get(route('wedding.public', $invitation->slug))->assertNotFound();
        $this->assertNull($renewal->fresh()->ends_at);
        $this->travel(5)->days();
        $this->actingAs($this->owner)->get(route('wedding.edit', $this->tenant))->assertOk();
        $this->post(route('wedding.publish', $this->tenant))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('wedding_invitations', 1);
        $this->assertDatabaseCount('core_purchase_orders', 2);
        $this->assertSame($renewal->id, $invitation->fresh()->published_order_id);
        $this->assertSame($invitation->slug, $invitation->fresh()->slug);
        $this->assertTrue($originalFirstPublish->equalTo($invitation->fresh()->first_published_at));
        $this->assertTrue(now()->addMonthsNoOverflow(12)->equalTo($renewal->fresh()->ends_at));
        $this->assertTrue($end->equalTo($order->fresh()->ends_at));
        $this->get(route('wedding.public', $invitation->slug))->assertOk()->assertSee('Eka');
        $this->accept($order); // Old callback cannot overwrite the new entitlement.
        $this->assertTrue($renewal->fresh()->ends_at->equalTo(Entitlement::first()->ends_at));
    }

    public function test_customer_and_unverified_admin_cannot_accept_payments(): void
    {
        $order = $this->order();
        $this->get(route('admin.payments.index'))->assertForbidden();
        $this->post(route('admin.payments.accept', $order))->assertForbidden();
        $this->admin->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($this->admin)->post(route('admin.payments.accept', $order))->assertRedirect(route('verification.notice'));
        $this->assertNull($order->fresh()->paid_at);
        $this->assertDatabaseCount('core_entitlements', 0);
    }

    public function test_other_workspace_member_and_superadmin_cannot_edit_owner_invitation(): void
    {
        $order = $this->order();
        $this->accept($order);
        $other = User::factory()->create();
        $this->tenant->members()->attach($other);
        foreach ([$other, $this->admin] as $user) {
            $this->actingAs($user)->get(route('billing.index', $this->tenant))->assertForbidden();
            $this->post(route('billing.store', $this->tenant), ['package' => 'gold'])->assertForbidden();
            $this->get(route('wedding.edit', $this->tenant))->assertForbidden();
            $this->get(route('wedding.preview', $this->tenant))->assertForbidden();
            $this->patch(route('wedding.update', $this->tenant), $this->content())->assertForbidden();
            $this->post(route('wedding.publish', $this->tenant))->assertForbidden();
        }
        $this->assertNull(Invitation::firstOrFail()->draft_content);
    }

    public function test_forged_price_status_and_unknown_package_are_rejected(): void
    {
        $this->actingAs($this->owner);
        foreach (['price_amount' => 1, 'paid_at' => now()->toISOString(), 'terms' => ['price_amount' => 1], 'status' => 'active'] as $key => $value) {
            $this->post(route('billing.store', $this->tenant), ['package' => 'gold', $key => $value])->assertSessionHasErrors($key);
        }
        $this->post(route('billing.store', $this->tenant), ['package' => 'nonexistent'])->assertSessionHasErrors('package');
        $this->assertDatabaseCount('core_purchase_orders', 0);
    }

    public function test_pending_duplicate_and_active_purchase_cannot_create_second_order(): void
    {
        $order = $this->order();
        $this->order();
        $this->assertDatabaseCount('core_purchase_orders', 1);
        $this->post(route('billing.store', $this->tenant), ['package' => 'diamond'])->assertSessionHasErrors('package');
        $this->accept($order);
        $this->actingAs($this->owner)->post(route('billing.store', $this->tenant), ['package' => 'gold'])->assertSessionHasErrors('package');
        $this->assertDatabaseCount('core_purchase_orders', 1);
    }

    public function test_cancelled_order_cannot_be_accepted_and_paid_order_cannot_be_cancelled(): void
    {
        $order = $this->order();
        $this->post(route('billing.cancel', [$this->tenant, $order]))->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('admin.payments.accept', $order))->assertSessionHasErrors('payment');
        $this->assertDatabaseCount('core_entitlements', 0);
        $replacement = $this->order('emerald');
        $this->accept($replacement);
        $this->actingAs($this->owner)->post(route('billing.cancel', [$this->tenant, $replacement]))->assertSessionHasErrors('payment');
        $this->assertNull($replacement->fresh()->cancelled_at);
    }

    public function test_order_cannot_be_cancelled_through_another_owned_workspace(): void
    {
        $order = $this->order();
        $second = app(CreateWorkspace::class)->handle($this->owner, 'Second');
        $this->post(route('billing.cancel', [$second, $order]))->assertNotFound();
        $this->assertNull($order->fresh()->cancelled_at);
    }

    public function test_acceptance_rolls_back_when_notification_fails(): void
    {
        $order = $this->order();
        $this->mock(RecordNotification::class)->shouldReceive('handle')->once()->andThrow(new RuntimeException('simulated failure'));
        try {
            app(ManualWeddingBilling::class)->accept($this->admin, $order);
            $this->fail('Expected rollback.');
        } catch (RuntimeException $exception) {
            $this->assertSame('simulated failure', $exception->getMessage());
        }
        $this->assertNull($order->fresh()->paid_at);
        $this->assertDatabaseCount('core_entitlements', 0);
        $this->assertDatabaseCount('wedding_invitations', 0);
    }

    public function test_content_is_validated_escaped_and_access_respects_revocation(): void
    {
        $order = $this->order();
        $this->accept($order);
        $this->actingAs($this->owner)->patch(route('wedding.update', $this->tenant), $this->content(['event_date' => 'bad']))
            ->assertSessionHasErrors('event_date');
        $this->patch(route('wedding.update', $this->tenant), $this->content(['message' => '<script>alert(1)</script>']))->assertSessionHasNoErrors();
        $this->post(route('wedding.publish', $this->tenant))->assertSessionHasNoErrors();
        $this->get(route('wedding.public', Invitation::first()->slug))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false)
            ->assertHeader('Cache-Control', 'no-store, private');
        Entitlement::query()->update(['status' => 'revoked']);
        $this->get(route('wedding.edit', $this->tenant))->assertForbidden();
        $this->get(route('wedding.public', Invitation::first()->slug))->assertNotFound();
    }

    public function test_payment_expires_at_exactly_24_hours_and_cannot_be_accepted_or_cancelled(): void
    {
        $order = $this->order();
        $deadline = $order->created_at->addHours(24);
        $this->travelTo($deadline->subSecond());
        $this->get(route('billing.index', $this->tenant))->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.status', 'pending_payment')
            ->where('orders.data.0.payment_expires_at', $deadline->toIso8601String()));
        $this->travelTo($deadline);
        $this->get(route('billing.index', $this->tenant))->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.status', 'payment_expired'));
        $this->post(route('billing.cancel', [$this->tenant, $order]))->assertSessionHasErrors('payment');
        $this->actingAs($this->admin)->get(route('admin.payments.index'))->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.status', 'payment_expired'));
        $this->post(route('admin.payments.accept', $order))->assertSessionHasErrors('payment');
        $this->assertNull($order->fresh()->paid_at);
        $this->assertNull($order->fresh()->cancelled_at);
        $this->assertDatabaseCount('core_entitlements', 0);
        $this->assertDatabaseCount('wedding_invitations', 0);
    }

    public function test_retry_does_not_extend_deadline_but_expired_order_can_be_replaced(): void
    {
        $order = $this->order();
        $deadline = $order->paymentExpiresAt();
        $this->travel(23)->hours();
        $retry = $this->order();
        $this->assertSame($order->id, $retry->id);
        $this->assertTrue($deadline->equalTo($retry->paymentExpiresAt()));
        $this->travelTo($deadline);
        $replacement = $this->order();
        $this->assertNotSame($order->id, $replacement->id);
        $this->assertTrue($deadline->addHours(24)->equalTo($replacement->paymentExpiresAt()));
        $this->assertDatabaseCount('core_purchase_orders', 2);
        $this->actingAs($this->admin)->post(route('admin.payments.accept', $order))->assertSessionHasErrors('payment');
        $this->accept($replacement);
        $this->assertDatabaseCount('core_entitlements', 1);
    }

    public function test_acceptance_just_before_deadline_keeps_editor_available_after_24_hours(): void
    {
        $order = $this->order();
        $this->travelTo($order->paymentExpiresAt()->subSecond());
        $this->accept($order);
        $this->travel(2)->days();
        $this->actingAs($this->owner)->get(route('billing.index', $this->tenant))->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.status', 'awaiting_publish'));
        $this->get(route('wedding.edit', $this->tenant))->assertOk();
        $this->assertNull($order->fresh()->ends_at);
    }

    public function test_payment_deadline_cannot_be_overridden_by_the_customer(): void
    {
        $this->actingAs($this->owner)->post(route('billing.store', $this->tenant), [
            'package' => 'gold', 'payment_expires_at' => now()->addYear()->toIso8601String(),
            'created_at' => now()->addYear()->toIso8601String(),
        ])->assertSessionHasErrors(['payment_expires_at', 'created_at']);
        $this->assertDatabaseCount('core_purchase_orders', 0);
    }

    public function test_development_routes_are_disabled_in_production_even_with_flag_enabled(): void
    {
        $order = $this->order();
        $this->app->instance('env', 'production');
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['billing.manual_development_enabled' => true]);
        $this->actingAs($this->owner)->get(route('billing.index', $this->tenant))->assertNotFound();
        $this->post(route('billing.store', $this->tenant), ['package' => 'gold'])->assertNotFound();
        $this->actingAs($this->admin)->post(route('admin.payments.accept', $order))->assertNotFound();
        $this->assertNull($order->fresh()->paid_at);
    }
}

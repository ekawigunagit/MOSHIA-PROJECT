<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileBillingHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_only_exposes_orders_from_owned_workspaces_even_for_admin(): void
    {
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('super-admin');
        $other = User::factory()->create();
        $mine = app(CreateWorkspace::class)->handle($owner, 'Mine');
        $foreign = app(CreateWorkspace::class)->handle($other, 'Private');
        $ownOrder = app(ManualWeddingBilling::class)->create($owner, $mine, 'gold');
        app(ManualWeddingBilling::class)->create($other, $foreign, 'diamond');

        $this->actingAs($owner)->get('/profile?section=billing')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Profile/Edit')
                ->has('billingHistory.data', 1)
                ->where('billingHistory.data.0.id', $ownOrder->id)
                ->where('billingHistory.data.0.workspace', 'Mine')
                ->where('billingHistory.data.0.amount', 149000)
                ->missing('billingHistory.data.0.accepted_by')
                ->missing('billingHistory.data.0.terms'));
        $this->get('/profile')->assertInertia(fn (Assert $page) => $page->where('billingHistory', null));
    }

    public function test_subscriptions_exclude_unpaid_expired_revoked_and_foreign_packages(): void
    {
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $owner = User::factory()->create();
        $owner->assignRole('super-admin');
        $billing = app(ManualWeddingBilling::class);
        $kept = [];
        foreach (['awaiting', 'active', 'pending', 'expired', 'revoked', 'foreign'] as $state) {
            $buyer = $state === 'foreign' ? User::factory()->create() : $owner;
            $tenant = app(CreateWorkspace::class)->handle($buyer, $state);
            $order = $billing->create($buyer, $tenant, 'gold');
            if ($state !== 'pending') {
                $billing->accept($owner, $order);
            }
            if ($state === 'active') {
                $billing->saveContent($buyer, $tenant, ['partner_one' => 'A', 'partner_two' => 'B', 'event_date' => '2027-01-01', 'venue' => 'Jakarta']);
                $billing->publish($buyer, $tenant);
            }
            if ($state === 'expired') {
                $order->update(['ends_at' => now()]);
            }
            if ($state === 'revoked') {
                \App\Modules\Core\Entitlement\Models\Entitlement::where('tenant_id', $tenant->id)->update(['status' => 'revoked']);
            }
            if (in_array($state, ['awaiting', 'active'])) {
                $kept[] = $order->id;
            }
        }
        $this->actingAs($owner)->get('/profile?section=billing')->assertInertia(fn (Assert $page) => $page
            ->has('subscriptions.data', 2)
            ->where('subscriptions.data.0.id', $kept[1])
            ->where('subscriptions.data.0.status', 'active')
            ->where('subscriptions.data.1.id', $kept[0])
            ->where('subscriptions.data.1.status', 'awaiting_publish')
            ->has('billingHistory.data', 5));
        $this->get('/profile')->assertInertia(fn (Assert $page) => $page->where('subscriptions', null));
    }

    public function test_history_requires_login_and_empty_account_has_no_orders(): void
    {
        $this->withoutVite();
        $this->get('/profile?section=billing')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/profile?section=billing')
            ->assertInertia(fn (Assert $page) => $page->has('billingHistory.data', 0));
    }
}

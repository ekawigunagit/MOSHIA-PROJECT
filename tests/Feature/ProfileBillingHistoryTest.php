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

    public function test_history_requires_login_and_empty_account_has_no_orders(): void
    {
        $this->withoutVite();
        $this->get('/profile?section=billing')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/profile?section=billing')
            ->assertInertia(fn (Assert $page) => $page->has('billingHistory.data', 0));
    }
}

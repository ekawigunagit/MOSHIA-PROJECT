<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkspaceProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settled_product_is_manageable_only_in_its_workspace_without_starting_the_period(): void
    {
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $owner = User::factory()->create();
        $one = app(CreateWorkspace::class)->handle($owner, 'Pertama');
        $two = app(CreateWorkspace::class)->handle($owner, 'Kedua');
        $billing = app(ManualWeddingBilling::class);
        $order = $billing->create($owner, $one, 'gold');
        $this->actingAs($owner)->withSession(['tenant_id' => $one->id])->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page->has('settledProducts', 0));
        $billing->accept($admin, $order);
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->has('settledProducts', 1)->where('settledProducts.0.id', $order->id)
            ->where('settledProducts.0.status', 'awaiting_publish')->where('settledProducts.0.can_manage', true)
            ->where('settledProducts.0.ends_at', null));
        $this->get(route('wedding.edit', $one->id))->assertOk();
        $this->assertNull($order->fresh()->first_published_at);
        $this->post(route('workspaces.select', $two->id));
        $this->get('/dashboard')->assertInertia(fn (Assert $page) => $page->has('settledProducts', 0));
        $this->actingAs($admin)->withSession(['tenant_id' => $one->id])->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page->has('settledProducts', 0));
        \App\Modules\Core\Entitlement\Models\Entitlement::where('tenant_id', $one->id)->update(['status' => 'revoked']);
        $this->actingAs($owner)->withSession(['tenant_id' => $one->id])->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page->has('settledProducts', 1)->where('settledProducts.0.can_manage', false));
    }
}

<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Billing\Models\Plan;
use App\Modules\Core\Catalog\Models\Product;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WeddingDraftTermsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
    }

    private function loginAdmin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $this->actingAs($user);
    }

    private function payload(string $key): array
    {
        return [
            'product_id' => Product::where('slug', 'wedding')->value('id'),
            'name' => 'Paket '.$key,
            'wedding_package' => $key,
        ];
    }

    public function test_presets_store_server_prices_and_terms_without_granting_access(): void
    {
        $this->loginAdmin();
        $this->get('/admin/plans/create')->assertInertia(fn (Assert $page) => $page
            ->has('weddingPackages', 3));
        foreach (['gold' => [149000, 6], 'emerald' => [249000, 12], 'diamond' => [559000, 12]] as $key => [$price, $months]) {
            $this->post('/admin/plans', $this->payload($key))->assertSessionHasNoErrors();
            $plan = Plan::where('name', 'Paket '.$key)->firstOrFail();
            $this->assertSame('draft', $plan->status);
            $this->assertSame($price, $plan->commercial_terms['price_amount']);
            $this->assertSame('IDR', $plan->commercial_terms['currency']);
            $this->assertSame($months, $plan->commercial_terms['validity_months']);
            $this->assertSame(1, $plan->commercial_terms['invitations_per_workspace']);
            $this->assertSame('first_publish', $plan->commercial_terms['starts_on']);
            $this->assertSame($key === 'diamond' ? 'com' : null, $plan->commercial_terms['domain_extension']);
        }
        $this->assertDatabaseCount('core_entitlements', 0);
        $this->get('/')->assertInertia(fn (Assert $page) => $page->missing('plans'));
    }

    public function test_forged_terms_unknown_presets_and_other_products_are_rejected(): void
    {
        $this->loginAdmin();
        $this->post('/admin/plans', $this->payload('unknown'))->assertSessionHasErrors('wedding_package');
        $this->post('/admin/plans', array_replace($this->payload('gold'), [
            'commercial_terms' => ['price_amount' => 1],
        ]))->assertSessionHasErrors('commercial_terms');
        $this->post('/admin/plans', array_replace($this->payload('gold'), [
            'price_amount' => 1,
        ]))->assertSessionHasErrors('price_amount');
        $this->post('/admin/plans', array_replace($this->payload('gold'), [
            'product_id' => Product::where('slug', 'jastip')->value('id'),
        ]))->assertSessionHasErrors('wedding_package');
        $this->assertDatabaseCount('core_plans', 0);
    }

    public function test_edit_preserves_snapshot_and_can_remove_or_change_preset(): void
    {
        $this->loginAdmin();
        $this->post('/admin/plans', $this->payload('gold'))->assertSessionHasNoErrors();
        $plan = Plan::firstOrFail();
        config(['wedding_plans.packages.gold.price_amount' => 199000]);
        $this->patch('/admin/plans/'.$plan->id, $this->payload('gold'))->assertSessionHasNoErrors();
        $this->assertSame(149000, $plan->fresh()->commercial_terms['price_amount']);
        $this->patch('/admin/plans/'.$plan->id, $this->payload('diamond'))->assertSessionHasNoErrors();
        $this->assertSame(559000, $plan->fresh()->commercial_terms['price_amount']);
        $this->patch('/admin/plans/'.$plan->id, array_replace($this->payload('gold'), [
            'wedding_package' => '',
            'product_id' => Product::where('slug', 'jastip')->value('id'),
        ]))->assertSessionHasNoErrors();
        $this->assertNull($plan->fresh()->commercial_terms);
    }

    public function test_customer_cannot_save_commercial_terms(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/plans', $this->payload('diamond'))->assertForbidden();
        $this->assertDatabaseCount('core_plans', 0);
    }
}

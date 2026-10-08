<?php
namespace Tests\Feature\Core;
use App\Models\User;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
class ProductPlansTest extends TestCase {
    use RefreshDatabase;
    public function test_plans_are_verified_scoped_and_other_products_have_no_packages(): void {
        $this->withoutVite();
        $this->get('/products/wedding')->assertRedirect('/login');
        $this->actingAs(User::factory()->unverified()->create())->get('/products/wedding')->assertRedirect('/verify-email');
        $owner = User::factory()->create();
        $mine = app(CreateWorkspace::class)->handle($owner, 'Mine');
        $other = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Other');
        $this->actingAs($owner)->withSession(['tenant_id' => $other->id])->get('/products/wedding')
            ->assertInertia(fn (Assert $page) => $page->component('Products/Plans')->has('packages', 3)
                ->where('packages.0.price_amount', 149000)->where('workspace.id', $mine->id)->where('canPurchase', true));
        foreach (['jastip','photobooth','restaurant'] as $slug) {
            $this->get('/products/'.$slug)->assertInertia(fn (Assert $page) => $page->has('packages', 0));
        }
        config(['billing.manual_development_enabled' => false]);
        $this->get('/products/wedding')->assertInertia(fn (Assert $page) => $page->where('canPurchase', false));
        $this->get('/products/unknown')->assertNotFound();
        Product::where('slug', 'wedding')->update(['status' => 'hidden']);
        $this->get('/products/wedding')->assertNotFound();
    }
    public function test_no_workspace_never_offers_checkout(): void {
        $this->withoutVite();
        $this->actingAs(User::factory()->create())->get('/products/wedding')
            ->assertInertia(fn (Assert $page) => $page->where('workspace', null)->where('canPurchase', false));
    }
}

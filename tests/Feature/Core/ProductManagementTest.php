<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Catalog\ProductCatalog;
use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Wedding Moshia', 'summary' => 'Ringkasan baru', 'description' => 'Deskripsi baru',
            'icon' => 'link', 'sort_order' => 10, 'status' => 'planned',
        ], $overrides);
    }

    public function test_migration_preserves_initial_catalog_ids_order_and_planned_status(): void
    {
        $products = app(ProductCatalog::class)->all();
        $this->assertSame(['wedding', 'jastip', 'photobooth', 'restaurant'], array_column($products, 'slug'));
        $this->assertSame(['planned'], array_values(array_unique(array_column($products, 'status'))));
        $this->assertSame('Wedding Invitation', $products[0]['title']);
        $this->assertDatabaseCount('core_products', 4);
    }

    public function test_only_verified_admins_can_read_and_update_catalog(): void
    {
        $this->get('/admin/products')->assertRedirect('/login');
        $this->get('/admin/products/wedding/edit')->assertRedirect('/login');
        $this->patch('/admin/products/wedding', $this->payload())->assertRedirect('/login');
        $customer = User::factory()->create();
        $customer->assignRole('user');
        $this->actingAs($customer)->get('/admin/products')->assertForbidden();
        $this->get('/admin/products/wedding/edit')->assertForbidden();
        $this->patch('/admin/products/wedding', $this->payload())->assertForbidden();
        $this->assertFalse(Gate::forUser($customer)->allows('update', Product::where('slug', 'wedding')->firstOrFail()));

        $unverified = User::factory()->unverified()->create();
        $unverified->assignRole('super-admin');
        $this->actingAs($unverified)->get('/admin/products')->assertRedirect('/verify-email');
        $this->get('/admin/products/wedding/edit')->assertRedirect('/verify-email');
        $this->patch('/admin/products/wedding', $this->payload())->assertRedirect('/verify-email');
        $this->assertFalse(Gate::forUser($unverified)->allows('update', Product::where('slug', 'wedding')->firstOrFail()));
        $this->assertDatabaseHas('core_products', ['slug' => 'wedding', 'title' => 'Wedding Invitation']);
    }

    public function test_admin_edit_is_shared_by_public_dashboard_and_admin_catalogs(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/products')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Products/Index')->has('products', 4));
        $this->get('/admin/products/wedding/edit')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Products/Edit')->where('product.slug', 'wedding'));
        $this->patch('/admin/products/wedding', $this->payload())->assertRedirect('/admin/products/wedding/edit')
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        foreach (['/', '/dashboard', '/admin', '/admin/products'] as $path) {
            $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->where('products.0.title', 'Wedding Moshia')->where('products.0.summary', 'Ringkasan baru'));
        }
        $this->post('/logout');
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('products.0.title', 'Wedding Moshia'));
        // Runtime no longer reads the old registry.
        config(['moshia.products.wedding.title' => 'Stale config']);
        $this->assertSame('Wedding Moshia', app(ProductCatalog::class)->find('wedding')['title']);
    }

    public function test_hiding_product_removes_public_cards_without_revoking_entitlements(): void
    {
        $admin = $this->admin();
        $tenant = app(CreateWorkspace::class)->handle($admin, 'Mine');
        Entitlement::create(['tenant_id' => $tenant->id, 'product_id' => \App\Modules\Core\Catalog\Models\Product::where('slug', 'wedding')->value('id'), 'status' => 'active']);
        $this->actingAs($admin)->patch('/admin/products/wedding', $this->payload(['status' => 'hidden']))->assertSessionHasNoErrors();
        foreach (['/', '/dashboard'] as $path) {
            $this->get($path)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->has('products', 3)->where('products.0.slug', 'jastip'));
        }
        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('products', 4)->where('stats.products', 4)->where('products.0.status', 'hidden'));
        $this->assertTrue(app(ProductAccess::class)->allows($admin, $tenant, 'wedding'));
        $this->assertDatabaseCount('core_entitlements', 1);
        $this->patch('/admin/products/wedding', $this->payload())->assertSessionHasNoErrors();
        $this->assertCount(4, app(ProductCatalog::class)->all());
    }

    public function test_sort_order_and_equal_order_are_deterministic_and_empty_catalog_is_supported(): void
    {
        $this->actingAs($this->admin())->patch('/admin/products/restaurant', $this->payload(['sort_order' => 0]))
            ->assertSessionHasNoErrors();
        $this->assertSame('restaurant', app(ProductCatalog::class)->all()[0]['slug']);
        Product::query()->update(['sort_order' => 10]);
        $this->assertSame(['jastip', 'photobooth', 'restaurant', 'wedding'], array_column(app(ProductCatalog::class)->all(), 'slug'));
        Product::query()->update(['status' => 'hidden']);
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->has('products', 0));
        $this->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page->has('products', 0));
        $this->get('/admin/products')->assertOk()->assertInertia(fn (Assert $page) => $page->has('products', 4));
    }

    public static function invalidInput(): array
    {
        return [
            'cannot launch unfinished product' => [['status' => 'active'], 'status'],
            'invalid icon' => [['icon' => 'arbitrary-svg'], 'icon'],
            'negative order' => [['sort_order' => -1], 'sort_order'],
            'fractional order' => [['sort_order' => 1.5], 'sort_order'],
            'oversized order' => [['sort_order' => 65536], 'sort_order'],
            'missing title' => [['title' => ''], 'title'],
            'oversized summary' => [['summary' => str_repeat('a', 256)], 'summary'],
            'oversized description' => [['description' => str_repeat('a', 3001)], 'description'],
            'immutable identifier' => [['id' => 'another-product'], 'id'],
            'immutable slug' => [['slug' => 'another-product'], 'slug'],
            'immutable phase' => [['phase' => 99], 'phase'],
        ];
    }

    #[DataProvider('invalidInput')]
    public function test_invalid_input_is_rejected_without_partial_changes(array $input, string $error): void
    {
        $this->actingAs($this->admin())->patch('/admin/products/wedding', $this->payload($input))->assertSessionHasErrors($error);
        $this->assertDatabaseHas('core_products', ['slug' => 'wedding', 'title' => 'Wedding Invitation', 'status' => 'planned', 'phase' => 2]);
    }

    public function test_unknown_products_cannot_be_edited_and_there_is_no_delete_endpoint(): void
    {
        $this->actingAs($this->admin())->get('/admin/products/missing/edit')->assertNotFound();
        $this->patch('/admin/products/missing', $this->payload())->assertNotFound();
        $this->delete('/admin/products/wedding')->assertStatus(405);
        $this->assertDatabaseCount('core_products', 4);
    }

    public function test_login_restores_catalog_editor_only_for_authorized_admin(): void
    {
        $admin = $this->admin();
        $this->get('/admin/products/wedding/edit')->assertRedirect('/login');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect('/admin/products/wedding/edit');
        $this->post('/logout');
        $customer = User::factory()->create();
        $this->get('/admin/products')->assertRedirect('/login');
        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])->assertRedirect('/dashboard');
    }
}

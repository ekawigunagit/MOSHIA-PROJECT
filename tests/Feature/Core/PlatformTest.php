<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use App\Modules\Core\Tenancy\CurrentWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_only_lists_memberships_and_rejects_foreign_selection(): void
    {
        $user = User::factory()->create();
        $own = app(CreateWorkspace::class)->handle($user, 'My business');
        $foreign = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Other business');

        $this->actingAs($user)->withSession(['tenant_id' => $foreign->id])
            ->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->has('workspaces', 1)
                ->where('activeWorkspace.id', $own->id)
                ->has('products', 4));

        $this->post(route('workspaces.select', $foreign->id))->assertNotFound();
        $this->post(route('workspaces.select', $own->id))->assertRedirect(route('dashboard'))
            ->assertSessionHas('tenant_id', $own->id);
    }

    public function test_workspace_owner_cannot_be_supplied_by_client(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'New business', 'owner_id' => $other->id,
        ])->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('core_tenants', ['name' => 'New business', 'owner_id' => $user->id]);
        $this->assertSame(1, $user->tenants()->count());
        $this->assertSame(0, $other->tenants()->count());
    }

    public function test_admin_is_protected_by_role_on_the_server(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $this->get('/admin')->assertRedirect(route('login'));
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $user->assignRole('super-admin');
        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/Index'));
    }

    public function test_access_requires_membership_valid_dates_and_active_status(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $tenant = app(CreateWorkspace::class)->handle($user, 'Mine');
        $access = app(ProductAccess::class);
        $this->assertFalse($access->allows($user, $tenant, 'wedding'));

        $grant = Entitlement::create([
            'tenant_id' => $tenant->id, 'product_slug' => 'wedding', 'status' => 'trial',
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(),
        ]);
        $this->assertTrue($access->allows($user, $tenant, 'wedding'));
        $this->assertFalse($access->allows($other, $tenant, 'wedding'));
        $this->assertFalse($access->allows($user, $tenant, 'jastip'));

        $grant->update(['ends_at' => now()]);
        $this->assertFalse($access->allows($user, $tenant, 'wedding'));
        $grant->update(['ends_at' => null, 'starts_at' => now()->addDay()]);
        $this->assertFalse($access->allows($user, $tenant, 'wedding'));
        $grant->update(['starts_at' => null, 'status' => 'revoked']);
        $this->assertFalse($access->allows($user, $tenant, 'wedding'));
    }

    public function test_product_middleware_blocks_access_and_passes_resolved_workspace(): void
    {
        Route::middleware(['web', 'auth', 'product.access:wedding'])->get('/test-product-access',
            fn (\Illuminate\Http\Request $request) => response()->json(['tenant' => $request->attributes->get('workspace')->id]));
        $user = User::factory()->create();
        $tenant = app(CreateWorkspace::class)->handle($user, 'Mine');
        $this->actingAs($user)->get('/test-product-access')->assertForbidden();
        Entitlement::create(['tenant_id' => $tenant->id, 'product_slug' => 'wedding', 'status' => 'active']);
        $this->get('/test-product-access')->assertOk()->assertJson(['tenant' => $tenant->id]);
        $tenant->members()->detach($user->id);
        $this->get('/test-product-access')->assertForbidden();
    }

    public function test_public_landing_uses_shared_product_catalog(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')->has('products', 4)->where('products.0.id', 'wedding'));
    }

    public function test_guests_cannot_create_workspaces(): void
    {
        $this->post(route('workspaces.store'), ['name' => 'Not allowed'])->assertRedirect(route('login'));
        $this->assertDatabaseCount('core_tenants', 0);
    }
}

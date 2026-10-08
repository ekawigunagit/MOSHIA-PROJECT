<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardDestinationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public static function destinations(): array
    {
        return [
            'customer default' => ['user', null, '/dashboard'],
            'admin default' => ['super-admin', null, '/admin'],
            'customer forbidden admin' => ['user', 'http://localhost/admin', '/dashboard'],
            'admin intended admin' => ['super-admin', 'http://localhost/admin', '/admin'],
            'admin own workspace' => ['super-admin', '/dashboard', '/dashboard'],
            'profile query' => ['user', 'http://localhost/profile?tab=password#password', '/profile?tab=password#password'],
            'admin profile' => ['super-admin', '/profile', '/profile'],
            'external' => ['user', 'https://example.com/profile', '/dashboard'],
            'protocol relative' => ['user', '//example.com/profile', '/dashboard'],
            'wrong port' => ['user', 'http://localhost:8080/profile', '/dashboard'],
            'credentials' => ['user', 'http://someone@localhost/profile', '/dashboard'],
            'backslash' => ['user', '/\\example.com/profile', '/dashboard'],
            'encoded path' => ['user', '/%2fexample.com/profile', '/dashboard'],
            'control character' => ['user', "/profile\r\nLocation: https://example.com", '/dashboard'],
            'login loop' => ['super-admin', '/login', '/admin'],
            'unknown page' => ['user', '/missing', '/dashboard'],
            'post only route' => ['user', '/workspaces', '/dashboard'],
            'invalid session value' => ['user', ['unexpected'], '/dashboard'],
        ];
    }

    public function test_workspace_menu_is_private_and_create_page_selects_the_new_workspace(): void
    {
        $user = User::factory()->create();
        $own = app(CreateWorkspace::class)->handle($user, 'Workspace pertama');
        $foreign = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Workspace orang lain');
        $this->actingAs($user)->get(route('workspaces.create'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Workspaces/Create')->has('workspaceNavigation', 1)->where('workspaceNavigation.0.id', $own->id));
        $this->post(route('workspaces.store'), ['name' => 'Workspace kedua'])->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('workspaceNavigation', 2)->where('activeWorkspace.name', 'Workspace kedua'));
        $this->post(route('workspaces.select', $own->id))->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('activeWorkspace.id', $own->id));
        $this->post(route('workspaces.select', $foreign->id))->assertNotFound();
    }

    #[DataProvider('destinations')]
    public function test_login_uses_an_authorized_local_destination(string $role, mixed $intended, string $expected): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->withSession(['url.intended' => $intended])->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect($expected)->assertSessionMissing('url.intended');

        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_visiting_admin_before_login_returns_to_workspace(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $this->get('/admin')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->get('/admin')->assertForbidden();
    }

    public function test_logged_in_users_and_landing_share_the_role_home(): void
    {
        foreach (['user' => '/dashboard', 'super-admin' => '/admin'] as $role => $home) {
            $user = User::factory()->create();
            $user->assignRole($role);
            $this->actingAs($user)->get('/login')->assertRedirect($home);
            $this->get('/register')->assertRedirect($home);
            $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
                ->where('auth.homeUrl', $home)->missing('stats'));
        }
    }

    public function test_admin_statistics_do_not_grant_foreign_workspace_or_product_access(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $own = app(CreateWorkspace::class)->handle($admin, 'Admin workspace');
        $foreign = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Customer workspace');
        Entitlement::create(['tenant_id' => $foreign->id, 'product_id' => \App\Modules\Core\Catalog\Models\Product::where('slug', 'wedding')->value('id'), 'status' => 'active']);

        $this->actingAs($admin)->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Index')->where('stats.users', 2)
            ->where('stats.workspaces', 2)->where('stats.products', 4));
        $this->withSession(['tenant_id' => $foreign->id])->get('/dashboard')
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')->has('workspaces', 1)
            ->where('activeWorkspace.id', $own->id)->missing('stats'));
        $this->post(route('workspaces.select', $foreign))->assertNotFound();
        $this->assertFalse(app(ProductAccess::class)->allows($admin, $foreign, 'wedding'));
        $this->assertFalse(app(ProductAccess::class)->allows($admin, $own, 'wedding'));
    }
}

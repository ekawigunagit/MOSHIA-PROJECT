<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Identity\Actions\UpdatePlatformUser;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
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
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        return $admin;
    }

    public function test_guests_and_customers_cannot_manage_users(): void
    {
        $target = User::factory()->create();
        $payload = ['name' => 'Changed', 'email' => $target->email, 'roles' => ['super-admin']];
        $this->get(route('admin.users.index'))->assertRedirect('/login');
        $this->get(route('admin.users.edit', $target))->assertRedirect('/login');
        $this->patch(route('admin.users.update', $target), $payload)->assertRedirect('/login');
        $customer = User::factory()->create();
        $customer->assignRole('user');
        $this->actingAs($customer)->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.users.edit', $target))->assertForbidden();
        $this->patch(route('admin.users.update', $target), $payload)->assertForbidden();
        $this->assertFalse(Gate::forUser($customer)->allows('update', $target));
        $this->assertFalse($target->fresh()->hasRole('super-admin'));
        $this->assertNotSame('Changed', $target->fresh()->name);
    }

    public function test_search_and_role_filter_are_combined_and_private_fields_are_not_exposed(): void
    {
        $admin = $this->admin();
        $match = User::factory()->create(['name' => 'Rina Search']);
        $match->assignRole('user');
        $other = User::factory()->create(['name' => 'Rina Admin']);
        $other->assignRole('super-admin');
        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Rina', 'role' => 'user']))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Users/Index')->where('users.total', 1)
            ->where('users.data.0.id', $match->id)->missing('users.data.0.password')
            ->missing('users.data.0.remember_token')->where('filters.search', 'Rina'));
        $this->get(route('admin.users.index', ['search' => $match->email]))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->where('users.total', 1));
        $this->get(route('admin.users.index', ['search' => 'no-such-account']))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->where('users.total', 0));
    }

    public function test_users_are_paginated_with_filters_preserved(): void
    {
        $admin = $this->admin();
        User::factory()->count(17)->create(['name' => 'Pagination User']);
        $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Pagination']))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 15)->where('users.total', 17)
            ->where('users.next_page_url', fn ($url) => str_contains($url, 'search=Pagination')));
        $this->get(route('admin.users.index', ['search' => 'Pagination', 'page' => 2]))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->has('users.data', 2));
    }

    public function test_admin_can_update_identity_and_roles_without_changing_unrelated_fields(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $target->assignRole('user');
        $password = $target->password;
        $this->actingAs($admin)->get(route('admin.users.edit', $target))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Users/Edit')
                ->where('canUpdateRoles', true)->missing('account.password'));
        $this->patch(route('admin.users.update', $target), [
            'name' => 'Updated User', 'email' => 'updated@example.com',
            'roles' => ['user', 'super-admin'], 'password' => 'injected-password',
            'email_verified_at' => now(), 'id' => $admin->id,
        ])->assertRedirect(route('admin.users.edit', $target))->assertSessionHasNoErrors();
        $target->refresh();
        $this->assertSame('Updated User', $target->name);
        $this->assertSame('updated@example.com', $target->email);
        $this->assertNull($target->email_verified_at);
        $this->assertSame($password, $target->password);
        $this->assertTrue($target->hasAllRoles(['user', 'super-admin']));

        $this->patch(route('admin.users.update', $target), [
            'name' => $target->name, 'email' => $target->email, 'roles' => ['user'],
        ])->assertSessionHasNoErrors();
        $this->assertFalse($target->fresh()->hasRole('super-admin'));
        $this->actingAs($target->fresh())->get('/admin')->assertRedirect(route('verification.notice'));
        $target->markEmailAsVerified();
        $this->actingAs($target->fresh())->get('/admin')->assertForbidden();
    }

    public function test_unchanged_email_keeps_verification_and_self_identity_can_be_edited(): void
    {
        $admin = $this->admin();
        $verified = $admin->email_verified_at->toISOString();
        $this->actingAs($admin)->get(route('admin.users.edit', $admin))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('canUpdateRoles', false));
        $this->patch(route('admin.users.update', $admin), ['name' => 'My name', 'email' => $admin->email])
            ->assertSessionHasNoErrors();
        $this->assertSame($verified, $admin->fresh()->email_verified_at->toISOString());
        $this->assertTrue($admin->fresh()->hasRole('super-admin'));
    }

    public function test_self_role_changes_are_rejected_even_when_other_admins_exist(): void
    {
        $admin = $this->admin();
        $this->admin();
        $this->actingAs($admin)->patch(route('admin.users.update', $admin), [
            'name' => 'Should not save', 'email' => $admin->email, 'roles' => ['user'],
        ])->assertSessionHasErrors('roles');
        $this->assertTrue($admin->fresh()->hasRole('super-admin'));
        $this->assertSame($admin->name, $admin->fresh()->name);
    }

    public static function invalidRoles(): array
    {
        return [
            'unknown' => [['owner'], 'roles.0'],
            'different guard' => [['api-only'], 'roles.0'],
            'empty' => [[], 'roles'],
            'string' => ['super-admin', 'roles'],
            'duplicate' => [['user', 'user'], 'roles.0'],
        ];
    }

    #[DataProvider('invalidRoles')]
    public function test_invalid_roles_do_not_save_partial_updates(mixed $roles, string $error): void
    {
        $admin = $this->admin();
        Role::findOrCreate('api-only', 'api');
        $target = User::factory()->create();
        $target->assignRole('user');
        $this->actingAs($admin)->patch(route('admin.users.update', $target), [
            'name' => 'Invalid change', 'email' => $target->email, 'roles' => $roles,
        ])->assertSessionHasErrors($error);
        $this->assertSame($target->name, $target->fresh()->name);
        $this->assertEquals(['user'], $target->fresh()->getRoleNames()->all());
    }

    public function test_duplicate_email_and_missing_users_are_rejected(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $this->actingAs($admin)->patch(route('admin.users.update', $target), [
            'name' => $target->name, 'email' => $admin->email, 'roles' => ['super-admin'],
        ])->assertSessionHasErrors('email');
        $this->assertFalse($target->fresh()->hasRole('super-admin'));
        $this->get(route('admin.users.edit', 99999))->assertNotFound();
        $this->patch(route('admin.users.update', 99999), [])->assertNotFound();
    }

    public function test_mutation_rechecks_actor_role_instead_of_trusting_cached_permission(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $admin->load('roles');
        $admin->fresh()->syncRoles(['user']);
        try {
            app(UpdatePlatformUser::class)->handle($admin, $target, [
                'name' => 'Forbidden', 'email' => $target->email, 'roles' => ['super-admin'],
            ]);
            $this->fail('A demoted actor must not be allowed to change users.');
        } catch (AuthorizationException) {
            $this->assertSame($target->name, $target->fresh()->name);
            $this->assertFalse($target->fresh()->hasRole('super-admin'));
        }
    }

    public function test_login_preserves_authorized_admin_user_destination_only(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();
        $path = route('admin.users.edit', $target, false);
        $this->get($path)->assertRedirect('/login');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertRedirect($path);
        $this->post('/logout');
        $this->get($path)->assertRedirect('/login');
        $this->post('/login', ['email' => $target->email, 'password' => 'password'])->assertRedirect('/dashboard');
    }
}

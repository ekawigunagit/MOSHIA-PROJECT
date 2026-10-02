<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountDeletionSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_admin_cannot_delete_account_and_remains_logged_in(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertRedirect('/profile')->assertSessionHasErrors('account');
        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_an_admin_can_delete_only_when_another_admin_remains(): void
    {
        $this->seed(RoleSeeder::class);
        $first = User::factory()->create();
        $second = User::factory()->create();
        $first->assignRole('super-admin');
        $second->assignRole('super-admin');
        $this->actingAs($first)->delete('/profile', ['password' => 'password'])->assertRedirect('/');
        $this->assertGuest();
        $this->actingAs($second)->delete('/profile', ['password' => 'password'])->assertSessionHasErrors('account');
        $this->assertDatabaseMissing('users', ['id' => $first->id]);
        $this->assertDatabaseHas('users', ['id' => $second->id]);
    }

    public function test_workspace_owner_deletion_keeps_workspace_membership_and_entitlement(): void
    {
        $owner = User::factory()->create();
        $tenant = app(CreateWorkspace::class)->handle($owner, 'Keep me');
        Entitlement::create(['tenant_id' => $tenant->id, 'product_id' => Product::firstOrFail()->id, 'status' => 'active']);
        $this->actingAs($owner)->delete('/profile', ['password' => 'password'])->assertSessionHasErrors('account');
        $this->assertAuthenticatedAs($owner);
        $this->assertDatabaseCount('core_tenants', 1);
        $this->assertDatabaseCount('core_tenant_user', 1);
        $this->assertDatabaseCount('core_entitlements', 1);
    }

    public function test_database_prevents_owner_deletion_outside_http_action(): void
    {
        $owner = User::factory()->create();
        app(CreateWorkspace::class)->handle($owner, 'Protected');
        $this->expectException(QueryException::class);
        DB::table('users')->where('id', $owner->id)->delete();
    }

    public function test_member_without_ownership_can_delete_without_removing_workspace(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $tenant = app(CreateWorkspace::class)->handle($owner, 'Shared');
        $tenant->members()->attach($member->id);
        $this->actingAs($member)->delete('/profile', ['password' => 'password'])->assertRedirect('/');
        $this->assertDatabaseHas('core_tenants', ['id' => $tenant->id, 'owner_id' => $owner->id]);
        $this->assertDatabaseMissing('core_tenant_user', ['user_id' => $member->id]);
    }
}

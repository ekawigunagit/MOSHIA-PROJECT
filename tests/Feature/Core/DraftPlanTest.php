<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Billing\Models\Plan;
use App\Modules\Core\Catalog\Models\Product;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DraftPlanTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_replace(['name' => 'Draft Basic', 'product_id' => Product::where('slug', 'wedding')->value('id'), 'description' => 'Catatan rencana'], $overrides);
    }

    public function test_guests_customers_and_unverified_admins_cannot_manage_drafts(): void
    {
        $plan = Plan::create($this->payload());
        foreach ([null, User::factory()->create(), User::factory()->unverified()->create()] as $user) {
            if ($user) {
                $user->assignRole($user->hasVerifiedEmail() ? 'user' : 'super-admin');
                $this->actingAs($user);
            }
            foreach (['/admin/plans', '/admin/plans/create', '/admin/plans/'.$plan->id.'/edit'] as $url) {
                $this->get($url)->assertStatus($user?->hasVerifiedEmail() ? 403 : 302);
            }
            $this->post('/admin/plans', $this->payload())->assertStatus($user?->hasVerifiedEmail() ? 403 : 302);
            $this->patch('/admin/plans/'.$plan->id, $this->payload(['name' => 'Forbidden']))
                ->assertStatus($user?->hasVerifiedEmail() ? 403 : 302);
        }
        $this->assertDatabaseCount('core_plans', 1);
        $this->assertSame('Draft Basic', $plan->fresh()->name);
    }

    public function test_admin_can_create_and_edit_draft_without_publishing_or_granting_access(): void
    {
        $this->actingAs($this->admin())->get('/admin/plans/create')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Plans/Form')->where('plan', null)->has('products', 4));
        $this->post('/admin/plans', $this->payload())->assertSessionHasNoErrors();
        $plan = Plan::firstOrFail();
        $this->assertSame('draft', $plan->status);
        $this->get('/admin/plans')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Plans/Index')->where('plans.data.0.product.slug', 'wedding'));
        $this->patch('/admin/plans/'.$plan->id, $this->payload(['name' => 'Updated Draft']))
            ->assertRedirect(route('admin.plans.edit', $plan))->assertSessionHasNoErrors();
        $this->assertSame('Updated Draft', $plan->fresh()->name);
        $this->assertDatabaseCount('core_entitlements', 0);
        $this->get('/dashboard')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('plans'));
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->missing('plans'));
    }

    public function test_plan_name_is_unique_per_product_but_can_be_used_on_another_product(): void
    {
        $this->actingAs($this->admin())->post('/admin/plans', $this->payload())->assertSessionHasNoErrors();
        $this->post('/admin/plans', $this->payload())->assertSessionHasErrors('name');
        $this->post('/admin/plans', $this->payload(['product_id' => Product::where('slug', 'jastip')->value('id')]))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('core_plans', 2);
    }

    public static function invalidInput(): array
    {
        return [
            'publish' => [['status' => 'active'], 'status'],
            'price not configured' => [['price_amount' => 100000], 'price_amount'],
            'trial not configured' => [['trial_days' => 7], 'trial_days'],
            'missing name' => [['name' => ''], 'name'],
            'unknown product' => [['product_id' => 99999], 'product_id'],
            'long description' => [['description' => str_repeat('a', 5001)], 'description'],
        ];
    }

    #[DataProvider('invalidInput')]
    public function test_invalid_draft_is_not_saved(array $data, string $error): void
    {
        $this->actingAs($this->admin())->post('/admin/plans', $this->payload($data))->assertSessionHasErrors($error);
        $this->assertDatabaseCount('core_plans', 0);
    }

    public function test_product_referenced_by_draft_cannot_be_deleted(): void
    {
        $plan = Plan::create($this->payload());
        $this->expectException(QueryException::class);
        $plan->product->delete();
    }

    public function test_login_restores_plan_editor_for_admin(): void
    {
        $admin = $this->admin();
        $plan = Plan::create($this->payload());
        $this->get(route('admin.plans.edit', $plan))->assertRedirect('/login');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.plans.edit', $plan));
    }

    public function test_drafts_are_paginated_and_unknown_plan_returns_not_found(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            Plan::create($this->payload(['name' => 'Draft '.$i]));
        }
        $this->actingAs($this->admin())->get('/admin/plans')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('plans.data', 15)->where('plans.total', 16));
        $this->get('/admin/plans/99999/edit')->assertNotFound();
        $this->patch('/admin/plans/99999', $this->payload())->assertNotFound();
    }
}

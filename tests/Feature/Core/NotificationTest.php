<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Identity\Actions\DeleteAccount;
use App\Modules\Core\Notification\Actions\RecordNotification;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_or_update_notifications(): void
    {
        $this->getJson(route('notifications.index'))->assertUnauthorized();
        $this->patchJson(route('notifications.read-all'))->assertUnauthorized();
    }

    public function test_list_is_paginated_and_only_contains_own_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        for ($i = 0; $i < 12; $i++) {
            app(RecordNotification::class)->handle($user, 'Own '.$i, 'My activity');
        }
        $foreign = app(RecordNotification::class)->handle($other, 'Private other', 'Secret');
        $this->actingAs($user)->getJson(route('notifications.index'))
            ->assertOk()->assertJsonCount(10, 'items')->assertJsonPath('total', 12)
            ->assertJsonPath('unread_count', 12)->assertJsonMissing(['id' => $foreign->id]);
        $this->getJson(route('notifications.index', ['page' => 2]))->assertJsonCount(2, 'items');
        $this->getJson(route('notifications.index', ['page' => -1]))->assertUnprocessable();
    }

    public function test_mark_read_is_idempotent_and_cannot_access_another_account(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('super-admin');
        $own = app(RecordNotification::class)->handle($user, 'Own', 'Message');
        $foreign = app(RecordNotification::class)->handle(User::factory()->create(), 'Private', 'Message');
        $this->actingAs($user)->patchJson(route('notifications.read', $foreign->id))->assertNotFound();
        $this->patchJson(route('notifications.read', $own->id))->assertNoContent();
        $readAt = $own->fresh()->read_at;
        $this->travel(1)->minutes();
        $this->patchJson(route('notifications.read', $own->id))->assertNoContent();
        $this->assertTrue($readAt->equalTo($own->fresh()->read_at));
        $this->assertNull($foreign->fresh()->read_at);
        $this->getJson(route('notifications.index'))->assertJsonPath('unread_count', 0);
    }

    public function test_mark_all_only_marks_current_accounts_notifications(): void
    {
        $user = User::factory()->create();
        app(RecordNotification::class)->handle($user, 'One', 'Message');
        app(RecordNotification::class)->handle($user, 'Two', 'Message');
        $foreign = app(RecordNotification::class)->handle(User::factory()->create(), 'Private', 'Message');
        $this->actingAs($user)->patchJson(route('notifications.read-all'))->assertNoContent();
        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertNull($foreign->fresh()->read_at);
    }

    public function test_workspace_activity_is_recorded_and_rolled_back_with_transaction(): void
    {
        $user = User::factory()->create();
        app(CreateWorkspace::class)->handle($user, 'Workspace Test');
        $this->assertSame(1, $user->unreadNotifications()->count());
        $this->assertStringContainsString('Workspace Test', $user->notifications()->first()->data['message']);
        try {
            DB::transaction(function () use ($user) {
                app(CreateWorkspace::class)->handle($user, 'Should rollback');
                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('rollback', $exception->getMessage());
        }
        $this->assertSame(1, $user->notifications()->count());
        $this->assertDatabaseMissing('core_tenants', ['name' => 'Should rollback']);
    }

    public function test_saving_draft_notifies_actor_but_not_other_users_or_failed_validation(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $other = User::factory()->create();
        $payload = ['product_id' => Product::firstOrFail()->id, 'name' => 'Draft Test', 'description' => 'Internal only'];
        $this->actingAs($admin)->post(route('admin.plans.store'), $payload)->assertSessionHasNoErrors();
        $plan = \App\Modules\Core\Billing\Models\Plan::firstOrFail();
        $this->assertSame(1, $admin->notifications()->count());
        $this->patch(route('admin.plans.update', $plan), [...$payload, 'name' => 'Edited'])->assertSessionHasNoErrors();
        $this->assertSame(2, $admin->notifications()->count());
        $this->post(route('admin.plans.store'), [...$payload, 'name' => ''])->assertSessionHasErrors('name');
        $this->assertSame(2, $admin->notifications()->count());
        $this->assertSame(0, $other->notifications()->count());
    }

    public function test_account_deletion_removes_its_notifications_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        app(RecordNotification::class)->handle($user, 'Own', 'Message');
        app(RecordNotification::class)->handle($other, 'Other', 'Message');
        app(DeleteAccount::class)->handle($user);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(1, $other->notifications()->count());
    }
}

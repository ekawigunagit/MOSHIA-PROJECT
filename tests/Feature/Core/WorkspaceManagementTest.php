<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkspaceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_rename_only_the_target_workspace(): void
    {
        $owner = User::factory()->create();
        $workspace = app(CreateWorkspace::class)->handle($owner, 'Lama');
        $other = app(CreateWorkspace::class)->handle($owner, 'Tetap');
        $this->actingAs($owner)->get(route('workspaces.edit', $workspace->id))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Workspaces/Edit')->where('workspace.id', $workspace->id));
        $this->patch(route('workspaces.update', $workspace->id), ['name' => 'Baru', 'owner_id' => 999])
            ->assertRedirect(route('workspaces.edit', $workspace->id));
        $this->assertSame('Baru', $workspace->fresh()->name);
        $this->assertSame($owner->id, $workspace->fresh()->owner_id);
        $this->assertSame('Tetap', $other->fresh()->name);
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
            ->where('activeWorkspace.name', 'Baru')->where('canManageWorkspace', true)
            ->where('workspaceNavigation.0.name', 'Baru'));
        foreach (['', str_repeat('a', 101)] as $name) {
            $this->patch(route('workspaces.update', $workspace->id), ['name' => $name])->assertSessionHasErrors('name');
        }
        $this->assertSame('Baru', $workspace->fresh()->name);
    }

    public function test_non_owner_cannot_edit_even_as_a_member(): void
    {
        $workspace = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Privat');
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('workspaces.edit', $workspace->id))->assertNotFound();
        $this->patch(route('workspaces.update', $workspace->id), ['name' => 'Tidak boleh'])->assertNotFound();
        $workspace->members()->attach($user->id);
        $this->get(route('workspaces.edit', $workspace->id))->assertNotFound();
        $this->patch(route('workspaces.update', $workspace->id), ['name' => 'Tidak boleh'])->assertNotFound();
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('canManageWorkspace', false));
        $this->assertSame('Privat', $workspace->fresh()->name);
    }
}

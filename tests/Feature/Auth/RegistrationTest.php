<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user', 'web'));
        $this->assertSame(1, $user->tenants()->count());
        $this->assertSame($user->id, $user->tenants()->first()->owner_id);
        $this->assertFalse($user->hasRole('super-admin', 'web'));
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_role_seeder_can_be_run_repeatedly(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseHas('roles', ['name' => 'user', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'super-admin', 'guard_name' => 'web']);
    }

    public function test_missing_role_does_not_leave_a_partial_account(): void
    {
        $this->withoutExceptionHandling();

        try {
            $this->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $this->fail('Registration should fail when its required role is missing.');
        } catch (RoleDoesNotExist $exception) {
            $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
            $this->assertDatabaseCount('core_tenants', 0);
            $this->assertGuest();
        }
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class RequiredEmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
    }

    public function test_unverified_users_cannot_access_core_even_as_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->unverified()->create();
        $user->assignRole('super-admin');
        $tenant = app(CreateWorkspace::class)->handle($user, 'Existing workspace');
        $this->actingAs($user);
        foreach (['/dashboard', '/admin', '/admin/users'] as $path) {
            $this->get($path)->assertRedirect(route('verification.notice'));
        }
        $this->post('/workspaces', ['name' => 'Blocked'])->assertRedirect(route('verification.notice'));
        $this->post(route('workspaces.select', $tenant))->assertRedirect(route('verification.notice'));
        $this->patch(route('admin.users.update', $user), ['name' => 'Blocked', 'email' => $user->email])
            ->assertRedirect(route('verification.notice'));
        $this->get('/profile')->assertOk();
        $this->assertDatabaseCount('core_tenants', 1);
        $this->assertNotSame('Blocked', $user->fresh()->name);
    }

    public function test_login_requires_verification_and_restores_safe_intended_page_afterwards(): void
    {
        $user = User::factory()->unverified()->create();
        $this->withSession(['url.intended' => '/profile'])->post('/login', [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect('/verify-email')->assertSessionHas('url.intended', '/profile');
        $this->get($this->verificationUrl($user))->assertRedirect('/profile');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get('/dashboard')->assertOk();
    }

    public function test_verification_uses_role_home_and_does_not_follow_external_intended_urls(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->unverified()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin)->withSession(['url.intended' => 'https://example.com/'])
            ->get($this->verificationUrl($admin))->assertRedirect('/admin');
        $this->get('/verify-email')->assertRedirect('/admin');
    }

    public function test_verification_link_opened_while_logged_out_resumes_after_login(): void
    {
        $user = User::factory()->unverified()->create();
        $url = $this->verificationUrl($user);
        $this->get($url)->assertRedirect('/login');
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertRedirect($url);
        $this->get($response->headers->get('Location'))->assertRedirect('/dashboard');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_expired_tampered_wrong_account_and_old_email_links_are_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        $url = $this->verificationUrl($user);
        $this->actingAs($user)->get($url.'&tampered=1')->assertForbidden();
        $this->travel(61)->minutes();
        $this->get($url)->assertForbidden();
        $this->travelBack();
        $other = User::factory()->unverified()->create();
        $this->actingAs($other)->get($url)->assertForbidden();
        $user->update(['email' => 'new-address@example.com']);
        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertFalse($other->fresh()->hasVerifiedEmail());
    }

    public function test_resend_is_throttled_and_does_not_send_for_verified_accounts(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        for ($i = 0; $i < 6; $i++) {
            $this->from('/verify-email')->post(route('verification.send'))
                ->assertSessionHas('status', 'verification-link-sent');
        }
        $this->post(route('verification.send'))->assertTooManyRequests();
        Notification::assertSentToTimes($user, VerifyEmail::class, 6);
        $verified = User::factory()->create();
        $this->actingAs($verified)->post(route('verification.send'))->assertRedirect('/dashboard');
        Notification::assertNotSentTo($verified, VerifyEmail::class);
    }

    public function test_mail_failure_keeps_registration_pending_and_allows_retry(): void
    {
        $this->seed(RoleSeeder::class);
        Notification::shouldReceive('send')->once()->andThrow(new TransportException('Test SMTP failure'));
        $this->post('/register', [
            'name' => 'Pending User', 'email' => 'pending@example.com',
            'password' => 'password', 'password_confirmation' => 'password',
            'email_verified_at' => now()->toISOString(),
        ])->assertRedirect('/verify-email')->assertSessionHasErrors('email');
        $user = User::where('email', 'pending@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertSame(1, $user->tenants()->count());
        $this->get('/dashboard')->assertRedirect('/verify-email');
        Notification::fake();
        $this->from('/verify-email')->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_transport_failure_returns_recoverable_error(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new TransportException('Test SMTP failure'));
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->from('/verify-email')->post(route('verification.send'))
            ->assertRedirect('/verify-email')->assertSessionHasErrors('email');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_account_cannot_use_product_even_with_entitlement(): void
    {
        Route::middleware(['web', 'auth', 'product.access:wedding'])->get('/test-verified-product', fn () => 'Product');
        $user = User::factory()->unverified()->create();
        $tenant = app(CreateWorkspace::class)->handle($user, 'Mine');
        Entitlement::create(['tenant_id' => $tenant->id, 'product_slug' => 'wedding', 'status' => 'active']);
        $this->actingAs($user)->get('/test-verified-product')->assertForbidden();
        $user->markEmailAsVerified();
        $this->get('/test-verified-product')->assertOk();
    }

    public function test_verification_mail_uses_a_signed_expiring_link_and_moshia_copy(): void
    {
        $user = User::factory()->unverified()->create();
        $mail = (new VerifyEmail)->toMail($user);
        $this->assertSame('Verifikasi email akun Moshia', $mail->subject);
        $this->assertTrue(URL::hasValidSignature(Request::create($mail->actionUrl)));
        $this->assertStringContainsString('/verify-email/'.$user->id.'/', $mail->actionUrl);
        $this->assertStringContainsString('expires=', $mail->actionUrl);
    }
}

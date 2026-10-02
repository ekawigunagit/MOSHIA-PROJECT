<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Core\Billing\Models\Plan;
use App\Modules\Core\Billing\Policies\PlanPolicy;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Catalog\Policies\ProductPolicy;
use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Entitlement\Services\DatabaseProductAccess;
use App\Modules\Core\Identity\Policies\UserPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductAccess::class,
            DatabaseProductAccess::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Plan::class, PlanPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        VerifyEmail::toMailUsing(fn (object $user, string $url) => (new MailMessage)
            ->subject('Verifikasi email akun Moshia')
            ->greeting('Halo, '.$user->name.'!')
            ->line('Klik tombol di bawah untuk memverifikasi email dan mulai menggunakan Moshia.')
            ->action('Verifikasi email', $url)
            ->line('Tautan berlaku selama '.config('auth.verification.expire', 60).' menit.')
            ->line('Jika Anda tidak mendaftar di Moshia, abaikan email ini.')
            ->salutation('Tim Moshia')
            ->view(['html' => 'emails.verify-email', 'text' => 'emails.verify-email-text'], [
                'recipientName' => $user->name,
                'verificationUrl' => $url,
                'expiresInMinutes' => config('auth.verification.expire', 60),
            ]));

        Gate::policy(
            User::class,
            UserPolicy::class,
        );
        Vite::prefetch(concurrency: 3);
    }
}

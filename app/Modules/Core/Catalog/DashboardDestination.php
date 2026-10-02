<?php

namespace App\Modules\Core\Catalog;

use App\Models\User;
use App\Modules\Core\Billing\Models\Plan;
use App\Modules\Core\Catalog\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class DashboardDestination
{
    public function home(User $user): string
    {
        if (! $user->hasVerifiedEmail()) {
            return route('verification.notice', absolute: false);
        }

        return route($user->hasRole('super-admin') ? 'admin.index' : 'dashboard', absolute: false);
    }

    public function afterLogin(Request $request): string
    {
        $fallback = $this->home($request->user());
        $intended = $request->session()->pull('url.intended');

        if (! is_string($intended) || preg_match('/[\\\\\x00-\x20]/', $intended)) {
            return $fallback;
        }

        $parts = parse_url($intended);
        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
            return $fallback;
        }

        // Accept absolute URLs only from this application, never external redirects.
        if (isset($parts['host']) || isset($parts['scheme'])) {
            $origin = parse_url($request->getSchemeAndHttpHost());
            if (($parts['scheme'] ?? null) !== $origin['scheme']
                || ($parts['host'] ?? null) !== $origin['host']
                || ($parts['port'] ?? null) !== ($origin['port'] ?? null)) {
                return $fallback;
            }
        }

        // Explicitly allow the current read-only account destinations. Product
        // routes need their own access checks before joining this list.
        $allowed = [route('dashboard', absolute: false), route('profile.edit', absolute: false)];
        $path = $parts['path'] ?? '';
        $local = $path
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
        $verificationPath = route('verification.verify', [
            'id' => $request->user()->id,
            'hash' => sha1($request->user()->getEmailForVerification()),
        ], absolute: false);
        if ($path === $verificationPath
            && URL::hasValidSignature(Request::create($request->getSchemeAndHttpHost().$local))) {
            return $local;
        }

        if ($request->user()->hasRole('super-admin')) {
            $allowed[] = route('admin.index', absolute: false);
            $plansPath = route('admin.plans.index', absolute: false);
            $allowed[] = $plansPath;
            $allowed[] = route('admin.plans.create', absolute: false);
            if (preg_match('#^'.preg_quote($plansPath, '#').'/([1-9][0-9]*)/edit$#', $path, $matches)) {
                $plan = Plan::find($matches[1]);
                if ($plan && $request->user()->can('update', $plan)) {
                    $allowed[] = $path;
                }
            }
            $productsPath = route('admin.products.index', absolute: false);
            $allowed[] = $productsPath;
            if (preg_match('#^'.preg_quote($productsPath, '#').'/([a-z0-9-]+)/edit$#', $path, $matches)) {
                $product = Product::where('slug', $matches[1])->first();
                if ($product && $request->user()->can('update', $product)) {
                    $allowed[] = $path;
                }
            }
            $usersPath = route('admin.users.index', absolute: false);
            $allowed[] = $usersPath;
            if (preg_match('#^'.preg_quote($usersPath, '#').'/([1-9][0-9]*)/edit$#', $path, $matches)) {
                $target = User::find($matches[1]);
                if ($target && $request->user()->can('update', $target)) {
                    $allowed[] = $path;
                }
            }
        }

        if (! in_array($path, $allowed, true)) {
            return $fallback;
        }

        if (! $request->user()->hasVerifiedEmail()) {
            $request->session()->put('url.intended', $local);

            return $fallback;
        }

        return $local;
    }
}

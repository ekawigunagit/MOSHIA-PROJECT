<?php

namespace App\Modules\Core\Billing\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DevelopmentPayments
{
    public static function enabled(): bool
    {
        return app()->environment(['local', 'testing']) && config('billing.manual_development_enabled');
    }

    public function handle(Request $request, Closure $next): mixed
    {
        abort_unless(self::enabled(), 404);

        return $next($request);
    }
}

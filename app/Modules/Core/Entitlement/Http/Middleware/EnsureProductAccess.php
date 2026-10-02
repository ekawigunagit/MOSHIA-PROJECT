<?php

namespace App\Modules\Core\Entitlement\Http\Middleware;

use App\Modules\Core\Entitlement\Contracts\ProductAccess;
use App\Modules\Core\Tenancy\CurrentWorkspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProductAccess
{
    public function __construct(private CurrentWorkspace $workspace, private ProductAccess $access) {}

    public function handle(Request $request, Closure $next, string $product): Response
    {
        $tenant = $this->workspace->resolve($request);
        abort_unless(
            $request->user() && $request->user()->hasVerifiedEmail()
                && $tenant && $this->access->allows($request->user(), $tenant, $product),
            403,
            'Workspace tidak memiliki akses aktif untuk produk ini.'
        );
        $request->attributes->set('workspace', $tenant);

        return $next($request);
    }
}

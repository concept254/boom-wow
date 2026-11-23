<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the admin area: back-office roles only, and switches on
 * business-unit data isolation (see BelongsToBusiness).
 */
class EnsureBusinessAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user && $user->is_active && $user->isBackOffice(), 403);

        app()->instance('admin_scope', true);

        return $next($request);
    }
}

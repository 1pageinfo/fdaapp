<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Allow the request only if the authenticated user is a superadmin or
     * holds the given permission slug (e.g. "sanghs.view").
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('superadmin') || $user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this feature.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        if (count($roles) === 0) {
            abort(403, 'Unauthorized');
        }

        $userRole = strtolower(trim((string) $user->role));

        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode('|', $role) as $singleRole) {
                $singleRole = trim($singleRole);
                if ($singleRole !== '') {
                    $allowedRoles[] = strtolower($singleRole);
                }
            }
        }

        if ($userRole === '' || !in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}

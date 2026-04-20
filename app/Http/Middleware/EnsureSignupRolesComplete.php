<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSignupRolesComplete
{
    /**
     * Verified users must finish the “how you’ll use the site” step before browsing elsewhere.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($user->registration_roles_completed_at !== null) {
            return $next($request);
        }

        if ($request->routeIs([
            'account',
            'account.instructor',
            'register.roles',
            'register.roles.store',
            'logout',
            'verification.notice',
            'verification.send',
            'verification.verify',
        ])) {
            return $next($request);
        }

        return redirect()->route('register.roles');
    }
}

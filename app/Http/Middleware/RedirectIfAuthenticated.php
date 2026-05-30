<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * If the user is already authenticated on any of the given guards,
     * redirect them to the dashboard appropriate for their role instead of
     * letting them reach a guest-only route (login/register).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(self::redirectPathForUser(Auth::guard($guard)->user()));
            }
        }

        return $next($request);
    }

    /**
     * Resolve the post-authentication redirect path for the given user based
     * on their role.
     *
     * Seekers go to their profile, employers to the employer dashboard, admins
     * to the admin dashboard. Anything else falls back to the public job list.
     */
    public static function redirectPathForUser(?User $user): string
    {
        if ($user === null) {
            return '/jobs';
        }

        return match (true) {
            $user->isSeeker() => '/profile',
            $user->isEmployer() => '/employer/dashboard',
            $user->isAdmin() => '/admin/dashboard',
            default => '/jobs',
        };
    }
}

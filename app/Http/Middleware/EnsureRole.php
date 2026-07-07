<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request, ensuring the authenticated user has one of
     * the allowed roles. Aborts with 403 Forbidden otherwise.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            abort(403);
        }

        if (! in_array(Auth::user()->role, $roles, true)) {
            abort(403);
        }
        return $next($request);
    }
}
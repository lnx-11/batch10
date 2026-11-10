<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // The auth middleware runs first; only an account with the admin role can continue.
        abort_unless($request->user()?->isAdmin(), 403, 'Akses hanya tersedia untuk admin.');

        return $next($request);
    }
}

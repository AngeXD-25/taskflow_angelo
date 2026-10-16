<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check() || !in_array(Auth::user()->role?->slug, $roles)) {
            abort(403, 'No tienes permisos para acceder a esta función.');
        }

        return $next($request);
    }
}
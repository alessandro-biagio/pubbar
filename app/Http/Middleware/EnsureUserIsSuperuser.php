<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSuperuser
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->is_superuser) {
            abort(403, 'Only superusers can access this area.');
        }
        return $next($request);
    }
}

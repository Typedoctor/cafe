<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware {
    public function handle($request, Closure $next, $role) {
        if (!Auth::check() || Auth::user()->privilege !== $role) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Unauthorized Access']);
        }
        
        return $next($request);
    }
}

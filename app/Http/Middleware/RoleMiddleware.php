<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware {
    public function handle($request, Closure $next, $role) {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['error' => 'Please login first']);
        }
        
        //if (Auth::user()->privilege !== $role) {
            //$redirectRoute = Auth::user()->isManager() ? 'manager.dashboard' : 'cashier.dashboard';
          //  return redirect()->route($redirectRoute)->withErrors(['error' => 'Unauthorized Access']);
        //}
        
        return $next($request);
    }
}

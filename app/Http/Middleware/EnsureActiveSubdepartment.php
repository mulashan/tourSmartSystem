<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveSubdepartment
{
    public function handle(Request $request, Closure $next, string $module)
    {
        if (! session('logged_in')) {
            return redirect('/login');
        }

        // Switched context (e.g. Storage and Supplies -> Procurement) — destroy the old pick.
        if (session('active_subdepartment_module') !== $module) {
            session()->forget(['active_subdepartment_id', 'active_subdepartment_module']);
        }

        if (! session('active_subdepartment_id')) {
            session(['url.intended' => $request->fullUrl()]);

            return redirect()->route('storage-supplies.select-subdepartment', ['module' => $module]);
        }

        return $next($request);
    }
}
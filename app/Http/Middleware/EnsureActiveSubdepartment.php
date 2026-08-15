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

        $remembered = session("subdepartment_by_module.{$module}");

        if ($remembered && session('active_subdepartment_module') !== $module) {
            session(['active_subdepartment_id' => $remembered, 'active_subdepartment_module' => $module]);
        }

        if (! session('active_subdepartment_id') || session('active_subdepartment_module') !== $module) {
            session(['url.intended' => $request->fullUrl()]);
            return redirect()->route('storage-supplies.select-subdepartment', ['module' => $module]);
        }

        return $next($request);
    }
}
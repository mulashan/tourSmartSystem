<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClearSubdepartmentOnModuleExit
{
    public function handle(Request $request, Closure $next)
    {
        $activeModule = session('active_subdepartment_module');

        if ($activeModule) {
            $prefix = config("storage_modules.{$activeModule}.prefix");

            if (! $prefix || ! str_starts_with($request->path(), $prefix)) {
                session()->forget(['active_subdepartment_id', 'active_subdepartment_module']);
            }
        }

        return $next($request);
    }
}
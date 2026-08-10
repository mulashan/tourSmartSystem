<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSessionNotExpired
{
    public function handle(Request $request, Closure $next)
    {
        if (! session('logged_in')) {
            return $next($request);
        }

        $timeoutMinutes = session('active_branch_session_timeout', 30);
        $lastActivity = session('last_activity');

        if ($lastActivity && (now()->timestamp - $lastActivity) > ($timeoutMinutes * 60)) {
            $request->session()->flush();
            $request->session()->invalidate();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Session expired.'], 419);
            }

            return redirect('/login')->with('error', 'Your session expired due to inactivity. Please log in again.');
        }

        $request->session()->put('last_activity', now()->timestamp);

        return $next($request);
    }
}
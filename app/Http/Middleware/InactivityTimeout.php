<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InactivityTimeout
{
    protected $timeout = 10;
    public function handle(Request $request, Closure $next)
    {
        $lastActivity = session('lastActivityTime');
        $currentTime = now()->timestamp;
        if ($lastActivity && ($currentTime - $lastActivity) > $this->timeout) {
            Auth::logout();
            session()->invalidate();
            return response()->json(['status' => 'failed', 'message' => 'Session expired due to inactivity.'], 401);
        }
        session(['lastActivityTime' => $currentTime]);
        return $next($request);
    }
}

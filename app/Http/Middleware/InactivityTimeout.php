<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class InactivityTimeout
{
    protected $timeout = 180; // Timeout period in seconds

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            $cacheKey = 'user_last_activity_' . $user->id;
            $lastActivity = Cache::get($cacheKey);
            $currentTime = now()->timestamp;

            // Ensure $lastActivity is an integer or default to current time if null
            if ($lastActivity === null) {
                $lastActivity = $currentTime;
            }

            if (($currentTime - $lastActivity) > $this->timeout) {
                // Logout user and invalidate session
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Remove the cache entry
                Cache::forget($cacheKey);

                return response()->json(['status' => 'failed', 'message' => 'Session expired due to inactivity.'], 401);
            }

            // Update the last activity time in cache
            Cache::put($cacheKey, $currentTime, $this->timeout);
        }

        return $next($request);
    }
}

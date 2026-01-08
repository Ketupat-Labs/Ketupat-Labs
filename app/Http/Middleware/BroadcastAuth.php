<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BroadcastAuth
{
    /**
     * Handle an incoming request.
     * Authenticates user from Auth facade OR session for broadcasting routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure session is started
        if (!$request->hasSession()) {
            \Log::warning('BroadcastAuth: No session available', [
                'cookies' => $request->cookies->all(),
                'headers' => $request->headers->all(),
            ]);
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Session required for authentication.',
            ], 403);
        }

        // First try Laravel's Auth facade (standard Laravel authentication)
        if (Auth::check()) {
            \Log::debug('BroadcastAuth: Authenticated via Auth::check()', [
                'user_id' => Auth::id(),
            ]);
            return $next($request);
        }
        
        // Try to authenticate using the session guard
        // This handles cases where session exists but Auth facade hasn't loaded the user
        if (Auth::guard('web')->check()) {
            \Log::debug('BroadcastAuth: Authenticated via Auth::guard("web")->check()', [
                'user_id' => Auth::guard('web')->id(),
            ]);
            return $next($request);
        }
        
        // If not authenticated via Auth, try session-based auth
        // This is the primary auth method used in this app
        $userId = session('user_id');
        if ($userId) {
            try {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    // Log the user in via Auth facade so broadcasting channels work
                    Auth::login($user, false); // false = don't remember (session-based)
                    \Log::debug('BroadcastAuth: Authenticated via session("user_id")', [
                        'user_id' => $user->id,
                    ]);
                    return $next($request);
                } else {
                    \Log::warning('BroadcastAuth: User not found', [
                        'user_id' => $userId,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('BroadcastAuth: Error loading user', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // Try to get user from session using Laravel's session key
        // Laravel stores user ID in session as 'login_web_*' or similar
        try {
            $sessionData = session()->all();
            foreach ($sessionData as $key => $value) {
                if (str_contains($key, 'login_') && is_numeric($value)) {
                    $user = \App\Models\User::find($value);
                    if ($user) {
                        Auth::login($user, false);
                        \Log::debug('BroadcastAuth: Authenticated via Laravel session key', [
                            'key' => $key,
                            'user_id' => $user->id,
                        ]);
                        return $next($request);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('BroadcastAuth: Error reading session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        
        // Log detailed debug info for troubleshooting
        $sessionId = session()->getId();
        $sessionData = session()->all();
        $hasSessionUserId = session()->has('user_id');
        
        \Log::warning('BroadcastAuth: Unauthorized access attempt', [
            'auth_check' => Auth::check(),
            'auth_guard_check' => Auth::guard('web')->check(),
            'session_user_id' => session('user_id'),
            'session_id' => $sessionId,
            'has_session_user_id' => $hasSessionUserId,
            'session_keys' => array_keys($sessionData),
            'session_data' => $sessionData,
            'cookies' => $request->cookies->all(),
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
        ]);
        
        // Return 403 - this is expected for unauthenticated users
        // The frontend should handle this gracefully and fall back to polling
        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'You must be authenticated to access private channels.',
            'debug' => config('app.debug') ? [
                'auth_check' => Auth::check(),
                'auth_guard_check' => Auth::guard('web')->check(),
                'session_user_id' => session('user_id'),
                'has_session_user_id' => session()->has('user_id'),
                'session_id' => $sessionId,
                'session_keys' => array_keys($sessionData),
            ] : null
        ], 403);
    }
}

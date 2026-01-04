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
        // First try Laravel's Auth facade
        if (Auth::check()) {
            return $next($request);
        }
        
        // If not authenticated via Auth, try session-based auth
        $userId = session('user_id');
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                // Log the user in via Auth facade so broadcasting channels work
                Auth::login($user);
        return $next($request);
            }
        }
        
        // Not authenticated - return 403 with debug info (remove in production)
        \Log::warning('BroadcastAuth: Unauthorized access attempt', [
            'auth_check' => Auth::check(),
            'session_user_id' => session('user_id'),
            'session_id' => session()->getId(),
            'has_session' => session()->has('user_id'),
        ]);
        
        return response()->json([
            'error' => 'Unauthorized',
            'debug' => config('app.debug') ? [
                'auth_check' => Auth::check(),
                'session_user_id' => session('user_id'),
                'has_session' => session()->has('user_id'),
            ] : null
        ], 403);
    }
}

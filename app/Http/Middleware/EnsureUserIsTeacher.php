<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsTeacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Fallback: Check session manually if auth()->user() is null
        if (!$user && session('user_id')) {
            $user = \App\Models\User::find(session('user_id'));
        }

        // Check if user is authenticated (found)
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Anda perlu log masuk untuk mengakses ciri ini.',
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Anda perlu log masuk untuk mengakses ciri ini.');
        }

        // Check if user has teacher role
        if ($user->role !== 'teacher') {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Hanya cikgu dibenarkan menggunakan ciri ini. Pelajar tidak boleh menjana slaid atau kuiz.',
                ], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Hanya cikgu dibenarkan menggunakan ciri ini. Pelajar tidak boleh menjana slaid atau kuiz.');
        }

        return $next($request);
    }
}

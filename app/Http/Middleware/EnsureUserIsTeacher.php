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
        // Check if user is authenticated
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Anda perlu log masuk untuk mengakses ciri ini.',
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Anda perlu log masuk untuk mengakses ciri ini.');
        }

        // Check if user has teacher role
        if (auth()->user()->role !== 'teacher') {
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

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        // Add CORS headers to the response
        return $response
            ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
            ->header('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * Get the allowed origin for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getAllowedOrigin(Request $request): string
    {
        $origin = $request->header('Origin');
        
        // Allow all origins in development, or configure specific origins
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        
        // When credentials are required, we cannot use '*', must return specific origin
        if (in_array('*', $allowedOrigins)) {
            // For development, allow the requesting origin if present
            if ($origin) {
                return $origin;
            }
            // Fallback for same-origin requests (no Origin header)
            return $request->getSchemeAndHttpHost();
        }
        
        // Check if the origin is in the allowed list
        if ($origin && in_array($origin, $allowedOrigins)) {
            return $origin;
        }
        
        // If no origin header and not wildcard, return first allowed origin
        // or the request's own origin for same-origin requests
        return $origin ?: $request->getSchemeAndHttpHost();
    }
}


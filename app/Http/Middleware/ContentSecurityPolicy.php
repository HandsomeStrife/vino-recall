<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Build Content Security Policy
        $isLocal = app()->environment('local');

        // Base CSP
        $csp = [
            "default-src 'self'",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://fonts.bunny.net https://fonts.gstatic.com",
            'frame-src https://js.stripe.com',
            "base-uri 'self'",
            "form-action 'self'",
        ];

        // Script sources (different for local vs production)
        if ($isLocal) {
            // In development, allow Vite dev server
            $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://localhost:5183 https://js.stripe.com https://cdn.jsdelivr.net";
            $csp[] = "connect-src 'self' ws://localhost:5173 ws://localhost:5183 http://localhost:5173 http://localhost:5183 https://api.stripe.com";
        } else {
            // In production, stricter policy
            $csp[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://cdn.jsdelivr.net";
            $csp[] = "connect-src 'self' https://api.stripe.com";
        }

        // Style sources (allow external fonts and Vite dev server)
        if ($isLocal) {
            $csp[] = "style-src 'self' 'unsafe-inline' http://localhost:5173 http://localhost:5183 https://fonts.bunny.net https://fonts.googleapis.com";
        } else {
            $csp[] = "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com";
        }

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // Additional security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}

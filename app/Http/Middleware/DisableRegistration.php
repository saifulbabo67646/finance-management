<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableRegistration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Block registration routes for non-authenticated users
        if ($request->routeIs('register') || $request->routeIs('register.store')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Registration is disabled. Contact your administrator.'
                ], 403);
            }
            
            return redirect()->route('login')->with('error', 'Registration is disabled. Contact your administrator.');
        }

        return $next($request);
    }
}

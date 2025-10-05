<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockDisallowedHttpMethods
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        if (!in_array($request->method(), $allowed, true)) {
            return response()->json([
                'message' => 'Método ' . $request->method() . ' não permitido'
            ], 405);
        }

        return $next($request);
    }
}

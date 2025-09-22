<?php

namespace App\Http\Middleware;

use App\Http\Responses\JsonResponse;
use Closure;
use Illuminate\Http\Request;

class TokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): \Illuminate\Http\JsonResponse
    {
        $token = env('DEBUG_QUERY_API_KEY');
        $requestToken = $request->query('token');

        if (empty($token) || $requestToken !== $token) {
            return JsonResponse::Send(null, 'Invalid or missing API key', 401);
        }

        if ($request->has('token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->query('token'));
        }

        return $next($request);
    }
}

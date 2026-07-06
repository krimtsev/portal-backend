<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Responses\JsonResponse;
use Closure;
use Illuminate\Http\Request;

final class TokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): \Illuminate\Http\JsonResponse
    {
        $token = env('DEBUG_QUERY_API_KEY');
        $requestToken = $request->query('token');

        if (empty($token) || $requestToken !== $token) {
            return JsonResponse::Send(null, trans('auth.invalid_key'), 401);
        }

        if ($request->has('token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->query('token'));
        }

        return $next($request);
    }
}

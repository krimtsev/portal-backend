<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Responses\JsonResponse;
use Closure;
use Illuminate\Http\Request;

final readonly class TokenAuth
{
    public function handle(Request $request, Closure $next, ?string $ability = null): \Illuminate\Http\JsonResponse
    {
        $bearer = $request->bearerToken();

        if (empty($bearer)) {
            return JsonResponse::Send(null, trans('auth.invalid_key'), 401);
        }

        $tokenData = config('api_tokens.tokens')[$bearer] ?? null;

        if ($tokenData === null) {
            return JsonResponse::Send(null, trans('auth.invalid_key'), 401);
        }

        $abilities = $tokenData['abilities'] ?? [];

        if ($ability !== null && !in_array($ability, $abilities, true)) {
            return JsonResponse::Send(null, trans('auth.forbidden'), 403);
        }

        $request->attributes->set('portal', $tokenData['portal'] ?? null);
        $request->attributes->set('token_abilities', $abilities);

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\User\DashboardSection;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        if (DashboardSection::accessibleFor($user->role ?? null) === []) {
            abort(403, 'Dashboard access required');
        }

        return $next($request);
    }
}

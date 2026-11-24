<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\User\DashboardSection;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureDashboardSection
{
    public function handle(Request $request, Closure $next, string $section): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $sectionEnum = DashboardSection::tryFrom($section);

        if ($sectionEnum === null) {
            // Конфиг-ошибка: раздел объявлен в роутере, но не в enum
            abort(500, "Unknown dashboard section [{$section}]");
        }

        if (!$sectionEnum->allows($user->role ?? null)) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }
}

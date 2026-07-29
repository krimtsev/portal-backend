<?php

namespace App\Http\Middleware;

use App\Enums\User\UserRole;
use App\Responses\JsonResponse;
use App\Services\Maintenance\MaintenanceService;
use Closure;
use Illuminate\Http\Request;

final readonly class CheckMaintenanceMode
{
    public function __construct(
        private MaintenanceService $maintenanceService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        if ($this->maintenanceService->isEnabled()) {
            $user = $request->user();

            if ($user && $user->role === UserRole::Sysadmin->value) {
                return $next($request);
            }

            return JsonResponse::Maintenance();
        }

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel;

use App\Enums\User\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\Panel\PanelAnalyticsResource;
use App\Responses\JsonResponse;
use App\Services\Panel\PanelAnalyticsService;
use Illuminate\Http\Request;

final class PanelController extends Controller
{
    public function __construct(
        private readonly PanelAnalyticsService $analyticsService
    ) {}

    /**
     * Получить сводную статистику по тикетам и партнерам на панели управления.
     */
    public function analytics(Request $request): \Illuminate\Http\JsonResponse
    {
        $isSysadmin = $request->user()?->hasRole(UserRole::Sysadmin->value) ?? false;

        $stats = $this->analyticsService->getSummaryStats($isSysadmin);

        return JsonResponse::Send([
            'data' => new PanelAnalyticsResource($stats),
        ]);
    }
}

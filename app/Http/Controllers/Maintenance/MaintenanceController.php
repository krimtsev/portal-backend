<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Requests\Maintenance\MaintenanceRequest;
use App\Responses\JsonResponse;
use App\Services\Maintenance\MaintenanceService;

final readonly class MaintenanceController
{
    public function __construct(
        private MaintenanceService $maintenanceService
    ) {}

    public function get()
    {
        return JsonResponse::Send([
            'data' => [
                'enabled' => $this->maintenanceService->isEnabled(),
            ],
        ]);
    }

    public function update(MaintenanceRequest $request)
    {
        $status = $this->maintenanceService->updateStatus($request->input('enabled'));

        return JsonResponse::Send([
            'data' => [
                'enabled' => $status,
            ],
        ]);
    }
}

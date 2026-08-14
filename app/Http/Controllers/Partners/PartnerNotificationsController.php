<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partners;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\PartnerUpdateNotificationRequest;
use App\Http\Resources\Partner\PartnerNotificationListResource;
use App\Http\Resources\Partner\PartnerNotificationResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Partners\PartnerNotificationsService;
use Illuminate\Http\Request;

final class PartnerNotificationsController extends Controller
{
    public function __construct(
        private readonly PartnerNotificationsService $notificationsService
    ) {}

    /**
     * Список партнеров с их настройками
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Partner::query()
            ->select('id', 'name', 'yclients_id', 'disabled')
            ->with(['notificationChannel', 'reportSettings']);

        $result = Pagination::paginate(
            $query,
            $request,
            ['name'],
            ['name'],
            ['disabled'],
        );

        // Оборачиваем коллекцию в ресурс
        $result['list'] = PartnerNotificationListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    /**
     * Получение информации по одному партнеру
     */
    public function get(int $id): \Illuminate\Http\JsonResponse
    {
        $partner = $this->notificationsService->get($id);

        return JsonResponse::Send([
            'data' => new PartnerNotificationResource($partner),
        ]);
    }

    /**
     * Обновление настроек PartnerNotificationChannel и PartnerReportSetting
     */
    public function update(PartnerUpdateNotificationRequest $request, Partner $partner): \Illuminate\Http\JsonResponse
    {
        $this->notificationsService->update(
            partner: $partner,
            notificationData: $request->validated('notification_channel', []),
            reportData: $request->validated('report_settings', [])
        );

        return JsonResponse::Updated();
    }
}

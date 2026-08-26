<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partners;

use App\Enums\NotificationChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\PartnerSendMessageRequest;
use App\Http\Resources\Partner\PartnerMessageResource;
use App\Models\Partner\Partner;
use App\Responses\JsonResponse;
use App\Services\Partners\PartnerMessageService;

final class PartnerMessagesController extends Controller
{
    public function options(): \Illuminate\Http\JsonResponse
    {
        $partners = Partner::query()
            ->select(['id', 'name'])
            ->withActiveYclients()
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM)
            ->orderBy('name')
            ->get();

        return JsonResponse::Send([
            'list' => PartnerMessageResource::collection($partners),
        ]);
    }

    /**
     * Получение информации по одному партнеру
     */
    public function send(
        PartnerSendMessageRequest $request,
        PartnerMessageService $service
    ): \Illuminate\Http\JsonResponse
    {
        $service->broadcast(
            $request->validated('partner_ids'),
            $request->validated('message'),
            $request->file('file')
        );

        return JsonResponse::Send([
            'message' => 'Рассылка успешно запущена',
        ]);
    }
}

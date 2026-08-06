<?php

declare(strict_types=1);

namespace App\Http\Resources\Partner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PartnerNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,

            'notification_channel' => [
                'send_telegram'    => $this->notificationChannel?->send_telegram ?? false,
                'telegram_chat_id' => $this->notificationChannel?->telegram_chat_id ?? null,
                'check_payment'    => $this->notificationChannel?->check_payment ?? false,
                'payment_date'     => $this->notificationChannel?->payment_date?->format('Y-m-d'),
            ],

            'report_settings' => [
                'lost_clients_days'     => $this->reportSettings?->lost_clients_days ?? 0,
                'returned_clients_days' => $this->reportSettings?->returned_clients_days ?? 0,
                'new_clients_days'      => $this->reportSettings?->new_clients_days ?? 0,
            ],
        ];
    }
}

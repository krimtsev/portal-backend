<?php

declare(strict_types=1);

namespace App\Models\Partner\Traits;

use App\Enums\NotificationChannel;
use App\Models\Partner\Partner;
use Illuminate\Support\Collection;

trait HasPartnerReportDispatch
{
    /**
     * @return Collection<int, Partner>
     */
    protected function getTargetPartners(?string $companyId): Collection
    {
        return Partner::query()
            ->with(['reportSettings', 'notificationChannel'])
            // Проверяем, что есть YClients и партнер активен
            ->withActiveYclients()
            // Проверяем, что можно писать в конкретный канал (Telegram) и всё оплачено
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM)
            // Опциональный фильтр по конкретной компании
            ->when($companyId, fn ($query) => $query->where('yclients_id', $companyId))
            ->get();
    }
}

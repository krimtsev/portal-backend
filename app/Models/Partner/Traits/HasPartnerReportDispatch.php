<?php

declare(strict_types=1);

namespace App\Models\Partner\Traits;

use App\Enums\NotificationChannel;
use App\Models\Partner\Partner;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

trait HasPartnerReportDispatch
{
    /**
     * @return Collection<int, Partner>
     */
    private function getTargetPartnersBySetting(?string $companyId, string $settingKey): Collection
    {
        return Partner::query()
            ->with(['reportSettings', 'notificationChannel'])
            ->withActiveYclients()
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM)
            ->when($companyId, fn (Builder $query) => $query->where('yclients_id', $companyId))
            ->whereHas('reportSettings', fn (Builder $q) => $q->where($settingKey, '>', 0))
            ->get();
    }

    /**
     * @return Collection<int, Partner>
     */
    protected function getTargetPartnersForNewClients(?string $companyId): Collection
    {
        return $this->getTargetPartnersBySetting($companyId, 'new_clients_days');
    }

    /**
     * @return Collection<int, Partner>
     */
    protected function getTargetPartnersForLostClients(?string $companyId): Collection
    {
        return $this->getTargetPartnersBySetting($companyId, 'lost_clients_days');
    }

    /**
     * @return Collection<int, Partner>
     */
    protected function getTargetPartnersForReturnedClients(?string $companyId): Collection
    {
        return $this->getTargetPartnersBySetting($companyId, 'returned_clients_days');
    }
}

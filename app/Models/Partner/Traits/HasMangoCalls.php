<?php

declare(strict_types=1);

namespace App\Models\Partner\Traits;

use App\Enums\NotificationChannel;
use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait HasMangoCalls
{
    /**
     * Получить партнера для отправки уведомления о пропущенном звонке.
     * Проверяет привязку номера, настройки отчетов и доступность Telegram.
     */
    protected function getPartnerForMissedCall(string $calledNumber): ?Partner
    {
        return Partner::query()
            ->with(['notificationChannel'])
            ->withActiveYclients()
            ->where('mango_telnum', $calledNumber)
            ->whereHas('reportSettings', function ($query) {
                $query->where('send_missed_calls', true);
            })
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM)
            ->first();
    }

    /**
     * Получить список партнеров для рассылки отчетов по пропущенным звонкам.
     */
    protected function getPartnersForReport(?string $companyId = null): Collection
    {
        return Partner::query()
            ->with(['notificationChannel'])
            ->withActiveYclients()
            ->whereHas('reportSettings', function (Builder $query) {
                $query->where('send_missed_calls', true);
            })
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM)
            ->when($companyId, fn (Builder $query, string $id) => $query->where('yclients_id', $id))
            ->get();
    }
}

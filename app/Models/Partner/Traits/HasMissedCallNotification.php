<?php

declare(strict_types=1);

namespace App\Models\Partner\Traits;

use App\Enums\NotificationChannel;
use App\Models\Partner\Partner;

trait HasMissedCallNotification
{
    /**
     * Получить партнера для отправки уведомления о пропущенном звонке.
     * Проверяет привязку номера, настройки отчетов и доступность Telegram.
     */
    protected function getTargetPartnerForMissedCall(string $calledNumber): ?Partner
    {
        return Partner::query()
            ->with(['notificationChannel'])
            ->where('mango_telnum', $calledNumber)
            ->whereHas('reportSettings', function ($query) {
                $query->where('send_missed_calls', true);
            })
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM)
            ->first();
    }
}

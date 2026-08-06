<?php

declare(strict_types=1);

namespace App\Services\Partners;

use App\Models\Partner\Partner;
use Illuminate\Support\Facades\DB;

final readonly class PartnerNotificationsService
{
    /**
     * Получение настроек конкретного партнера
     */
    public function get(int $partnerId): Partner
    {
        return Partner::query()
            ->with(['notificationChannel', 'reportSettings'])
            ->findOrFail($partnerId);
    }

    /**
     * Обновление настроек партнера
     */
    public function update(Partner $partner, array $notificationData, array $reportData)
    {
        DB::transaction(function () use ($partner, $notificationData, $reportData) {
            if (!empty($notificationData)) {
                $partner->notificationChannel()->updateOrCreate(
                    ['partner_id' => $partner->id],
                    $notificationData
                );
            }

            if (!empty($reportData)) {
                $partner->reportSettings()->updateOrCreate(
                    ['partner_id' => $partner->id],
                    $reportData
                );
            }
        });
    }
}

<?php

namespace App\Services\Mango;

use App\Enums\Mango\MangoContextStatus;
use App\Jobs\Reports\SendDailyMissedCallsReportJob;
use App\Models\Mango\MangoCall;
use App\Models\Partner\Traits\HasMangoCalls;
use App\Services\Formatters\MangoMissedCallFormatter;
use Illuminate\Support\Carbon;

final class MangoDailyReportService
{
    use HasMangoCalls;

    public function __construct() {}

    /**
     * @return int Количество чатов, по которым были поставлены задачи
     */
    public function dispatchReports(Carbon $date, ?string $companyId = null): int
    {
        $partners = $this->getPartnersForReport($companyId);

        if ($partners->isEmpty()) {
            return 0;
        }

        $telnums = $partners->pluck('mango_telnum')->filter()->unique()->toArray();

        if (empty($telnums)) {
            return 0;
        }

        $statsByTelnum = $this->getBulkDailyStats($telnums, $date);

        $reportsGroupedByChat = [];

        foreach ($partners as $partner) {
            $telnum = $partner->mango_telnum;

            if (empty($telnum)) {
                continue;
            }

            $branchStats = $statsByTelnum[$telnum] ?? [
                'total'    => 0,
                'accepted' => 0,
                'missed'   => 0,
            ];

            $chatId = $partner->notificationChannel->telegram_chat_id;

            $reportsGroupedByChat[$chatId][] = [
                'branch' => $partner->name,
                'stats'  => $branchStats,
            ];
        }

        foreach ($reportsGroupedByChat as $chatId => $reports) {
            $messageText = MangoMissedCallFormatter::formatDailyReport($reports, $date);

            SendDailyMissedCallsReportJob::dispatch($chatId, $messageText);
        }

        return count($reportsGroupedByChat);
    }

    /**
     * Получить статистику сразу по массиву номеров (Избавляемся от N+1)
     *
     * @param string[] $telnums
     * @return array<string, array{total: int, accepted: int, missed: int}>
     */
    private function getBulkDailyStats(array $telnums, Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay   = $date->copy()->endOfDay();

        $stats = MangoCall::query()
            ->incoming()
            ->period($startOfDay, $endOfDay)
            ->whereIn('called_number', $telnums) // Используем IN вместо =
            ->toBase()
            ->selectRaw('
                called_number,
                COUNT(*) as total,
                SUM(CASE WHEN context_status = ? THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN context_status = ? THEN 1 ELSE 0 END) as missed
            ', [
                MangoContextStatus::SUCCESS->value,
                MangoContextStatus::FAILED->value
            ])
            ->groupBy('called_number')
            ->get();

        // Превращаем коллекцию в удобный ассоциативный массив с ключом по номеру телефона
        return $stats->keyBy('called_number')->map(fn ($item) => [
            'total'    => (int) $item->total,
            'accepted' => (int) $item->accepted,
            'missed'   => (int) $item->missed,
        ])->toArray();
    }
}

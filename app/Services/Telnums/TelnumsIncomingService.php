<?php

declare(strict_types=1);

namespace App\Services\Telnums;

use App\Constants\Timezone\Timezone;
use App\Enums\Mango\MangoContextStatus;
use App\Enums\Mango\MangoContextType;
use App\Models\Mango\MangoCall;
use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class TelnumsIncomingService
{
    /**
     * Партнеры + статистика входящих звонков за день.
     */
    public function getIncomingStatsQuery(string $date): Builder
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $stats = MangoCall::query()
            ->selectRaw(
                'called_number,
                 SUM(context_status = ?) AS success_count,
                 SUM(context_status = ?) AS failed_count',
                [
                    MangoContextStatus::SUCCESS->value,
                    MangoContextStatus::FAILED->value,
                ]
            )
            ->where('context_type', MangoContextType::INCOMING->value)
            ->where('context_start_time', '>=', $startOfDay)
            ->where('context_start_time', '<=', $endOfDay)
            ->groupBy('called_number');

        return Partner::query()
            ->leftJoinSub($stats, 'calls', 'calls.called_number', '=', 'partners.mango_telnum')
            ->whereNotNull('partners.mango_telnum')
            ->where('partners.disabled', '=', false)
            ->select([
                'partners.id',
                'partners.name',
                'partners.mango_telnum',
            ])
            ->selectRaw('COALESCE(calls.success_count, 0) AS success_count')
            ->selectRaw('COALESCE(calls.failed_count, 0) AS failed_count')
            ->selectRaw('COALESCE(calls.success_count, 0) + COALESCE(calls.failed_count, 0) AS total_count')
            ->selectRaw(
    'COALESCE(
                    COALESCE(calls.failed_count, 0) * 100
                    / NULLIF(COALESCE(calls.success_count, 0) + COALESCE(calls.failed_count, 0), 0),
                    0
                ) AS failed_percent'
            );
    }

    /**
     * Почасовая статистика входящих звонков.
     *
     * @param Carbon      $startLocal   Начало окна (в локальной таймзоне), округляется до часа
     * @param int         $hours        Сколько часов показать (12 для панели, 24 для суток)
     * @param string|null $calledNumber Если задан - статистика только по этому номеру
     *
     * @return array<int, array{time: string, incoming: int, missed: int}>
     */
    public function getHourlyCallsStats(
        Carbon $startLocal,
        int $hours,
        ?string $calledNumber = null
    ): array {
        $startLocal = $startLocal->copy()->setTimezone(Timezone::DEFAULT)->startOfHour();
        $endLocal = $startLocal->copy()->addHours($hours)->subSecond();

        $slots = [];
        for ($i = 0; $i < $hours; $i++) {
            $key = $startLocal->copy()->addHours($i)->format('H:00');
            $slots[$key] = [
                'time' => $key,
                'incoming' => 0,
                'missed' => 0
            ];
        }

        $rows = MangoCall::query()
            ->incoming()
            ->period($startLocal->copy()->utc(), $endLocal->copy()->utc())
            ->when($calledNumber !== null, fn ($q) => $q->where('called_number', $calledNumber))
            ->selectRaw(
                'DATE_FORMAT(CONVERT_TZ(context_start_time, \'+00:00\', ?), "%H:00") as hour_time,
                 COUNT(CASE WHEN context_status = ? THEN 1 END) as incoming_count,
                 COUNT(CASE WHEN context_status = ? THEN 1 END) as missed_count',
                [
                    $startLocal->format('P'), // смещение локальной зоны, напр. +03:00
                    MangoContextStatus::SUCCESS->value,
                    MangoContextStatus::FAILED->value,
                ]
            )
            ->groupBy('hour_time')
            ->get();

        foreach ($rows as $row) {
            if (isset($slots[$row->hour_time])) {
                $slots[$row->hour_time]['incoming'] = (int) $row->incoming_count;
                $slots[$row->hour_time]['missed'] = (int) $row->missed_count;
            }
        }

        return array_values($slots);
    }

    /**
     * Почасовая статистика партнера за сутки (24 часа).
     */
    public function getPartnerDayHourlyStats(Partner $partner, string $date): array
    {
        $startOfDay = Carbon::parse($date, Timezone::DEFAULT)->startOfDay();

        return $this->getHourlyCallsStats($startOfDay, 24, $partner->mango_telnum);
    }
}

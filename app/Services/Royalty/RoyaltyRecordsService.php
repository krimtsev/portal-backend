<?php

declare(strict_types=1);

namespace App\Services\Royalty;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class RoyaltyRecordsService
{
    /**
     * Получает статистику выполненных визитов по всем сотрудникам партнера за месяц + итоговую строку.
     */
    public function getRoyaltyReport(int $partnerId, Carbon $monthInput): array
    {
        $startDate = $monthInput->copy()->startOfMonth()->format('Y-m-d 00:00:00');
        $endDate = $monthInput->copy()->endOfMonth()->format('Y-m-d 23:59:59');
        $daysInMonth = $monthInput->daysInMonth;

        $totals = array_fill(0, $daysInMonth, 0);

        $rows = DB::table('yc_records')
            ->select([
                'yc_company_staff.staff_id',
                'yc_company_staff.name',
                'yc_company_staff.specialization',
                'yc_company_staff.avatar',
                DB::raw('DAY(yc_records.datetime) as record_day'),
                DB::raw('COUNT(yc_records.record_id) as total_records'),
            ])
            ->join('yc_company_staff', 'yc_records.staff_id', '=', 'yc_company_staff.staff_id')
            ->join('partners', 'yc_company_staff.company_id', '=', 'partners.yclients_id')
            ->where('partners.id', $partnerId)
            ->where('yc_records.attendance', 1)
            ->where('yc_records.deleted', 0)
            ->whereBetween('yc_records.datetime', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereRaw("LOWER(yc_company_staff.name) NOT REGEXP 'лист'")
                    ->where(function ($q) {
                        $q->whereNull('yc_company_staff.specialization')
                            ->orWhereRaw("LOWER(yc_company_staff.specialization) NOT REGEXP 'admin|админ|лист'");
                    });
            })
            ->groupBy([
                'yc_company_staff.staff_id',
                'yc_company_staff.name',
                'yc_company_staff.specialization',
                'yc_company_staff.avatar',
                DB::raw('DAY(yc_records.datetime)'),
            ])
            ->get();

        if ($rows->isEmpty()) {
            return [
                'list'   => collect([]),
                'totals' => $totals,
            ];
        }

        // Агрегируем результат по сотрудникам и дням в PHP за один проход
        $groupedByStaff = [];

        foreach ($rows as $row) {
            $staffId = $row->staff_id;

            if (!isset($groupedByStaff[$staffId])) {
                $groupedByStaff[$staffId] = [
                    'staff' => [
                        'staff_id'       => $row->staff_id,
                        'name'           => $row->name,
                        'specialization' => $row->specialization,
                        'avatar'         => $row->avatar,
                    ],
                    'data' => array_fill(0, $daysInMonth, 0),
                ];
            }

            $dayIndex = ((int) $row->record_day) - 1;
            $count = (int) $row->total_records;

            $groupedByStaff[$staffId]['data'][$dayIndex] = $count;
            $totals[$dayIndex] += 1;
        }

        return [
            'list'   => collect(array_values($groupedByStaff)),
            'totals' => $totals,
        ];
    }
}

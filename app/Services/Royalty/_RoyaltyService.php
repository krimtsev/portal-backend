<?php

namespace App\Services\Royalty;

use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class _RoyaltyService
{
    /**
     * Подготовка и расчет сетки роялти для коллекции действующих партнеров
     */
    public function prepareMonthlyRoyaltyList(Collection $partners, string $targetMonth): Collection
    {
        // Приводим целевую дату к началу отчетного месяца
        $currentMonthDate = Carbon::parse($targetMonth)->startOfMonth();

        return $partners->map(function (Partner $partner) use ($currentMonthDate) {
            $incomeTotal = (float) $partner->income_total;

            // Высчитываем коммерческие метрики
            $calculations = $this->calculateContractMetrics(
                $partner->opened_at,
                $currentMonthDate,
                $incomeTotal
            );

            // Возвращаем структуру, готовую для передачи в RoyaltyListResource
            return [
                'partner_id'       => $partner->id,
                'name'             => $partner->name,
                'opened_at'        => $partner->opened_at ? $partner->opened_at->format('Y-m-d') : null,
                'income_total'     => $incomeTotal,
                'royalty_percent'  => $calculations['percent'],
                'royalty_base_sum' => $calculations['royalty_sum'],
                'nds_sum'          => $calculations['nds_sum'],
                'sum_with_nds'     => $calculations['sum_with_nds'],
            ];
        });
    }

    /**
     * Калькулятор условий договора франшизы
     */
    private function calculateContractMetrics(?Carbon $openedAt, Carbon $currentMonth, float $incomeTotal): array
    {
        if (!$openedAt) {
            return [
                'percent'      => 0.0,
                'royalty_sum'  => 0.0,
                'nds_sum'      => 0.0,
                'sum_with_nds' => 0.0,
            ];
        }

        // Приводим обе даты к началу месяца для жесткого календарного подсчета
        $openedAtMonth = $openedAt->copy()->startOfMonth();
        $currentMonthDate = $currentMonth->copy()->startOfMonth();

        // Считаем разницу в календарных месяцах
        $diffInMonths = $openedAtMonth->diffInMonths($currentMonthDate);

        // 1. Налоговые каникулы: первые два календарных месяца (месяц подписания + следующий)
        if ($diffInMonths < 2) {
            return [
                'percent'      => 0.0,
                'royalty_sum'  => 0.0,
                'nds_sum'      => 0.0,
                'sum_with_nds' => 0.0,
            ];
        }

        // 2. Расчет шага индексации (+0.25% каждый год после отчетного периода)
        // Вычитаем 1, чтобы июнь следующего года стал триггером для перехода на новую ставку
        $yearsPassed = (int) floor(($diffInMonths - 1) / 12);

        $calculatedPercent = 2.5 + ($yearsPassed * 0.25);

        // 3. Ограничение максимальной ставки в 5%
        if ($calculatedPercent > 5.0) {
            $calculatedPercent = 5.0;
        }

        // 4. Финансовые расчеты с округлением до копеек
        $royaltySum = $incomeTotal * ($calculatedPercent / 100);
        $ndsSum = $royaltySum * 0.05; // НДС 5% поверх суммы роялти
        $sumWithNds = $royaltySum + $ndsSum;

        return [
            'percent'      => $calculatedPercent,
            'royalty_sum'  => round($royaltySum, 2),
            'nds_sum'      => round($ndsSum, 2),
            'sum_with_nds' => round($sumWithNds, 2),
        ];
    }
}

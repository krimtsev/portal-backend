<?php

namespace App\Services\Royalty;

use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RoyaltyService
{
    private const VAT_PERCENT = 5;

    private const ROYALTY_STEP = 0.25;

    private const MAX_ROYALTY_PERCENT = 5;

    public function transform(
        Collection $partners,
        Carbon $monthInput,
    ): Collection {
        return $partners->map(
            function (Partner $partner) use ($monthInput) {

                $grossRevenue = round((float) $partner->income_total, 2);

                $royaltyPercent = $this->calculateRoyaltyPercent(
                    $partner->opened_at,
                    $monthInput
                );

                $royaltyAmount = $this->calculateRoyaltyAmount(
                    $grossRevenue,
                    $royaltyPercent
                );

                $vatAmount = $this->calculateVatAmount($royaltyAmount);

                return [
                    'partner_id'       => $partner->id,
                    'partner_name'     => $partner->name,
                    'gross_revenue'    => $grossRevenue,
                    'royalty_percent'  => $royaltyPercent,
                    'royalty_amount'   => $royaltyAmount,
                    'vat_percent'      => self::VAT_PERCENT,
                    'vat_amount'       => $vatAmount,
                    'royalty_with_vat' => round($royaltyAmount + $vatAmount, 2),
                    'days_count'       => (int) $partner->days_count,
                    'opened_at'        => $partner->opened_at,
                ];
            }
        );
    }

    public function calculateRoyaltyPercent(?Carbon $openedAt, Carbon $targetDate): float
    {
        if (!$openedAt) {
            return 0;
        }

        /**
         * Первые 2 месяца бесплатно
         */
        $royaltyStart = $openedAt
            ->copy()
            ->startOfMonth()
            ->addMonths(2);

        $targetMonth = $targetDate
            ->copy()
            ->startOfMonth();

        if ($targetMonth->lt($royaltyStart)) {
            return 0;
        }

        /**
         * Каждые 12 месяцев +0.25%
         */
        $months = $royaltyStart->diffInMonths($targetMonth);

        $steps = intdiv($months, 12) + 1;

        $percent = $steps * self::ROYALTY_STEP;

        return min($percent, self::MAX_ROYALTY_PERCENT);
    }

    public function calculateRoyaltyAmount(float $grossRevenue, float $royaltyPercent): float
    {
        return round($grossRevenue * ($royaltyPercent / 100), 2);
    }

    public function calculateVatAmount(float $royaltyAmount): float
    {
        return round($royaltyAmount * (self::VAT_PERCENT / 100), 2);
    }
}

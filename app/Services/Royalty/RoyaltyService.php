<?php

namespace App\Services\Royalty;

use App\Models\Partner\Partner;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class RoyaltyService
{
    private const VAT_PERCENT = 5;

    private const ROYALTY_STEP = 0.25;

    private const MIN_ROYALTY_PERCENT = 2.5;

    private const MAX_ROYALTY_PERCENT = 5;

    public function transform(
        Collection $partners,
        Carbon $monthInput,
    ): Collection {
        return $partners->map(
            function (Partner $partner) use ($monthInput) {

                $grossRevenue = round((float) $partner->income_total, 2);

                $openedAt = $partner->opened_at ? Carbon::parse($partner->opened_at) : null;
                $startAt = $partner->start_at ? Carbon::parse($partner->start_at) : null;

                $royaltyPercent = $this->calculateRoyaltyPercent(
                    $openedAt,
                    $startAt,
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
                    'start_at'         => $partner->start_at,
                ];
            }
        );
    }

    public function calculateRoyaltyPercent(?Carbon $openedAt, ?Carbon $startAt, Carbon $targetDate): float
    {
        if (!$openedAt) {
            return 0;
        }

        $targetMonth = $targetDate
            ->copy()
            ->startOfMonth();

        $openedMonth = $openedAt
            ->copy()
            ->startOfMonth();

        $freePeriodEnd = $openedMonth->copy()->addMonth(2);

        /**
         * Первые 2 месяца бесплатно
         */
        if ($targetMonth->lt($freePeriodEnd)) {
            return 0;
        }

        $percent = self::MIN_ROYALTY_PERCENT;

        /**
         * Каждые 12 месяцев +0.25%
         */
        if ($startAt) {
            $startMonth = $startAt->copy()->startOfMonth();

            if ($targetMonth->gt($startMonth)) {
                $start = $startMonth->diffInMonths($targetMonth);
                $yearsPassed = intdiv($start, 12);
                $percent += $yearsPassed * self::ROYALTY_STEP;
            }
        }

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

<?php

declare(strict_types=1);

namespace App\Services\Royalty;

use App\Models\Partner\Partner;
use App\Services\Royalty\Contracts\RoyaltyPercentCalculator;
use App\Services\Royalty\DTO\RoyaltyContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RoyaltyService
{
    private const VAT_PERCENT = 5;

    public function __construct(
        private readonly RoyaltyPercentCalculator $percentCalculator,
    ) {}

    public function getPartnersWithStatsQuery(
        string $startDate,
        string $endDate,
        bool $applyRoyaltyScope = true
    ): Builder {
        $query = Partner::query();

        if ($applyRoyaltyScope) {
            $query->withRoyalty();
        }

        return $query->select(
            'partners.id',
            'partners.name',
            'partners.yclients_id',
            'partners.start_at',
            'partners.opened_at',
        )
            ->leftJoin('yc_company_stats as stats', function ($join) use ($startDate, $endDate) {
                $join->on('stats.company_id', '=', 'partners.yclients_id')
                    ->where('stats.start_date', $startDate)
                    ->where('stats.end_date', $endDate);
            })
            ->selectRaw('COALESCE(SUM(stats.income_total), 0) as income_total')
            ->groupBy(
                'partners.id',
                'partners.name',
                'partners.yclients_id',
                'partners.start_at',
                'partners.opened_at'
            );
    }

    public function transform(
        Collection $partners,
        Carbon $monthInput,
    ): Collection {
        return $partners->map(
            function ($partner) use ($monthInput) {
                $grossRevenue = round((float) $partner->income_total, 2);

                $royaltyPercent = $this->percentCalculator->calculate(
                    new RoyaltyContext(
                        grossRevenue: $grossRevenue,
                        period: $monthInput,
                        openedAt: $partner->opened_at ? Carbon::parse($partner->opened_at) : null,
                        startAt: $partner->start_at ? Carbon::parse($partner->start_at) : null,
                    )
                );

                $royaltyAmount = $this->calculateRoyaltyAmount($grossRevenue, $royaltyPercent);
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
                    'start_at'         => $partner->start_at,
                ];
            }
        );
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

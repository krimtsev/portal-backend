<?php

declare(strict_types=1);

namespace App\Services\Royalty\Strategies;

use App\Services\Royalty\Contracts\RoyaltyPercentCalculator;
use App\Services\Royalty\DTO\RoyaltyContext;

/**
 * Процент зависит от выручки за месяц (soda):
 *  - выручка < 2 000 000 — 3%;
 *  - выручка >= 2 000 000 — 2.5%.
 */
final class RevenueTierRoyaltyStrategy implements RoyaltyPercentCalculator
{
    /**
     * Нижняя граница выручки (включительно) => процент.
     * Должно быть отсортировано по возрастанию ключа.
     *
     * @var array<int, float>
     */
    private const TIERS = [
        0         => 3.0,
        2_000_000 => 2.5,
    ];

    public function calculate(RoyaltyContext $context): float
    {
        $percent = self::TIERS[0];

        foreach (self::TIERS as $threshold => $tierPercent) {
            if ($context->grossRevenue >= $threshold) {
                $percent = $tierPercent;
            }
        }

        return $percent;
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Royalty\Strategies;

use App\Services\Royalty\Contracts\RoyaltyPercentCalculator;
use App\Services\Royalty\DTO\RoyaltyContext;

/**
 * Процент зависит от "стажа" точки (britva):
 *  - первые 2 месяца после opened_at — бесплатно;
 *  - далее 2.5%, каждые 12 месяцев с start_at +0.25%, максимум 5%.
 */
final class SeniorityRoyaltyStrategy implements RoyaltyPercentCalculator
{
    private const FREE_MONTHS = 2;

    private const STEP_PERIOD_MONTHS = 12;

    private const STEP = 0.25;

    private const MIN_PERCENT = 2.5;

    private const MAX_PERCENT = 5.0;

    public function calculate(RoyaltyContext $context): float
    {
        if ($context->openedAt === null) {
            return 0.0;
        }

        $targetMonth = $context->period->copy()->startOfMonth();
        $openedMonth = $context->openedAt->copy()->startOfMonth();

        if ($targetMonth->lt($openedMonth->copy()->addMonths(self::FREE_MONTHS))) {
            return 0.0;
        }

        $percent = self::MIN_PERCENT;

        if ($context->startAt !== null) {
            $startMonth = $context->startAt->copy()->startOfMonth();

            if ($targetMonth->gt($startMonth)) {
                $monthsPassed = (int) $startMonth->diffInMonths($targetMonth);
                $percent += intdiv($monthsPassed, self::STEP_PERIOD_MONTHS) * self::STEP;
            }
        }

        return min($percent, self::MAX_PERCENT);
    }
}

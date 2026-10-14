<?php

declare(strict_types=1);

namespace App\Services\Royalty\Contracts;

use App\Services\Royalty\DTO\RoyaltyContext;

interface RoyaltyPercentCalculator
{
    /**
     * Процент роялти (например, 2.5), без НДС.
     */
    public function calculate(RoyaltyContext $context): float;
}

<?php

declare(strict_types=1);

namespace App\Services\Royalty;

use App\Enums\Partner\PartnerCode;
use App\Services\Royalty\Contracts\RoyaltyPercentCalculator;
use App\Services\Royalty\Strategies\RevenueTierRoyaltyStrategy;
use App\Services\Royalty\Strategies\SeniorityRoyaltyStrategy;

/**
 * Единственное место, где партнер сопоставляется с формулой роялти.
 *
 * match без default: при добавлении нового case в PartnerCode
 * без обновления этого файла будет UnhandledMatchError (и ошибка в PHPStan).
 */
final readonly class RoyaltyStrategyResolver
{
    public function __construct(
        private SeniorityRoyaltyStrategy $seniority,
        private RevenueTierRoyaltyStrategy $revenueTier,
    ) {}

    public function resolve(PartnerCode $partner): RoyaltyPercentCalculator
    {
        return match ($partner) {
            PartnerCode::Britva,
            PartnerCode::Lapki => $this->seniority,
            PartnerCode::Soda  => $this->revenueTier,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Royalty\DTO;

use Carbon\Carbon;

/**
 * Входные данные для расчета процента роялти по одному партнеру за месяц.
 */
final readonly class RoyaltyContext
{
    public function __construct(
        public float $grossRevenue,
        public Carbon $period,
        public ?Carbon $openedAt,
        public ?Carbon $startAt,
    ) {}
}

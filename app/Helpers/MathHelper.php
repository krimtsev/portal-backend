<?php

declare(strict_types=1);

namespace App\Helpers;

final class MathHelper
{
    /**
     * Вычисляет процент изменения (прироста/падения) между текущим и прошлым значением.
     */
    public static function calculatePercent(float $current, float $previous): int
    {
        if ($previous > 0) {
            return (int) round((($current - $previous) / $previous) * 100);
        }

        if ($current > 0) {
            return 100;
        }

        return 0;
    }

    public static function calculateGrowth(float $current, float $previous): ?int
    {
        if ($current === 0 && $previous === 0) {
            return 0;
        }

        if ($current >= $previous) {
            if ($current === 0) {
                return 0;
            }

            return (int) round((1 - ($previous / $current)) * 100);
        }

        if ($previous === 0) {
            return 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}

<?php

namespace App\Helpers;

final class NumberHelper
{
    public static function money(float|int|string|null $value): string
    {

        return number_format(
            (float) $value,
            2,
            '.',
            ''
        );
    }

    public static function formatPrice(float|int|string|null $value): string
    {
        $floatValue = (float) $value;

        $truncated = floor($floatValue * 100) / 100;

        return number_format($truncated, 2, ',', "\u{00A0}");
    }
}

<?php

namespace App\Helpers;

class NumberHelper
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
}

<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

final class DateHelper
{
    public static function parseMonthWithoutShift(string $dateString): Carbon
    {
        return Carbon::parse($dateString)->startOfMonth()->shiftTimezone('UTC');
    }
}

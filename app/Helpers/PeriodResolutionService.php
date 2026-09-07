<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class PeriodResolutionService
{
    /**
     * Генерирует массив объектов Carbon на основе переданных параметров
     *
     * @return Carbon[]
     */
    public function resolveFromParams(
        ?string $date = null,
        ?string $month = null,
        ?string $timezone = 'Europe/Moscow',
    ): array
    {
        $dates = [];

        $yesterday = Carbon::now($timezone)->startOfDay()->subDay();
        $today = Carbon::now($timezone)->startOfDay();

        if ($month) {
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                throw new InvalidArgumentException('Месяц должен быть в формате YYYY-MM');
            }

            $startOfMonth = Carbon::parse($month)->startOfMonth();
            $endOfMonth = Carbon::parse($month)->endOfMonth();

            // Если месяц текущий, не идем дальше вчерашнего дня
            if ($endOfMonth->gt($yesterday)) {
                $endOfMonth = $yesterday;
            }

            for ($current = $startOfMonth->copy(); $current->lte($endOfMonth); $current->addDay()) {
                $dates[] = $current->copy();
            }

            return $dates;
        }

        if ($date) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                throw new InvalidArgumentException('Дата должна быть в формате YYYY-MM-DD');
            }

            $parsedDate = Carbon::parse($date, $timezone)->startOfDay();

            if ($parsedDate->gte($today)) {
                throw new InvalidArgumentException('Нельзя запустить команду за текущий или будущий день.');
            }

            return [$parsedDate];
        }

        return [$yesterday];
    }

    public function resolveMonthBounds(
        string $month,
        ?string $timezone = 'Europe/Moscow',
    ): array
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw new InvalidArgumentException('Месяц должен быть в формате YYYY-MM');
        }

        $start = Carbon::parse($month, $timezone)->startOfMonth();
        $end = Carbon::parse($month, $timezone)->endOfMonth();

        $localNow = Carbon::now($timezone)->startOfDay();

        if ($end->gte($localNow)) {
            throw new InvalidArgumentException('Нельзя запустить за незавершенный месяц.');
        }

        return [
            $start->toDateString(),
            $end->toDateString(),
        ];
    }
}

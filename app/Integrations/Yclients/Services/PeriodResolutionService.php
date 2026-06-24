<?php

namespace App\Integrations\Yclients\Services;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class PeriodResolutionService
{
    /**
     * Генерирует массив объектов Carbon на основе переданных параметров
     *
     * @return Carbon[]
     */
    public function resolveFromParams(?string $date = null, ?string $month = null): array
    {
        $dates = [];

        if ($month) {
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                throw new InvalidArgumentException('Месяц должен быть в формате YYYY-MM');
            }

            $startOfMonth = Carbon::parse($month)->startOfMonth();
            $endOfMonth = Carbon::parse($month)->endOfMonth();

            // Если месяц текущий, не идем дальше вчерашнего дня
            if ($endOfMonth->isFuture()) {
                $endOfMonth = now()->subDay();
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

            return [Carbon::parse($date)];
        }

        return [now()->subDay()];
    }
}

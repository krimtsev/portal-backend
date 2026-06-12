<?php

namespace App\Helpers;

use Generator;
use Illuminate\Support\Carbon;

class QueueThrottler
{
    /**
     * Разрезает и распределяет поток элементов, генерируя для каждого нарастающую Carbon-задержку.
     *
     * @param  iterable  $items  Исходная коллекция или массив (например, партнеры)
     * @param  int  $stepSeconds  Информационное окно/шаг задержки в секундах (минимум 1 секунда)
     */
    public static function chunkWithDelay(iterable $items, int $stepSeconds = 1): Generator
    {
        $currentIndex = 0;

        foreach ($items as $key => $item) {
            $delay = now()->addSeconds($currentIndex * $stepSeconds);

            // Возвращаем массив с самим элементом и его персональным тайм-слотом
            yield $key => [
                'item'  => $item,
                'delay' => $delay,
            ];

            $currentIndex++;
        }
    }
}

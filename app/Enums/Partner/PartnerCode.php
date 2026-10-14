<?php

declare(strict_types=1);

namespace App\Enums\Partner;

use App\Exceptions\Partner\UnknownPartnerException;

/**
 * Код партнера (филиала), под которым запущено приложение (PARTNER_NAME).
 *
 * Здесь живут только "флаги поведения" партнера. Выбор конкретной
 * формулы роялти находится в RoyaltyStrategyResolver.
 */
enum PartnerCode: string
{
    case Britva = 'britva';
    case Soda = 'soda';
    case Lapki = 'lapki';

    /**
     * Текущий партнер из config('partner.current').
     *
     * @throws UnknownPartnerException
     */
    public static function current(): self
    {
        $code = config('partner.current');

        return self::tryFrom((string) $code)
            ?? throw UnknownPartnerException::forCode(is_string($code) ? $code : null);
    }

    /**
     * Показывать ли в панели "income_total" (доход по партнерам с роялти).
     * Для soda пока не считаем.
     */
    public function reportsRoyaltyIncome(): bool
    {
        return match ($this) {
            self::Soda => false,
            self::Britva, self::Lapki => true,
        };
    }
}

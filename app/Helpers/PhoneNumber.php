<?php

namespace App\Helpers;

final class PhoneNumber
{
    /**
     * Форматирует 11-значный номер РФ/РК в вид +7 (XXX) XXX-XX-XX
     */
    public static function format(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 11 && in_array($digits[0], ['7', '8'], true)) {
            return preg_replace(
                '/^[78](\d{3})(\d{3})(\d{2})(\d{2})$/',
                '+7 ($1) $2-$3-$4',
                $digits
            );
        }

        return $phone;
    }

    /**
     * Нормализует номер к единому виду без форматирования (например: 79266948749)
     */
    public static function sanitize(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits[0] = '7';
        }

        return $digits;
    }
}

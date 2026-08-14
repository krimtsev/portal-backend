<?php

namespace App\Helpers;

class FileHelper
{
    public static function safeFileName(string $value): string
    {
        return preg_replace('/[\/\\\\:\*\?"<>|]/u', '', $value);
    }
}

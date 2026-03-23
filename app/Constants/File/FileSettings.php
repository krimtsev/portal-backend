<?php

namespace App\Constants\File;

class FileSettings
{
    public const IMAGE_MIMES = 'jpg,jpeg,png,webp,tif,ai';
    public const DOC_MIMES = 'pdf,doc,docx,xlsx,xls,txt,pptx';
    public const VIDEO_MIMES = 'mp4,mov,avi,wmv,qt';
    public const ARCHIVE_MIMES = 'zip';

    public static function allMimes(): string
    {
        return implode(',', [
            self::IMAGE_MIMES,
            self::DOC_MIMES,
            self::VIDEO_MIMES,
            self::ARCHIVE_MIMES,
        ]);
    }

    public static function getRules(int $maxSizeMb = 1): array
    {
        return [
            'file',
            "max:" . ($maxSizeMb * 1024),
            "mimes:" . self::allMimes(),
        ];
    }
}

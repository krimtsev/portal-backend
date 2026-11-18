<?php

use App\Models\Cloud\CloudFile;
use Illuminate\Support\Facades\Storage;

/**
 * php artisan tinker database/patches/cloud_files_size_checksum.php
 *
 * Восстанавливает size и checksum для записей cloud_files.
 * Файлы, отсутствующие на диске, пропускаются (size = 0, checksum = null).
 * updated_at у записей не изменяется.
 */
try {
    $disk = Storage::disk('cloud');
    $stats = [
        'updated' => 0,
        'missing' => 0,
        'errors'  => 0,
    ];

    CloudFile::query()
        ->orderBy('id')
        ->chunkById(50, function ($files) use ($disk, &$stats) {
            foreach ($files as $file) {
                try {
                    if (empty($file->path) || !$disk->exists($file->path)) {
                        $stats['missing']++;

                        continue;
                    }

                    $size = $disk->size($file->path);
                    $checksum = $disk->checksum($file->path, [
                        'checksum_algo' => 'sha256',
                    ]);

                    CloudFile::whereKey($file->id)->toBase()->update([
                        'size'     => $size,
                        'checksum' => $checksum ?: null,
                    ]);

                    $stats['updated']++;
                } catch (Throwable $e) {
                    $stats['errors']++;
                    echo "Ошибка на файле #{$file->id} ({$file->path}): {$e->getMessage()}\n";
                }
            }
        });

    echo sprintf(
        "Патч выполнен. Обновлено: %d, пропущено (нет файла): %d, ошибок: %d\n",
        $stats['updated'],
        $stats['missing'],
        $stats['errors']
    );
} catch (Throwable $e) {
    echo 'Ошибка: ' . $e->getMessage() . ' (Строка: ' . $e->getLine() . ')' . PHP_EOL;
}

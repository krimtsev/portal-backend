<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

final class ClearBroadcastsCommand extends Command
{
    /**
     * Имя и сигнатура консольной команды.
     */
    protected $signature = 'clear:broadcasts
                            {--days= : Количество дней, старше которых нужно удалять папки}';

    /**
     * Описание команды.
     */
    protected $description = 'Удаляет папки рассылок старше указанного количества дней';

    public function handle(): int
    {

        $subDays = (int) ($this->option('days') ?? 120);

        $disk = Storage::disk('broadcasts');

        $threshold = Carbon::now()->subDays($subDays)->getTimestamp();

        $deletedCount = 0;

        $directories = $disk->directories();

        foreach ($directories as $directory) {
            if ($disk->lastModified($directory) < $threshold) {
                $disk->deleteDirectory($directory);
                $deletedCount++;
            }
        }

        $this->info("Удалено папок рассылок старше {$subDays} дней: {$deletedCount}");

        return self::SUCCESS;
    }
}

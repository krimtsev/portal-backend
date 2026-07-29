<?php

namespace App\Services\Maintenance;

use Illuminate\Support\Facades\Storage;

class MaintenanceService
{
    private const FILE_PATH = 'maintenance.lock';

    public function isEnabled(): bool
    {
        return Storage::exists(self::FILE_PATH);
    }

    public function updateStatus(bool $enabled): bool
    {
        if ($enabled) {
            Storage::put(self::FILE_PATH, 'true');
        } else {
            Storage::delete(self::FILE_PATH);
        }

        return $enabled;
    }
}

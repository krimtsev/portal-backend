<?php

declare(strict_types=1);

namespace App\Services\Cloud;

use App\Models\Cloud\CloudFolder;

final class CloudFolderService
{
    public function wouldCreateCycle(CloudFolder $folder, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return false;
        }

        $current = CloudFolder::find($newParentId);
        $visited = [];

        while ($current !== null) {
            if ($current->id === $folder->id) {
                return true;
            }

            if (isset($visited[$current->id])) {
                return true;
            }
            $visited[$current->id] = true;

            $current = $current->parent;
        }

        return false;
    }
}

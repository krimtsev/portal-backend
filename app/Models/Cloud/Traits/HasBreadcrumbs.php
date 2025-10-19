<?php

namespace App\Models\Cloud\Traits;

use App\Models\Cloud\CloudFolder;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasBreadcrumbs
{
    protected function breadcrumb(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->generateBreadcrumb()
        );
    }

    protected function generateBreadcrumb(): array
    {
        $breadcrumbs = [];
        $current = $this;

        while ($current) {
            $breadcrumbs[] = [
                'label' => $current->name,
                'path'  => $current->slug,
            ];
            $current = $current->parent;
        }

        return array_reverse($breadcrumbs);
    }

    protected function buildFullSlug(CloudFolder $folder): string
    {
        $slugs = [];
        $current = $folder;

        while ($current) {
            array_unshift($slugs, $current->slug);
            $current = $current->parent;
        }

        return implode('/', $slugs);
    }
}

<?php

namespace App\Repositories\Cloud;

use App\Models\Cloud\CloudFile;
use App\Models\Cloud\CloudFolder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CloudRepository
{
    /**
     * Получить папку со вложенными подпапками по slug
     */
    public function getFolderBySlug(?string $slug): ?CloudFolder
    {
        if (!$slug) {
            return null;
        }

        return CloudFolder::with('childrenRecursive')
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Получить список папок для текущего уровня
     */
    public function getFolders(?CloudFolder $folder): array
    {
        $query = $folder
            ? $folder->children()
            : CloudFolder::whereNull('category_id');

        return $query->select('id', 'name', 'slug', 'folder', 'category_id')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Получить список файлов в конкретной папке
     */
    public function getFiles(?CloudFolder $folder): array
    {
        if (!$folder) {
            return [];
        }

        return CloudFile::select('id', 'title', 'ext', 'name', 'cloud_folders_id')
            ->where('cloud_folders_id', $folder->id)
            ->orderBy('title')
            ->get()
            ->toArray();
    }

    /**
     * Поиск файлов (с учетом вложенности папок, если папка указана)
     */
    public function searchFiles(string $search, ?CloudFolder $folder): array
    {
        $query = CloudFile::select('id', 'title', 'ext', 'name', 'cloud_folders_id');

        if ($folder) {
            $folderIds = $folder->getAllChildrenIds();
            $query->whereIn('cloud_folders_id', $folderIds);
        }

        return $query->where('title', 'like', "%{$search}%")
            ->orderBy('title')
            ->get()
            ->toArray();
    }

    /**
     * Получение всех данных для построения дерева через CTE
     */
    public function getFullTreeData(): Collection
    {
        $sql = "
            WITH RECURSIVE folder_tree AS (
                SELECT id, name, slug, category_id
                FROM cloud_folders
                WHERE category_id IS NULL
                UNION ALL
                SELECT f.id, f.name, f.slug, f.category_id
                FROM cloud_folders f
                INNER JOIN folder_tree ft ON f.category_id = ft.id
            ) SELECT * FROM folder_tree
        ";

        $allFolders = CloudFolder::hydrate(DB::select($sql));

        $allFolders->load(['files' => function($query) {
            $query->select('id', 'title', 'ext', 'downloads', 'cloud_folders_id');
        }]);

        return $allFolders;
    }

    /**
     * Рекурсивная сборка дерева (логика трансформации структуры)
     */
    public function assembleTree($folderMap, $parentId, $path = [])
    {
        $level = $folderMap->get($parentId, collect());

        foreach ($level as $folder) {
            $currentPath = array_merge($path, [$folder->slug]);
            $folder->setAttribute('path_array', $currentPath);

            $children = $this->assembleTree($folderMap, $folder->id, $currentPath);

            $folder->setRelation('children', $children);
            $folder->setRelation('withFilesRecursive', $children);
        }

        return $level->sortBy('name');
    }

    /**
     * Получить древовидный список папок для выбора (options)
     * Если указан exclude_id, исключается сама папка и все её потомки
     */
    public function getOptionsTree(?int $excludeId = null): array
    {
        if ($excludeId) {
            $sql = "
                WITH RECURSIVE descendants AS (
                    SELECT id FROM cloud_folders WHERE category_id = :exclude_id
                    UNION ALL
                    SELECT f.id FROM cloud_folders f
                    INNER JOIN descendants d ON f.category_id = d.id
                )
                SELECT id, name, category_id
                FROM cloud_folders
                WHERE id NOT IN (SELECT id FROM descendants)
                ORDER BY name
            ";

            $results = DB::select($sql, ['exclude_id' => $excludeId]);
            $folders = collect($results);
        } else {
            $folders = CloudFolder::select('id', 'name', 'category_id')
                ->orderBy('name')
                ->get();
        }

        $folderMap = $folders->groupBy('category_id');

        return $this->formatOptionsTree($folderMap);
    }

    private function formatOptionsTree($folderMap, $parentId = null): array
    {
        $branch = [];
        $folders = $folderMap->get($parentId, []);

        foreach ($folders as $folder) {
            $children = $this->formatOptionsTree($folderMap, $folder->id);

            $node = [
                'id'   => $folder->id,
                'name' => $folder->name,
            ];

            if (!empty($children)) {
                $node['children'] = $children;
            }

            $branch[] = $node;
        }

        return $branch;
    }

    /**
     * Получить плоский список папок для выбора (options)
     * Если указан exclude_id, исключаются только её дочерние элементы
     */
    public function getFlatOptions(?int $excludeId = null): Collection
    {
        if ($excludeId) {
            $sql = "
            WITH RECURSIVE descendants AS (
                SELECT id FROM cloud_folders WHERE id = :exclude_id
                UNION ALL
                SELECT f.id FROM cloud_folders f
                INNER JOIN descendants d ON f.category_id = d.id
            )
            SELECT id, name
            FROM cloud_folders
            WHERE id NOT IN (SELECT id FROM descendants)
            ORDER BY name
        ";

            return collect(DB::select($sql, ['exclude_id' => $excludeId]));
        }

        return CloudFolder::select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}

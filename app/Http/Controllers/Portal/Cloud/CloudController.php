<?php

namespace App\Http\Controllers\Portal\Cloud;


use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use App\Models\Cloud\CloudFile;
use App\Models\Cloud\CloudFolder;
use Illuminate\Http\Request;
use App\Helpers\Cache\Cache;

class CloudController extends Controller
{
    private const CACHE_TTL = 3600; // Время жизни кэша в секундах (1 час)
    private const CACHE_TAG = "cloud";

    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $slug = $request->query('slug');
        $search = $request->query('search');

        $cacheKey = $this->getCacheKey($slug, $search);

        $data = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($slug, $search) {
            $files = [];
            $folders = [];
            $breadcrumbs = [];

            $folder = $this->getFolder($slug);

            if ($slug && !$folder) {
                return $this->emptyData();
            }

            if ($folder) {
                $breadcrumbs = $folder->breadcrumb;
            }

            if (!empty($search)) {
                $folders = [];
                $files = $this->searchFiles($search, $folder);
            } else {
                $folders = $this->getFolders($folder);
                $files = $this->getFiles($folder);
            }

            return [
                'files' => $files,
                'folders' => $folders,
                'breadcrumbs' => $breadcrumbs,
            ];
        }, self::CACHE_TAG);

        return JsonResponse::Send($data);
    }

    private function getCacheKey(?string $slug, ?string $search): string
    {
        $prefix = $slug ? "cloud_{$slug}_" : "cloud_root_";
        return $prefix . md5((string)$search);
    }

    private function getFolder(?string $slug): ?CloudFolder
    {
        if (!$slug) {
            return null;
        }

        return CloudFolder::with('allChildrenRecursive')
            ->where('slug', $slug)
            ->first();
    }

    private function getFolders(?CloudFolder $folder)
    {
        if (!$folder) {
            return CloudFolder::select('id', 'name', 'slug', 'folder', 'category_id')
                ->whereNull('category_id')
                ->orderBy('name')
                ->get()
                ->toArray();
        }

        return $folder->children()
            ->select('id', 'name', 'slug', 'folder', 'category_id')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    private function getFiles(?CloudFolder $folder)
    {
        if (!$folder) {
            return [];
        }

        return CloudFile::select('id', 'title', 'ext', 'name')
            ->where('cloud_folders_id', $folder->id)
            ->orderBy('title')
            ->get()
            ->toArray();
    }

    private function searchFiles(string $search, ?CloudFolder $folder)
    {
        $query = CloudFile::select('id', 'title', 'ext', 'name');

        if ($folder) {
            $folderIds = $folder->getAllChildrenIds();
            $query->whereIn('cloud_folders_id', $folderIds);
        }

        return $query->where('title', 'like', "%$search%")
            ->orderBy('title')
            ->get()
            ->toArray();
    }

    private function emptyData(): array
    {
        return [
            'files'       => [],
            'folders'     => [],
            'breadcrumbs' => [],
        ];
    }
}

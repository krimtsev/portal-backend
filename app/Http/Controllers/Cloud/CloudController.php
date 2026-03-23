<?php

namespace App\Http\Controllers\Cloud;


use App\Helpers\Cache\Cache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cloud\CloudCreateRequest;
use App\Http\Requests\Cloud\CloudUpdateRequest;
use App\Http\Resources\Cloud\CloudOptionTreeResource;
use App\Http\Resources\Cloud\CloudResource;
use App\Http\Resources\Cloud\CloudTreeResource;
use App\Models\Cloud\CloudFile;
use App\Models\Cloud\CloudFolder;
use App\Repositories\Cloud\CloudRepository;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CloudController extends Controller
{
    public function __construct(
        private CloudRepository $repository
    ) {}

    private function getCacheKey(?string $slug, ?string $search): string
    {
        $prefix = $slug ? "cloud_{$slug}" : "cloud_root";
        return $search ? "{$prefix}_" . md5($search) : $prefix;
    }

    private function emptyData(): array
    {
        return [
            'files'       => [],
            'folders'     => [],
            'breadcrumbs' => [],
        ];
    }

    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $slug = $request->query('slug');
        $search = $request->query('search');
        $cacheKey = $this->getCacheKey($slug, $search);

        $data = Cache::remember($cacheKey, CloudFolder::CACHE_TTL, function () use ($slug, $search) {
            $folder = $this->repository->getFolderBySlug($slug);

            if ($slug && !$folder) {
                return $this->emptyData();
            }

            $files = !empty($search)
                ? $this->repository->searchFiles($search, $folder)
                : $this->repository->getFiles($folder);

            $folders = !empty($search)
                ? []
                : $this->repository->getFolders($folder);

            $breadcrumbs = $folder
                ? $folder->breadcrumb
                : [];

            return [
                'files'       => $files,
                'folders'     => $folders,
                'breadcrumbs' => $breadcrumbs,
            ];
        }, CloudFolder::CACHE_TAG);

        return JsonResponse::Send($data);
    }

    /**
     * Дерево папок и файлов
     * @return \Illuminate\Http\JsonResponse
     */
    public function tree(): \Illuminate\Http\JsonResponse
    {
        $cacheKey = $this->getCacheKey("tree", null);

        $rootNodes = Cache::remember($cacheKey, CloudFolder::CACHE_TTL, function () {
            $allFolders = $this->repository->getFullTreeData();
            $folderMap = $allFolders->groupBy('category_id');

            return $this->repository->assembleTree($folderMap, null);
        }, CloudFolder::CACHE_TAG);

        return JsonResponse::Send([
            'list' => CloudTreeResource::collection($rootNodes)
        ]);
    }

    /**
     * Получить плоский список папок
     */
    public function options(Request $request): \Illuminate\Http\JsonResponse
    {
        $excludeId = $request->query('exclude_id');

        // Получаем плоский список без лишних полей и вложенностей
        $list = $this->repository->getFlatOptions(
            $excludeId ? (int) $excludeId : null
        );

        return JsonResponse::Send([
            'list' => $list
        ]);
    }

    /**
     * Получить список папок в виде дерева
     */
    public function optionsTree(Request $request): \Illuminate\Http\JsonResponse
    {
        $excludeId = $request->query('exclude_id');

        $tree = $this->repository->getOptionsTree(
            $excludeId ? (int) $excludeId : null
        );

        return JsonResponse::Send([
            'list' => CloudOptionTreeResource::collection($tree)
        ]);
    }

    public function get(Request $request, CloudFolder $folder): \Illuminate\Http\JsonResponse
    {
        $folder->load('files');

        return JsonResponse::Send([
            'data' => new CloudResource($folder),
        ]);
    }

    public function create(CloudCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $folderUuid = (string) Str::orderedUuid();

        $data['folder'] = $folderUuid;

        if (!Storage::disk('cloud')->makeDirectory($folderUuid)) {
            return JsonResponse::Forbidden('Could not create directory');
        }

        $folder = CloudFolder::create($data);

        Cache::flush(CloudFolder::CACHE_TAG);

        return JsonResponse::Send([
            'data' => new CloudResource($folder),
        ], 201);
    }

    public function update(CloudUpdateRequest $request, CloudFolder $folder): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $folder->update($data);

        Cache::flush(CloudFolder::CACHE_TAG);

        return JsonResponse::Updated();
    }

    public function remove(Request $request, CloudFolder $folder): \Illuminate\Http\JsonResponse
    {
        $hasSubfolders = CloudFolder::where('category_id', $folder->id)->exists();

        if ($hasSubfolders) {
            return JsonResponse::Forbidden('The folder cannot be deleted because it contains subdirectories.');
        }

        $folderId = $folder->id;
        $folderPath = $folder->folder;

        CloudFile::where('cloud_folders_id', $folderId)->delete();

        if (Storage::disk('cloud')->exists($folderPath)) {
            Storage::disk('cloud')->deleteDirectory($folderPath);
        }

        $folder->forceDelete();

        Cache::flush(CloudFolder::CACHE_TAG);

        return JsonResponse::Removed();
    }
}

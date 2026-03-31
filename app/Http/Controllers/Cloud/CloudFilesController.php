<?php

namespace App\Http\Controllers\Cloud;

use App\Helpers\Cache\Cache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cloud\CloudFileUpdateRequest;
use App\Http\Requests\Cloud\CloudFileUploadRequest;
use App\Http\Resources\Cloud\CloudFileResource;
use App\Models\Cloud\CloudFile;
use App\Models\Cloud\CloudFolder;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CloudFilesController extends Controller
{
    public function download(Request $request, CloudFolder $folder, string $fileName): \Illuminate\Http\JsonResponse|StreamedResponse
    {

        $file = CloudFile::where('name', $fileName)
            ->where('cloud_folders_id', $folder->id)
            ->first();

        if (!$file) {
            return JsonResponse::FileNotFound();
        }

        $path = $file->path;

        if (!Storage::disk('cloud')->exists($path)) {
            return JsonResponse::FileNotFound();
        }

        $file->increment('downloads');

        $downloadName = sprintf('%s.%s', $file->title, $file->ext);

        return new StreamedResponse(function () use ($path) {
            echo Storage::disk('cloud')->get($path);
        }, 200, [
            'Content-Type'        => $file->type  ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
        ]);
    }

    public function list(CloudFolder $folder): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => CloudFileResource::collection($folder->files)
        ]);
    }

    private function ensureFileBelongsToFolder(CloudFolder $folder, CloudFile $file): ?\Illuminate\Http\JsonResponse
    {
        if ($file->cloud_folders_id !== $folder->id) {
            return JsonResponse::Forbidden(trans('cloud.file_not_in_folder'));
        }

        return null;
    }

    private static function addFile($storagePath, $cloud_folders_id, $file)
    {
        $path = Storage::disk('cloud')->put($storagePath, $file);
        $origin = $file->getClientOriginalName();

        return CloudFile::create([
            'title'            => pathinfo($origin, PATHINFO_FILENAME),
            'name'             => basename($path),
            'origin'           => $origin,
            'path'             => $path,
            'type'             => $file->getMimeType(),
            'ext'              => pathinfo($origin, PATHINFO_EXTENSION),
            'cloud_folders_id' => $cloud_folders_id,
            'downloads'        => 0
        ]);
    }

    public function update(CloudFileUpdateRequest $request, CloudFolder $folder, CloudFile $file): \Illuminate\Http\JsonResponse
    {
        if ($error = $this->ensureFileBelongsToFolder($folder, $file)) {
            return $error;
        }

        $data = $request->validated();

        $file->update($data);

        Cache::flush(CloudFolder::CACHE_TAG);

        return JsonResponse::Updated();
    }

    public function upload(CloudFileUploadRequest $request, CloudFolder $folder): \Illuminate\Http\JsonResponse
    {
        $request->validated();

        $path = $folder->folder;
        $createdFiles = [];

        if (!$path || !Storage::disk('cloud')->exists($path)) {
            return JsonResponse::Forbidden(trans('cloud.directory_not_found'));
        }

        foreach ($request->file('files') as $file) {
            $createdFiles[] = self::addFile($path, $folder->id, $file);
        }

        Cache::flush(CloudFolder::CACHE_TAG);

        return JsonResponse::Send([
            'data' => CloudFileResource::collection($createdFiles)
        ]);
    }

    public function remove(Request $request, CloudFolder $folder, CloudFile $file): \Illuminate\Http\JsonResponse
    {
        if ($error = $this->ensureFileBelongsToFolder($folder, $file)) {
            return $error;
        }

        if (Storage::disk('cloud')->exists($file->path)) {
            Storage::disk('cloud')->delete($file->path);
        }

        $file->delete();

        Cache::flush(CloudFolder::CACHE_TAG);

        return JsonResponse::Removed();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cloud;

use App\Helpers\Cache;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cloud\CloudFileUpdateRequest;
use App\Http\Requests\Cloud\CloudFileUploadRequest;
use App\Http\Resources\Cloud\CloudFileResource;
use App\Models\Cloud\CloudFile;
use App\Models\Cloud\CloudFolder;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CloudFilesController extends Controller
{
    public function download(Request $request, CloudFolder $folder, string $fileName): \Illuminate\Http\JsonResponse|StreamedResponse
    {
        $file = CloudFile::where('name', $fileName)
            ->where('cloud_folders_id', $folder->id)
            ->first();

        if (!$file) {
            return JsonResponse::FileNotFound();
        }

        $disk = Storage::disk('cloud');

        if (!$disk->exists($file->path)) {
            return JsonResponse::FileNotFound();
        }

        if ($request->isMethod('GET')) {
            $file->increment('downloads');
        }

        $downloadName = sprintf('%s.%s', $file->title, $file->ext);
        $size = $disk->size($file->path);
        $mime = $file->type ?? 'application/octet-stream';
        $asciiFallback = preg_replace('/[^\x20-\x7E]/', '_', $downloadName);
        $disposition = sprintf(
            "attachment; filename=\"%s\"; filename*=UTF-8''%s",
            addcslashes($asciiFallback, '"\\'),
            rawurlencode($downloadName)
        );

        return response()->stream(function () use ($disk, $file) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $stream = $disk->readStream($file->path);
            if ($stream === false) {
                return;
            }

            while (!feof($stream)) {
                if (connection_aborted()) {
                    break;
                }
                echo fread($stream, 8192);
                flush();
            }

            fclose($stream);
        }, 200, [
            'Content-Type'           => $mime,
            'Content-Length'         => (string) $size,
            'Content-Disposition'    => $disposition,
            'Cache-Control'          => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function list(CloudFolder $folder): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => CloudFileResource::collection($folder->files),
        ]);
    }

    private function ensureFileBelongsToFolder(CloudFolder $folder, CloudFile $file): ?\Illuminate\Http\JsonResponse
    {
        if ($file->cloud_folders_id !== $folder->id) {
            return JsonResponse::Forbidden(trans('cloud.file_not_in_folder'));
        }

        return null;
    }

    private static function addFile(
        string $storagePath,
        int $cloud_folders_id,
        UploadedFile $file
    ) {
        $originalName = $file->getClientOriginalName();

        $path = Storage::disk('cloud')->putFile($storagePath, $file);

        $rawFilename = pathinfo($originalName, PATHINFO_FILENAME);
        $title = Str::limit(strip_tags($rawFilename), 150, '');

        $ext = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $size = $file->getSize();
        $checksum = Storage::disk('cloud')->checksum($path, [
            'checksum_algo' => 'sha256',
        ]);

        return CloudFile::create([
            'title'            => $title,
            'name'             => basename($path),
            'origin'           => $originalName,
            'path'             => $path,
            'type'             => $file->getMimeType(),
            'ext'              => Str::lower($ext),
            'cloud_folders_id' => $cloud_folders_id,
            'size'             => $size,
            'checksum'         => $checksum ?: null,
            'user_id'          => auth()->id(),
            'downloads'        => 0,
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
            'data' => CloudFileResource::collection($createdFiles),
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

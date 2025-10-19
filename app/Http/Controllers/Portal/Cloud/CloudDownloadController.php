<?php

namespace App\Http\Controllers\Portal\Cloud;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use App\Models\Cloud\CloudFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CloudDownloadController extends Controller
{
    public function download(Request $request): \Illuminate\Http\JsonResponse|StreamedResponse
    {
        $name = $request->query('name');

        if (!$name) {
            return JsonResponse::FileNotFound();
        }

        $file = CloudFile::select('path', 'name', 'title', 'ext', 'type')
            ->where('name', $request->name)
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
}

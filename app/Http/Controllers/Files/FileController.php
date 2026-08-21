<?php

namespace App\Http\Controllers\Files;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Скачать защищённый файл (с фолбэком на default)
     */
    public function download(string $category, string $fileName): StreamedResponse
    {
        return $this->serveFile($category, $fileName, isDownload: true);
    }

    /**
     * Отобразить защищённый файл inline (с фолбэком на default)
     */
    public function render(string $category, string $fileName): StreamedResponse
    {
        return $this->serveFile($category, $fileName, isDownload: false);
    }

    private function serveFile(string $category, string $fileName, bool $isDownload): StreamedResponse
    {
        $partner = config('partner.current', 'default');
        $cleanCategory = basename($category);
        $cleanFileName = basename($fileName);

        $disk = Storage::disk('partners_private');

        $partnerPath = "{$partner}/{$cleanCategory}/{$cleanFileName}";
        $defaultPath = "default/{$cleanCategory}/{$cleanFileName}";

        $targetPath = match (true) {
            $disk->exists($partnerPath) => $partnerPath,
            $disk->exists($defaultPath) => $defaultPath,
            default => null,
        };

        if (!$targetPath) {
            abort(404, 'Файл не найден');
        }

        return $isDownload
            ? $disk->download($targetPath, $cleanFileName)
            : $disk->response($targetPath);
    }
}

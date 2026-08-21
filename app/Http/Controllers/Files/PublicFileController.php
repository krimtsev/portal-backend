<?php

namespace App\Http\Controllers\Files;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicFileController extends Controller
{
    public function show(string $category, string $fileName): BinaryFileResponse
    {
        $partner = config('partner.current', 'default');
        $cleanCategory = basename($category);
        $cleanFileName = basename($fileName);

        $disk = Storage::disk('partners_public');

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

        return response()->file($disk->path($targetPath));
    }
}

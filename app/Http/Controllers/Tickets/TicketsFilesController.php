<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketFile;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TicketsFilesController extends Controller
{
    /**
     * Скачать файл тикета
     */
    public function download(Request $request, string $ticket, string $fileName): \Illuminate\Http\JsonResponse|StreamedResponse
    {
        $file = TicketFile::select('path', 'name', 'title', 'ext', 'type')
            ->where('name', $fileName)
            ->first();

        if (!$file) {
            return JsonResponse::FileNotFound();
        }

        $path = $file->path;

        if (!Storage::disk('tickets')->exists($path)) {
            return JsonResponse::FileNotFound();
        }

        $downloadName = sprintf('%s.%s', $file->title, $file->ext);

        return Storage::disk('tickets')->download($file->path, $downloadName, [
            'Content-Type'   => $file->type ?? 'application/octet-stream',
            'Content-Length' => Storage::disk('tickets')->size($file->path),
        ]);
    }

    /**
     * Загрузить файл
     */
    public static function add(int $ticketId, int $ticketMessageId, UploadedFile $file): void
    {
        $path = Storage::disk('tickets')->putFile($ticketId, $file);
        $originalName = $file->getClientOriginalName();

        $rawFilename = pathinfo($originalName, PATHINFO_FILENAME);
        $title = Str::limit(strip_tags($rawFilename), 150, '');

        $ext = $file->guessExtension() ?? $file->getClientOriginalExtension();

        TicketFile::create([
            'title'             => $title,
            'name'              => basename($path),
            'origin'            => $originalName,
            'path'              => $path,
            'type'              => $file->getMimeType(),
            'ext'               => Str::lower($ext),
            'ticket_message_id' => $ticketMessageId,
        ]);
    }
}

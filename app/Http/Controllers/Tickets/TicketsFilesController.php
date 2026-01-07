<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Http\Responses\JsonResponse;
use App\Models\Ticket\TicketFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketsFilesController extends Controller {

    /**
     * Скачать файл тикета
     */
    public function download(string $ticket, string $name): \Illuminate\Http\JsonResponse|StreamedResponse
    {

        if (!$name) {
            return JsonResponse::FileNotFound();
        }

        $file = TicketFile::select('path', 'name', 'title', 'ext', 'type')
            ->where('name', $name)
            ->first();

        if (!$file) {
            return JsonResponse::FileNotFound();
        }

        $path = $file->path;

        if (!Storage::disk('tickets')->exists($path)) {
            return JsonResponse::FileNotFound();
        }

        $downloadName = sprintf('%s.%s', $file->title, $file->ext);

        return new StreamedResponse(function () use ($path) {
            echo Storage::disk('tickets')->get($path);
        }, 200, [
            'Content-Type'        => $file->type  ?? 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
        ]);
    }

    /**
     * Загрузить файл
     */
    public static function add(int $ticketId, int $ticketMessageId, UploadedFile $file): void
    {
        $path = Storage::disk('tickets')->putFile($ticketId, $file);
        $origin = $file->getClientOriginalName();

        TicketFile::create([
            "title"             => pathinfo($origin, PATHINFO_FILENAME),
            "name"              => basename($path),
            "origin"            => $file->getClientOriginalName(),
            "path"              => $path,
            "type"              => $file->getMimeType(),
            "ext"               => $file->getClientOriginalExtension(),
            "ticket_message_id" => $ticketMessageId,
        ]);
    }
}

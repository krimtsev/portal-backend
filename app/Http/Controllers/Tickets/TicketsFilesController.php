<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TicketsFilesController extends Controller {
    public function download(Request $request)
    {

    }

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

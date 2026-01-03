<?php

namespace App\Http\Controllers\Tickets;

use App\Enums\Ticket\TicketState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\TicketsCreateRequest;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket\TicketMessage;
use Illuminate\Http\Request;
use App\Http\Responses\JsonResponse;
use App\Helpers\Pagination\Pagination;
use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Ticket::with([
            'category:id,title',
            'partner:id,name',
            'user:id,name'
        ])->select(
            'id',
            'title',
            'category_id',
            'partner_id',
            'user_id',
            'state',
        );

        $result = Pagination::paginate(
            $query,
            $request,
            ['title'],
            ['id'],
            ['category_id', 'partner_id'],
        );

        $result['list'] = TicketResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function create(TicketsCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $userId = Auth::id();

        $ticketPayload = [
            'title'       => $data['title'],
            'attributes'  => $data['attributes'],
            'category_id' => $data['category_id'],
            'partner_id'  => $data['partner_id'],
            'user_id'     => $userId,
            'state'       => TicketState::New,
        ];

        $ticket = Ticket::create($ticketPayload);

        $ticketMessagePayload = [
            'ticket_id' => $ticket->id,
            'user_id'   => $userId,
            'text'      => $data['message'],
        ];

        $ticketMessage = TicketMessage::create($ticketMessagePayload);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                TicketsFilesController::add($ticket->id, $ticketMessage->id, $file);
            }
        }

        return JsonResponse::Created();
    }

    public function get(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $result = Ticket::with([
            'category:id,title',
            'partner:id,name',
            'user:id,name'
        ])->select(
            'id',
            'title',
            'category_id',
            'partner_id',
            'user_id',
            'state',
        )->findOrFail($id);

        return JsonResponse::Send([
            'data' => (new TicketResource($result))
        ]);
    }

    public function update(Request $request)
    {
        return $request;
    }
}

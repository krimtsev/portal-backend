<?php

namespace App\Http\Controllers\Tickets;

use App\Enums\Ticket\TicketState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\TicketsCreateRequest;
use App\Http\Requests\Ticket\TicketsUpdateRequest;
use App\Http\Resources\Ticket\TicketListResource;
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

        $result['list'] = TicketListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $ticket = Ticket::with([
            'category:id,title',
            'partner:id,name',
            'user:id,name,login',
            'messages.user:id,name,login',
            'messages.files',
            'events.user:id,name,login',
        ])->findOrFail($id);

        return JsonResponse::Send([
            'data' => new TicketResource($ticket),
        ]);
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

    private function canEdit(Ticket $ticket): bool
    {
        return !in_array($ticket->state, [
            TicketState::Success->value,
            TicketState::Closed->value,
            TicketState::Cancel->value,
        ]);
    }

    public function update(Request $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->canEdit($ticket)) {
            return JsonResponse::Forbidden('This ticket cannot be edited.');
        }

        $updatableFields = ['title', 'state', 'category_id', 'partner_id'];
        $changesRequest = new Request();

        foreach ($updatableFields as $field) {
            if ($request->has($field) && $ticket->$field !== $request->input($field)) {
                $changesRequest->merge([$field => $request->input($field)]);
                $ticket->$field = $request->input($field);
            }
        }

        // Фиксируем изменения в событиях
        $eventsController = new TicketsEventsController();
        $eventsController->create($ticket, $changesRequest);

        $ticket->save();

        return JsonResponse::Send([
            'ticket_id' => $ticket->id,
            'message'   => 'Ticket updated successfully.',
        ]);
    }

    public function updateMessage(TicketsUpdateRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->canEdit($ticket)) {
            return JsonResponse::Forbidden('This ticket cannot be edited.');
        }

        $data = $request->validated();

        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'text'      => $data['message'],
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                TicketsFilesController::add($ticket->id, $ticketMessage->id, $file);
            }
        }

        $ticketsController = new TicketsController();
        return $ticketsController->get(new Request(), $ticket->id);
    }

    public function remove(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->canEdit($ticket)) {
            return JsonResponse::Forbidden('This ticket cannot be edited or removed.');
        }

        $request = new Request([
            'state' => TicketState::Closed->value
        ]);

        $eventsController = new TicketsEventsController();
        $eventsController->create($ticket, $request);

        $ticket->state = $request->input("state");
        $ticket->save();

        return JsonResponse::Send([]);
    }
}

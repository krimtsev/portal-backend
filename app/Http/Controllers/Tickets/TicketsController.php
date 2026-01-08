<?php

namespace App\Http\Controllers\Tickets;

use App\Enums\Ticket\TicketState;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\TicketsCreateRequest;
use App\Http\Requests\Ticket\TicketsUpdateMessageRequest;
use App\Http\Requests\Ticket\TicketsUpdateRequest;
use App\Http\Resources\Ticket\TicketListResource;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Partner\Partner;
use App\Models\Ticket\TicketMessage;
use Illuminate\Http\Request;
use App\Http\Responses\JsonResponse;
use App\Helpers\Pagination\Pagination;
use App\Models\Ticket\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TicketsController extends Controller
{
    private function paginatedList(Builder $query, Request $request): \Illuminate\Http\JsonResponse
    {
        $query->with([
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
            'created_at',
        );

        $result = Pagination::paginate(
            $query,
            $request,
            ['title'],
            ['id'],
            ['category_id', 'partner_id', 'state'],
        );

        $result['list'] = TicketListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    /**
     * Получить список с проверкой доступных пользователю партнеров
     */
    public function restrictedList(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (!$user->partner_id) {
            return JsonResponse::Send([
                'list' => [],
            ]);
        }

        $partner = Partner::with('group.partners')->findOrFail($user->partner_id);

        $accessiblePartnerIds = $partner->group
            ? $partner->group->partners->pluck('id')
            : collect([$partner->id]);

        return $this->paginatedList(
            Ticket::whereIn('partner_id', $accessiblePartnerIds),
            $request
        );
    }

    /**
     * Получить список
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->paginatedList(
            Ticket::query(),
            $request
        );
    }

    /**
     * Получить заявление по id
     */
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

    /**
     * Создать заявление
     */
    public function create(TicketsCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function() use ($data, $request) {
            $userId = Auth::id();

            $ticketPayload = [
                'title' => $data['title'],
                'attributes' => $data['attributes'] ?? null,
                'type' => $data['type'],
                'category_id' => $data['category_id'],
                'partner_id' => $data['partner_id'],
                'user_id' => $userId,
                'state' => TicketState::New,
            ];

            $ticket = Ticket::create($ticketPayload);

            $ticketMessagePayload = [
                'ticket_id' => $ticket->id,
                'user_id' => $userId,
                'text' => $data['message'],
            ];

            $ticketMessage = TicketMessage::create($ticketMessagePayload);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    TicketsFilesController::add($ticket->id, $ticketMessage->id, $file);
                }
            }
        });

        return JsonResponse::Created();
    }

    /**
     * Обновить заявление
     * Выполняется только для активного статуса [new, in_progress, waiting]
     */
    public function update(TicketsUpdateRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->canEdit($ticket)) {
            return JsonResponse::Forbidden('This ticket cannot be edited.');
        }

        $data = $request->validated();

        DB::transaction(function() use ($ticket, $data, $request) {
            $original = clone $ticket;

            $ticket->update([
                'title'       => $data['title'],
                'category_id' => $data['category_id'],
                'partner_id'  => $data['partner_id'],
                'state'       => $data['state'],
            ]);

            if (!empty($data['message']) || $request->hasFile('files')) {
                $userId = Auth::id();

                $ticketMessagePayload = [
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId,
                    'text' => $data['message'],
                ];

                $ticketMessage = TicketMessage::create($ticketMessagePayload);

                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        TicketsFilesController::add($ticket->id, $ticketMessage->id, $file);
                    }
                }
            }

            $eventsController = new TicketsEventsController();
            $eventsController->create($original, $ticket);
        });

        $ticketsController = new TicketsController();
        return $ticketsController->get(new Request(), $ticket->id);
    }

    /**
     * Обновление сообщения
     * Выполняется пользователем, ожидает только сообщение и файлы
     */
    public function updateMessage(TicketsUpdateMessageRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
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

    /**
     * Закрыть заявление
     * Переволд в статус Closed, закрывается пользователем
     */
    public function remove(Request $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->canEdit($ticket)) {
            return JsonResponse::Forbidden('This ticket cannot be edited or removed.');
        }

        DB::transaction(function() use ($ticket, $request) {
            $original = clone $ticket;

            $ticket->state = TicketState::Closed->value;
            $ticket->save();

            $eventsController = new TicketsEventsController();
            $eventsController->create($original, $ticket);
        });

        return JsonResponse::Send([]);
    }

    private function canEdit(Ticket $ticket): bool
    {
        return !in_array($ticket->state, [
            TicketState::Success->value,
            TicketState::Closed->value,
            TicketState::Cancel->value,
        ]);
    }
}

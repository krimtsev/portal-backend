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
    private function isDashboard(): bool
    {
        return (bool) request()->attributes->get('is_dashboard', false);
    }

    private function getPartnerIds(): array
    {
        $user = auth()->user();
        if (!$user || !$user->partner_id) {
            return [];
        }

        $partner = Partner::with('group.partners')->find($user->partner_id);
        if (!$partner) {
            return [];
        }

        return $partner->group
            ? $partner->group->partners->pluck('id')->toArray()
            : [$partner->id];
    }

    private function ticketAccess(Ticket $ticket): bool
    {
        if (!$this->isDashboard()) {
            if (!in_array($ticket->partner_id, $this->getPartnerIds())) {
                return false;
            }
        }

        return true;
    }

    private function paginatedList(Builder $query, Request $request): \Illuminate\Http\JsonResponse
    {
        $showDeleted = filter_var($request->input('show_deleted', false), FILTER_VALIDATE_BOOLEAN);

        if (!$showDeleted) {
            $query->whereNull('tickets.deleted_at');
        }

        $query->with([
            'category:id,title',
            'partner:id,name',
            'user:id,name'
        ])
            ->select(
                'id',
                'title',
                'category_id',
                'partner_id',
                'user_id',
                'state',
                'created_at',
            )
            ->selectSub(
                DB::table('tickets_messages')
                    ->selectRaw('MAX(tickets_messages.created_at)')
                    ->whereColumn('tickets_messages.ticket_id', 'tickets.id')
                    ->whereNull('tickets_messages.deleted_at'),
                'last_message_at'
            )
            ->orderBy('tickets.created_at', 'desc');

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
     * Получить список
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Ticket::query();

        if (!$this->isDashboard()) {
            $allowedIds = $this->getPartnerIds();

            if (empty($allowedIds)) {
                return JsonResponse::Send([
                    'list' => [],
                ]);
            }

            $query->whereIn('partner_id', $allowedIds);
        }

        return $this->paginatedList(
            $query,
            $request
        );
    }

    /**
     * Получить заявление по id
     */
    public function get(Request $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->ticketAccess($ticket)) {
            return JsonResponse::Forbidden();
        }

        $ticket->load([
            'category:id,title',
            'partner:id,name',
            'user:id,name,login',
            'messages.user:id,name,login',
            'messages.files',
            'events.user:id,name,login',
        ]);

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

        if (!$this->isDashboard()) {
            $allowedIds = $this->getPartnerIds();

            if (!in_array($data['partner_id'], $allowedIds)) {
                return JsonResponse::Forbidden();
            }
        }

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
                'user_id'   => $userId,
                'text'      => $this->normalizeMessage($data['message']),
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
        if (!$this->ticketAccess($ticket)) {
            return JsonResponse::Forbidden();
        }

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
                    'user_id'   => $userId,
                    'text'      => $this->normalizeMessage($data['message']),
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

        return $this->get(new Request(), $ticket->id);
    }

    /**
     * Обновление сообщения
     * Выполняется пользователем, ожидает только сообщение и файлы
     */
    public function updateMessage(TicketsUpdateMessageRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->ticketAccess($ticket)) {
            return JsonResponse::Forbidden();
        }

        if (!$this->canEdit($ticket)) {
            return JsonResponse::Forbidden('This ticket cannot be edited.');
        }

        $data = $request->validated();

        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'text'      => $this->normalizeMessage($data['message']),
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                TicketsFilesController::add($ticket->id, $ticketMessage->id, $file);
            }
        }

        return $this->get(new Request(), $ticket->id);
    }

    /**
     * Закрыть заявление
     * Переволд в статус Closed, закрывается пользователем
     */
    public function remove(Request $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if (!$this->ticketAccess($ticket)) {
            return JsonResponse::Forbidden();
        }

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

    private function normalizeMessage(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Преобразуем любые переносы в единые \n
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        // Убираем пробелы в начале каждой строки
        $value = preg_replace('/^[ \t]+/m', '', $value);

        // Схлопываем 3 и более переносов в два
        $value = preg_replace("/\n{3,}/", "\n\n", $value);

        // Схлопываем несколько пробелов подряд в один
        $value = preg_replace('/[ \t]{2,}/', ' ', $value);

        // Обрезаем пробелы и переносы в начале и конце всего текста
        return trim($value);
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

<?php

namespace App\Http\Controllers\EventCalendar;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventCalendar\EventCalendarCreateRequest;
use App\Http\Requests\EventCalendar\EventCalendarListRequest;
use App\Http\Requests\EventCalendar\EventCalendarUpdateRequest;
use App\Http\Resources\EventCalendar\EventCalendarListResource;
use App\Http\Resources\EventCalendar\EventCalendarResource;
use App\Http\Resources\Partner\PartnerResource;
use App\Models\EventCalendar\EventCalendar;
use App\Models\Partner\Partner;
use App\Models\User\User;
use App\Responses\JsonResponse;
use App\Services\EventCalendar\EventCalendarService;
use Auth;
use Illuminate\Http\Request;

final class EventCalendarController extends Controller
{
    public function __construct(
        private readonly EventCalendarService $eventService
    ) {}

    public function list(EventCalendarListRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $result = $this->eventService->list($request, $user);

        $result['list'] = EventCalendarListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(Request $request, EventCalendar $batch)
    {
        $user = Auth::user();

        if (!$user->isSysAdmin() && $batch->user_id !== $user->id) {
            return JsonResponse::Forbidden();
        }

        $batch->load('eventCalendarUsers');

        return JsonResponse::Send([
            'data' => new EventCalendarResource($batch),
        ]);
    }

    public function create(EventCalendarCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $creatorId = Auth::user()->id;

        $this->eventService->create($request->validated(), $creatorId);

        return JsonResponse::Created();
    }

    public function update(EventCalendarUpdateRequest $request, EventCalendar $batch): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (!$user->isSysAdmin() && $batch->user_id !== $user->id) {
            return JsonResponse::Forbidden();
        }

        $this->eventService->update($request->validated(), $batch);

        return JsonResponse::Updated();
    }

    public function remove(EventCalendar $batch): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (!$user->isSysAdmin() && $batch->user_id !== $user->id) {
            return JsonResponse::Forbidden();
        }

        $this->eventService->delete($batch);

        return JsonResponse::Removed();
    }
}

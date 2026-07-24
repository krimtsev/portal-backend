<?php

namespace App\Services\EventCalendar;

use App\Helpers\Pagination\Pagination;
use App\Http\Requests\EventCalendar\EventCalendarListRequest;
use App\Models\EventCalendar\EventCalendar;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class EventCalendarService
{
    /**
     * Получить список событий в календаре.
     */
    public function list(EventCalendarListRequest $request, User $user): array
    {
        $query = EventCalendar::query()
            ->with([
                'user:id,name',
                'department:id,title',
            ])
            ->select([
                'id',
                'title',
                'description',
                'user_id',
                'department_id',
                'start_at',
                'end_at',
                'created_at',
            ]);

        if (!$user->isSysAdmin()) {
            $query->where('user_id', $user->id);
        }

        return Pagination::paginate(
            $query,
            $request,
            ['title'],
            ['id'],
            [],
        );
    }

    /**
     * Создать новое событие в календаре.
     *
     * @throws Throwable
     */
    public function create(array $data, int $creatorId)
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $data['user_id'] = $creatorId;

            $event = EventCalendar::create($data);

            if (!empty($data['responsible_user_ids'])) {
                $event->responsibleUsers()->attach($data['responsible_user_ids']);
            }
        });
    }

    /**
     * Обновить существующее событие.
     *
     * @throws Throwable
     */
    public function update(array $data, EventCalendar $event)
    {
        return DB::transaction(function () use ($event, $data) {
            $event->update($data);

            if (isset($data['responsible_user_ids'])) {
                $event->responsibleUsers()->sync($data['responsible_user_ids']);
            }
        });
    }

    /**
     * Удалить событие.
     *
     * @throws Throwable
     */
    public function delete(EventCalendar $event): bool
    {
        return DB::transaction(function () use ($event) {
            return $event->delete();
        });
    }

    public function getEventsForPeriod(
        int $startMonths = 3,
        int $endMonths = 2
    )
    {
        $startDate = Carbon::now()->subMonths($startMonths);
        $endDate = Carbon::now()->addMonths($endMonths);

        return EventCalendar::query()
            ->with([
                'user:id,name',
                'responsibleUsers:id,name',
            ])
            ->select([
                'id',
                'title',
                'description',
                'start_at',
                'end_at',
                'department_id',
                'user_id',
            ])
            ->whereBetween('start_at', [$startDate, $endDate])
            ->get()
            ->map(function ($event) {
                return [
                    'id'                => $event->id,
                    'title'             => $event->title,
                    'description'       => $event->description,
                    'start_at'          => $event->start_at,
                    'end_at'            => $event->end_at,
                    'department_id'     => $event->department_id,
                    'user'              => $event->user?->name,
                    'responsible_users' => $event->responsibleUsers->pluck('name'),
                ];
            });
    }
}

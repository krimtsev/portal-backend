<?php

declare(strict_types=1);

namespace App\Services\Partners;

use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

final class PartnerService
{
    /**
     * Получение структуры доступных партнеров пользователя
     */
    public function getUserPartnersData(User $user): array
    {
        $partnerId = $user->partner_id;

        if (!$partnerId) {
            return [
                'partner_id' => null,
                'partners'   => [],
            ];
        }

        $partner = Partner::with(['group.partners' => function ($query) {
            $query->orderBy('name');
        }])->findOrFail($partnerId);

        if ($partner->group) {
            $partners = $partner->group->partners->map(function (Partner $p) {
                return [
                    'partner_id' => $p->id,
                    'name'       => $p->name,
                ];
            });
        } else {
            $partners = collect([
                [
                    'partner_id' => $partner->id,
                    'name'       => $partner->name,
                ],
            ]);
        }

        return [
            'partner_id' => $partnerId,
            'partners'   => $partners,
        ];
    }

    /**
     * Создание партнера с телефонами и настройками по умолчанию
     */
    public function create(array $data): Partner
    {
        return DB::transaction(function () use ($data) {
            /** @var Partner $partner */
            $partner = Partner::create($data);

            if (!empty($data['telnums'])) {
                $partner->telnums()->createMany($data['telnums']);
            }

            $partner->reportSettings()->create();
            $partner->notificationChannel()->create();

            return $partner;
        });
    }

    /**
     * Обновление партнера и синхронизация его телефонов
     */
    public function update(Partner $partner, array $data): Partner
    {
        return DB::transaction(function () use ($partner, $data) {
            $partner->update($data);

            if (isset($data['telnums'])) {
                $telnumsData = collect($data['telnums']);

                $idsToKeep = $telnumsData->pluck('id')->filter()->toArray();
                $partner->telnums()->whereNotIn('id', $idsToKeep)->delete();

                foreach ($telnumsData as $telnumItem) {
                    $partner->telnums()->updateOrCreate(
                        ['id' => $telnumItem['id'] ?? null],
                        [
                            'name'   => $telnumItem['name'] ?? null,
                            'number' => $telnumItem['number'],
                        ]
                    );
                }
            }

            return $partner;
        });
    }
}

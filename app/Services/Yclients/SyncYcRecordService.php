<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclients\YcRecord;
use App\Models\Yclients\YcRecordDocument;
use App\Models\Yclients\YcRecordGoodsTransaction;
use App\Models\Yclients\YcRecordService;
use App\Models\Yclients\YcTariff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class SyncYcRecordService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function sync(int $companyId, string $date): void
    {
        $rawResponse = $this->yclients->records()->getRecords(
            $companyId,
            new RecordsFilters(
                start_date: $date,
                end_date: $date
            )
        );

        $recordsData = $rawResponse['data'] ?? [];

        if (empty($recordsData)) {
            return;
        }

        $activeTariffs = YcTariff::where('disabled', false)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderBy('start_date', 'desc')
            ->get()
            ->unique('service_id')
            ->keyBy('service_id');

        foreach (array_chunk($recordsData, 50) as $chunk) {
            $recordsToUpsert = [];
            $servicesToUpsert = [];
            $documentsToUpsert = [];
            $goodsToUpsert = [];

            foreach ($chunk as $item) {
                $dto = RecordsResponse::from($item);

                $totalCost = 0.00;
                $totalManualCost = 0.00;
                $totalTariffCost = 0.00;
                $totalBaseTariffCost = 0.00;

                foreach ($dto->services as $serviceDto) {
                    if (!$serviceDto->id) {
                        continue;
                    }

                    $costs = $this->calculateTariffCosts($serviceDto, $activeTariffs);

                    $totalTariffCost += $costs['tariff_cost'];
                    $totalBaseTariffCost += $costs['base_tariff_cost'];
                    $totalCost += $serviceDto->cost;
                    $totalManualCost += $serviceDto->manual_cost;

                    $servicesToUpsert[] = [
                        'company_id'       => $companyId,
                        'record_id'        => $dto->id,
                        'service_id'       => $serviceDto->id,
                        'title'            => $serviceDto->title,
                        'cost'             => $serviceDto->cost,
                        'manual_cost'      => $serviceDto->manual_cost,
                        'discount'         => $serviceDto->discount,
                        'amount'           => $serviceDto->amount,
                        'tariff_cost'      => $costs['tariff_cost'],
                        'base_tariff_cost' => $costs['base_tariff_cost'],
                    ];
                }

                foreach ($dto->documents as $documentDto) {
                    if (!$documentDto->id) {
                        continue;
                    }

                    $documentsToUpsert[] = [
                        'document_id'  => $documentDto->id,
                        'company_id'   => $companyId,
                        'type_id'      => $documentDto->type_id,
                        'type_title'   => $documentDto->type_title,
                        'storage_id'   => $documentDto->storage_id,
                        'user_id'      => $documentDto->user_id,
                        'date_created' => $documentDto->date_created,
                        'visit_id'     => $documentDto->visit_id,
                        'record_id'    => $documentDto->record_id,
                    ];
                }

                foreach ($dto->goods_transactions as $goodsDto) {
                    if (!$goodsDto->id) {
                        continue;
                    }

                    $goodsToUpsert[] = [
                        'record_id'              => $dto->id,
                        'transaction_id'         => $goodsDto->id,
                        'company_id'             => $dto->company_id,
                        'title'                  => $goodsDto->title,
                        'amount'                 => $goodsDto->amount,
                        'cost_per_unit'          => $goodsDto->cost_per_unit,
                        'cost'                   => $goodsDto->cost,
                        'manual_cost'            => $goodsDto->manual_cost,
                        'master_id'              => $goodsDto->master_id,
                        'storage_id'             => $goodsDto->storage_id,
                        'good_id'                => $goodsDto->good_id,
                        'discount'               => $goodsDto->discount,
                        'loyalty_abonement_id'   => $goodsDto->loyalty_abonement_id,
                        'loyalty_certificate_id' => $goodsDto->loyalty_certificate_id,

                        'datetime'        => $dto->datetime,   // Время записи
                        'record_staff_id' => $dto->staff_id,   // Мастер
                        'attendance'      => $dto->attendance, // Статус
                    ];
                }

                $recordsToUpsert[] = [
                    'record_id'              => $dto->id,
                    'company_id'             => $companyId,
                    'staff_id'               => $dto->staff_id,
                    'visit_id'               => $dto->visit_id,
                    'client_id'              => $dto->client?->id,
                    'client_name'            => $dto->client?->name,
                    'client_phone'           => $dto->client?->phone,
                    'client_success_visits'  => $dto->client?->success_visits_count ?? 0,
                    'client_fail_visits'     => $dto->client?->fail_visits_count ?? 0,
                    'datetime'               => $dto->datetime,
                    'visit_attendance'       => $dto->visit_attendance,
                    'attendance'             => $dto->attendance,
                    'confirmed'              => $dto->confirmed,
                    'length'                 => $dto->length,
                    'deleted'                => $dto->deleted,
                    'total_cost'             => $totalCost,
                    'total_manual_cost'      => $totalManualCost,
                    'total_tariff_cost'      => $totalTariffCost,
                    'total_base_tariff_cost' => $totalBaseTariffCost,
                ];
            }

            DB::transaction(function () use (
                $recordsToUpsert,
                $servicesToUpsert,
                $documentsToUpsert,
                $goodsToUpsert,
            ) {
                if (!empty($recordsToUpsert)) {
                    YcRecord::upsert(
                        $recordsToUpsert,
                        [
                            'record_id',
                        ],
                        [
                            'staff_id',
                            'visit_id',
                            'client_id',
                            'client_name',
                            'client_phone',
                            'client_success_visits',
                            'client_fail_visits',
                            'datetime',
                            'visit_attendance',
                            'attendance',
                            'confirmed',
                            'length',
                            'total_cost',
                            'total_manual_cost',
                            'total_tariff_cost',
                            'total_base_tariff_cost',
                        ]
                    );
                }

                if (!empty($servicesToUpsert)) {
                    YcRecordService::upsert(
                        $servicesToUpsert,
                        [
                            'record_id',
                            'service_id',
                        ],
                        [
                            'title',
                            'cost',
                            'manual_cost',
                            'discount',
                            'amount',
                            'tariff_cost',
                            'base_tariff_cost',
                        ]
                    );
                }

                if (!empty($documentsToUpsert)) {
                    YcRecordDocument::upsert(
                        $documentsToUpsert,
                        [
                            'record_id',
                            'document_id',
                        ],
                        [
                            'company_id',
                            'type_id',
                            'type_title',
                            'storage_id',
                            'user_id',
                            'date_created',
                            'visit_id',
                        ]
                    );
                }

                if (!empty($goodsToUpsert)) {
                    YcRecordGoodsTransaction::upsert(
                        $goodsToUpsert,
                        [
                            'record_id',
                            'transaction_id',
                        ],
                        [
                            'company_id',
                            'title',
                            'amount',
                            'cost_per_unit',
                            'cost',
                            'manual_cost',
                            'master_id',
                            'storage_id',
                            'good_id',
                            'discount',
                            'loyalty_abonement_id',
                            'loyalty_certificate_id',
                        ]
                    );
                }
            });
        }
    }

    /**
     * Цены по тарифам
     * $tariff_cost - считаем как const цены из тарифа или цена по записи
     * $base_tariff_cost - цена из тарифов, считаем только cost
     */
    private function calculateTariffCosts(object $serviceDto, Collection $activeTariffs): array
    {
        /** @var YcTariff|null $tariff */
        $tariff = $activeTariffs->get($serviceDto->id);

        if (!$tariff) {
            return [
                'tariff_cost'      => 0.00,
                'base_tariff_cost' => 0.00,
            ];
        }

        return [
            'tariff_cost'      => (float) ($tariff->cost !== null ? $tariff->cost : $serviceDto->manual_cost),
            'base_tariff_cost' => $tariff->cost !== null ? (float) $tariff->cost : 0.00,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcRecordService;
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

        foreach (array_chunk($recordsData, 50) as $chunk) {
            $recordsToUpsert = [];
            $servicesToUpsert = [];

            foreach ($chunk as $item) {
                $dto = RecordsResponse::from($item);

                if (!$dto->client?->id) {
                    continue;
                }

                $recordsToUpsert[] = [
                    'record_id'             => $dto->id,
                    'company_id'            => $companyId,
                    'staff_id'              => $dto->staff_id,
                    'visit_id'              => $dto->visit_id,
                    'client_id'             => $dto->client?->id,
                    'client_name'           => $dto->client?->name,
                    'client_phone'          => $dto->client?->phone,
                    'client_success_visits' => $dto->client?->success_visits_count ?? 0,
                    'client_fail_visits'    => $dto->client?->fail_visits_count ?? 0,
                    'datetime'              => $dto->datetime,
                    'total_cost'            => array_sum(array_map(fn ($s) => $s->cost, $dto->services)),
                    'total_manual_cost'     => array_sum(array_map(fn ($s) => $s->manual_cost, $dto->services)),
                    'total_analytics_cost'  => 0,
                ];

                foreach ($dto->services as $serviceDto) {
                    if (!$serviceDto->id) {
                        continue;
                    }

                    $servicesToUpsert[] = [
                        'company_id'     => $companyId,
                        'record_id'      => $dto->id,
                        'service_id'     => $serviceDto->id,
                        'title'          => $serviceDto->title,
                        'cost'           => $serviceDto->cost,
                        'analytics_cost' => 0,
                        'manual_cost'    => $serviceDto->manual_cost,
                        'discount'       => $serviceDto->discount,
                        'amount'         => $serviceDto->amount,
                    ];
                }
            }

            DB::transaction(function () use ($recordsToUpsert, $servicesToUpsert) {
                if (!empty($recordsToUpsert)) {
                    YcRecord::upsert(
                        $recordsToUpsert,
                        [
                            'company_id',
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
                            'total_cost',
                            'total_manual_cost',
                        ]
                    );
                }

                if (!empty($servicesToUpsert)) {
                    YcRecordService::upsert(
                        $servicesToUpsert,
                        [
                            'company_id',
                            'record_id',
                            'service_id',
                        ],
                        [
                            'title',
                            'cost',
                            'manual_cost',
                            'discount',
                            'amount',
                        ]
                    );
                }
            });
        }
    }
}

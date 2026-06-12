<?php

declare(strict_types=1);

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Integrations\Yclients\YclientsException;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcRecordService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SyncRecordsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
        public readonly string $date
    ) {
        $this->onQueue(QueueName::YCLIENTS->value);
    }

    /**
     * Уникальный ID задачи для предотвращения race conditions.
     */
    public function uniqueId(): string
    {
        return "yc_records_{$this->companyId}_{$this->date}";
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     * Первая ошибка — ждем 10 сек, вторая — 60 сек, третья — 120 сек.
     */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    /**
     * @throws YclientsException
     */
    public function handle(YclientsApi $yclients): void
    {
        $rawResponse = $yclients->records()->getRecords(
            $this->companyId,
            new RecordsFilters(
                start_date: $this->date,
                end_date: $this->date
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

                $recordsToUpsert[] = [
                    'record_id'             => $dto->id,
                    'company_id'            => $this->companyId,
                    'staff_id'              => $dto->staff_id,
                    'visit_id'              => $dto->visit_id,
                    'client_id'             => $dto->client->id,
                    'client_name'           => $dto->client->name,
                    'client_phone'          => $dto->client->phone,
                    'client_success_visits' => $dto->client->success_visits_count ?? 0,
                    'client_fail_visits'    => $dto->client->fail_visits_count ?? 0,
                    'datetime'              => $dto->datetime,
                    'total_cost'            => array_sum(array_map(fn ($s) => $s->cost, $dto->services)),
                    'total_manual_cost'     => array_sum(array_map(fn ($s) => $s->manual_cost, $dto->services)),
                ];

                foreach ($dto->services as $serviceDto) {
                    if (!$serviceDto->id) {
                        continue;
                    }

                    $servicesToUpsert[] = [
                        'company_id'  => $this->companyId,
                        'record_id'   => $dto->id,
                        'service_id'  => $serviceDto->id,
                        'title'       => $serviceDto->title,
                        'cost'        => $serviceDto->cost,
                        'manual_cost' => $serviceDto->manual_cost,
                        'discount'    => $serviceDto->discount,
                        'amount'      => $serviceDto->amount,
                    ];
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
                                'company_id',
                                'record_id',
                                'service_id',
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

    /**
     * Метод срабатывает, когда все попытки завершились неудачей
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('yclients')
            ->critical('Синхронизация YClients завершилась.', [
                'company_id' => $this->companyId,
                'date'       => $this->date,
                'error'      => $exception->getMessage(),
            ]);
    }
}

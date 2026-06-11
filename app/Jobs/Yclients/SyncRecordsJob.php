<?php

namespace App\Jobs\Yclients;

use App\Integrations\Yclients\Resources\Records\DTO\RecordsFilters;
use App\Integrations\Yclients\Resources\Records\DTO\RecordsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Integrations\Yclients\YclientsException;
use App\Models\Yclient\YcRecord;
use App\Models\Yclient\YcRecordService;
use App\Models\Yclient\YcTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncRecordsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
        public readonly string $date
    ) {}

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
        $rawData = $yclients->records()->getRecords(
            $this->companyId,
            new RecordsFilters(
                start_date: $this->date,
                end_date: $this->date
            )
        );

        $recordsToUpsert = [];
        $servicesToUpsert = [];

        foreach ($rawData as $item) {
            $dto = RecordsResponse::fromArray($item);

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

                'total_cost',
                'total_manual_cost',
            ];

            foreach ($dto->services as $service) {
                $servicesToUpsert[] = [
                    'company_id'     => $this->companyId,
                    'record_id'      => $dto->id,
                    'service_id'     => $service['id'],
                    'title'          => $service['title'],
                    'cost'           => $service['cost'] ?? 0,
                    'manual_cost'    => $service['manual_cost'] ?? 0,
                    'discount'       => $service['discount'] ?? 0,
                    'amount'         => $service['amount'] ?? 1,
                ];
            }

            DB::transaction(function () use ($recordsToUpsert, $servicesToUpsert) {
                if (!empty($recordsToUpsert)) {
                    YcRecord::upsert(
                        $recordsToUpsert,
                        [
                            'company_id',
                            'record_id'
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
                            'total_manual_cost'
                        ]
                    );
                }

                if (!empty($servicesToUpsert)) {
                    YcRecordService::upsert(
                        $servicesToUpsert,
                        ['company_id', 'record_id', 'service_id'], // Уникальный составной ключ для услуги
                        ['title', 'cost', 'manual_cost', 'discount', 'amount']
                    );
                }
            });
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

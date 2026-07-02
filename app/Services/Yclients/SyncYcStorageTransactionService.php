<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\StorageTransactions\DTO\StorageTransactionsFilters;
use App\Integrations\Yclients\Resources\StorageTransactions\DTO\StorageTransactionsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcStorageTransaction;
use Carbon\Carbon;

final readonly class SyncYcStorageTransactionService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function sync(int $companyId, string $date): void
    {
        $rawResponse = $this->yclients->storageTransactions()->getStorageTransactions(
            $companyId,
            new StorageTransactionsFilters(
                start_date: $date,
                end_date: $date
            )
        );

        $storageTransactionsData = $rawResponse['data'] ?? [];

        if (empty($storageTransactionsData)) {
            return;
        }

        $upsertData = [];

        foreach ($storageTransactionsData as $item) {
            $dto = StorageTransactionsResponse::from($item);

            $upsertData[] = [
                'transaction_id'      => $dto->id,
                'company_id'          => $companyId,
                'master_id'           => $dto->master?->id,
                'document_id'         => $dto->document_id,
                'type_id'             => $dto->type_id,
                'type'                => $dto->type,
                'operation_unit_type' => $dto->operation_unit_type,
                'amount'              => $dto->amount,
                'create_date'         => Carbon::parse($dto->create_date)
                    ->tz(config('app.timezone'))
                    ->toDateTimeString(),
                'cost_per_unit'          => $dto->cost_per_unit,
                'cost'                   => $dto->cost,
                'discount'               => $dto->discount,
                'comment'                => $dto->comment,
                'record_id'              => $dto->record_id,
                'loyalty_abonement_id'   => $dto->loyalty_abonement_id,
                'loyalty_certificate_id' => $dto->loyalty_certificate_id,
                'good_id'                => $dto->good->id,
                'good_title'             => $dto->good->title,
                'storage_id'             => $dto->storage?->id,
                'storage_title'          => $dto->storage?->title,
                'client_id'              => $dto->client?->id,
                'service_id'             => $dto->service?->id,
                'service_title'          => $dto->service?->title,
            ];
        }

        YcStorageTransaction::upsert(
            $upsertData,
            [
                'company_id',
                'transaction_id',
            ],
            [
                'master_id',
                'document_id',
                'type_id',
                'type',
                'operation_unit_type',
                'amount',
                'create_date',
                'cost_per_unit',
                'cost',
                'discount',
                'comment',
                'record_id',
                'loyalty_abonement_id',
                'loyalty_certificate_id',
                'good_id',
                'good_title',
                'storage_id',
                'storage_title',
                'client_id',
                'service_id',
                'service_title',
            ]
        );
    }
}

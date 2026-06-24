<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsFilters;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcTransaction;
use Carbon\Carbon;

final readonly class SyncYcTransactionService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function sync(int $companyId, string $date): void
    {
        $rawResponse = $this->yclients->transactions()->getTransactions(
            $companyId,
            new TransactionsFilters(
                start_date: $date,
                end_date: $date
            )
        );

        $transactionsData = $rawResponse['data'] ?? [];

        if (empty($transactionsData)) {
            return;
        }

        $upsertData = [];

        foreach ($transactionsData as $item) {
            $dto = TransactionsResponse::from($item);

            $upsertData[] = [
                'transaction_id' => $dto->id,
                'company_id'     => $companyId,
                'master_id'      => $dto->master?->id,
                'document_id'    => $dto->document_id,
                'record_id'      => $dto->record_id,
                'visit_id'       => $dto->visit_id,
                'account_id'     => $dto->account?->id,
                'account_title'  => $dto->account?->title,
                'client_id'      => $dto->client?->id,
                'amount'         => $dto->amount,
                'sold_item_id'   => $dto->sold_item_id,
                'sold_item_type' => $dto->sold_item_type,
                'expense_id'     => $dto->expense?->id,
                'expense_title'  => $dto->expense?->title,
                'expense_type'   => $dto->expense?->type,
                'date'           => Carbon::parse($dto->date)
                    ->tz(config('app.timezone'))
                    ->toDateTimeString(),
            ];
        }

        YcTransaction::upsert(
            $upsertData,
            [
                'company_id',
                'transaction_id',
            ],
            [
                'master_id',
                'document_id',
                'record_id',
                'visit_id',
                'account_id',
                'account_title',
                'client_id',
                'amount',
                'sold_item_id',
                'sold_item_type',
                'expense_id',
                'expense_title',
                'expense_type',
                'date',
            ]
        );
    }
}

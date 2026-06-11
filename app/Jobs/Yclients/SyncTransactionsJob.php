<?php

namespace App\Jobs\Yclients;

use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsFilters;
use App\Integrations\Yclients\Resources\Transactions\DTO\TransactionsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Integrations\Yclients\YclientsException;
use App\Models\Yclient\YcTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncTransactionsJob implements ShouldBeUnique, ShouldQueue
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
        return "yc_comments_{$this->companyId}_{$this->date}";
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
        $rawData = $yclients->transactions()->getTransactions(
            $this->companyId,
            new TransactionsFilters(
                start_date: $this->date,
                end_date: $this->date
            )
        );

        foreach ($rawData as $item) {
            $dto = TransactionsResponse::fromArray($item);

            YcTransaction::updateOrCreate(
                [
                    'company_id'     => $this->companyId,
                    'transaction_id' => $dto->id,
                ],
                [
                    'transaction_id' => $dto->id,
                    'company_id'     => $this->companyId,
                    'staff_id'       => $dto->getMasterId(),
                    'record_id'      => $dto->record_id,
                    'visit_id'       => $dto->visit_id,
                    'document_id'    => $dto->document_id,
                    'amount'         => $dto->amount,
                    'sold_item_type' => $dto->sold_item_type,
                    'expense_id'     => $dto->expense_id,
                    'expense_title'  => $dto->expense_title,
                    'expense_type'   => $dto->expense_type,
                    'date'           => $dto->date,
                ]
            );
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

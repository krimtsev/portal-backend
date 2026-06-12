<?php

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsFilters;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Integrations\Yclients\YclientsException;
use App\Models\Yclient\YcCompanyStaff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncCommentsJob implements ShouldBeUnique, ShouldQueue
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
        $raw = $yclients->comments()->getComments(
            $this->companyId,
            new CommentsFilters(
                start_date: $this->date,
                end_date: $this->date
            )
        );

        $items = $raw['data'] ?? [];

        $upsertData = [];

        foreach ($items as $item) {
            $dto = CommentsResponse::from($item);

            $upsertData[] = [
                'company_id' => $this->companyId,
                'comment_id' => $dto->id,
                'salon_id'   => $dto->salon_id,
                'staff_id'   => $dto->master_id,
                'type'       => $dto->type,
                'rating'     => $dto->rating,
                'date'       => $dto->date,
            ];
        }

        if (!empty($upsertData)) {
            YcCompanyStaff::upsert(
                $upsertData,
                [
                    'company_id',
                    'comment_id',
                ],
                [
                    'company_id',
                    'comment_id',
                    'salon_id',
                    'staff_id',
                    'type',
                    'rating',
                    'date',
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

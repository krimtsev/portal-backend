<?php

namespace App\Jobs\Yclients;

use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsResponse;
use App\Enums\QueueName;
use App\Integrations\Yclients\DTO\Analytics\CompanyStatsDto;
use App\Integrations\Yclients\YclientsApi;
use App\Integrations\Yclients\YclientsException;
use App\Models\Yclient\YcCompanyDailyStat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncCompanyDailyStatJob implements ShouldBeUnique, ShouldQueue
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
        return "yc_company_stats_{$this->companyId}_{$this->date}";
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
        $rawData = $yclients->analytics()->getCompanyStats(
            $this->companyId,
            new CompanyStatsFilters(
                date_from: $this->date,
                date_to: $this->date
            )
        );

        $dto = CompanyStatsResponse::fromArray($rawData);

        YcCompanyDailyStat::updateOrCreate(
            [
                'company_id' => $this->companyId,
                'date'       => $this->date,
            ],
            [
                'income_total'     => $dto->income_total,
                'income_goods'     => $dto->income_goods,
                'income_services'  => $dto->income_services,
                'fullness_percent' => $dto->fullness_percent,
                'record_completed' => $dto->record_completed,
                'record_pending'   => $dto->record_pending,
                'record_canceled'  => $dto->record_canceled,
                'record_total'     => $dto->record_total,
                'client_new'       => $dto->client_new,
                'client_return'    => $dto->client_return,
                'client_active'    => $dto->client_active,
                'client_lost'      => $dto->client_lost,
                'client_total'     => $dto->client_total,
            ]
        );
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

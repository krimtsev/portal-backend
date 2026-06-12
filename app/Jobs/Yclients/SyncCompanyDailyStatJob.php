<?php

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsResponse;
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
        $raw = $yclients->analytics()->getCompanyStats(
            $this->companyId,
            new CompanyStatsFilters(
                date_from: $this->date,
                date_to: $this->date
            )
        );

        $item = $raw['data'] ?? [];

        $dto = CompanyStatsResponse::from($item);

        YcCompanyDailyStat::updateOrCreate(
            [
                'company_id' => $this->companyId,
                'date'       => $this->date,
            ],
            [
                'income_total'     => $dto->income_total_stats->current_sum,
                'income_goods'     => $dto->income_goods_stats->current_sum,
                'income_services'  => $dto->income_services_stats->current_sum,
                'fullness_percent' => $dto->fullness_stats->current_percent,
                'record_completed' => $dto->record_stats->current_completed_count,
                'record_pending'   => $dto->record_stats->current_pending_count,
                'record_canceled'  => $dto->record_stats->current_canceled_count,
                'record_total'     => $dto->record_stats->current_total_count,
                'client_new'       => $dto->client_stats->new_count,
                'client_return'    => $dto->client_stats->return_count,
                'client_active'    => $dto->client_stats->active_count,
                'client_lost'      => $dto->client_stats->lost_count,
                'client_total'     => $dto->client_stats->total_count,
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

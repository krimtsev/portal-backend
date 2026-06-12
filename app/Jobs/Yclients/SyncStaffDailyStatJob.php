<?php

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\Resources\Analytics\AnalyticsResource;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsFilters;
use App\Integrations\Yclients\Resources\Analytics\DTO\CompanyStatsResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncStaffDailyStatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $companyId,
        public readonly int $staffId,
        public readonly string $date
    ) {
        $this->onQueue(QueueName::YCLIENTS->value);
    }

    public function handle(AnalyticsResource $analyticsResource): void
    {
        $filters = new CompanyStatsFilters([
            'date_from'  => $this->date,
            'date_to'    => $this->date,
            'staff_id'   => $this->staffId,
        ]);

        // Получаем сырые данные из API
        $rawStats = $analyticsResource->getCompanyStats($this->companyId, $filters);

        // Гидратируем DTO (предполагаем наличие фабричного метода класса ValidateResponse)
        /** @var CompanyStatsResponse $stats */
        $stats = CompanyStatsResponse::fromArray($rawStats);

        // Записываем в базу, используя заготовленный уникальный ключ
        DB::table('yc_staff_daily_stats')->updateOrCreate(
            [
                'company_id' => $this->companyId,
                'staff_id'   => $this->staffId,
                'date'       => $this->date,
            ],
            [
                // Финансовые показатели
                'income_total'     => $stats->income_total_stats->total,
                'income_goods'     => $stats->income_goods_stats->total ?? 0,
                'income_services'  => $stats->income_services_stats->total ?? 0,

                // Операционные показатели
                'fullness_percent' => $stats->fullness_stats->percent ?? 0,

                // Записи
                'record_completed' => $stats->record_stats->completed ?? 0,
                'record_pending'   => $stats->record_stats->pending ?? 0,
                'record_canceled'  => $stats->record_stats->failed ?? 0,
                'record_total'     => $stats->record_stats->total ?? 0,

                // Клиенты
                'client_new'       => $stats->client_stats->new ?? 0,
                'client_return'    => $stats->client_stats->return ?? 0,
                'client_active'    => $stats->client_stats->active ?? 0,
                'client_lost'      => $stats->client_stats->lost ?? 0,
                'client_total'     => $stats->client_stats->total ?? 0,

                'updated_at'       => now(),
            ]
        );
    }
}

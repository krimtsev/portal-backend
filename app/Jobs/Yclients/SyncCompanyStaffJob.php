<?php

namespace App\Jobs\Yclients;

use App\Enums\QueueName;
use App\Integrations\Yclients\Resources\Staff\DTO\StaffResponse;
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

class SyncCompanyStaffJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
    ) {
        $this->onQueue(QueueName::YCLIENTS->value);
    }

    /**
     * Уникальный ID задачи для предотвращения race conditions.
     */
    public function uniqueId(): string
    {
        return "yc_company_staff_{$this->companyId}";
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
     * @throws YclientsException|Throwable
     */
    public function handle(YclientsApi $yclients): void
    {
        $raw = $yclients->staff()->getStaff($this->companyId);
        $items = $raw['data'] ?? [];

        if (empty($items)) {
            return;
        }

        $upsertData = [];

        foreach ($items as $item) {
            $dto = StaffResponse::from($item);

            $upsertData[] = [
                'company_id'     => $dto->company_id,
                'staff_id'       => $dto->id,
                'name'           => $dto->name,
                'firstname'      => $dto->employee?->firstname,
                'surname'        => $dto->employee?->surname,
                'specialization' => $dto->specialization,
                'fired'          => $dto->fired,
                'dismissal_date' => $dto->dismissal_date,
                'rating'         => $dto->rating,
            ];
        }

        if (!empty($upsertData)) {
            YcCompanyStaff::upsert(
                $upsertData,
                [
                    'company_id',
                    'staff_id',
                ],
                [
                    'name',
                    'firstname',
                    'surname',
                    'specialization',
                    'is_fired',
                    'dismissal_date',
                    'rating',
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
                'error'      => $exception->getMessage(),
            ]);
    }
}

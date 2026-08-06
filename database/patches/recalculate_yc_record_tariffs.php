<?php

use App\Models\Yclients\YcRecord;
use App\Models\Yclients\YcRecordService;
use App\Models\Yclients\YcTariff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * php artisan tinker database/patches/recalculate_yc_record_tariffs.php
 */

// Кэш для активных тарифов по датам, чтобы не делать запросы в цикле
$tariffsByDate = [];

$getTariffsForDate = function (string $date) use (&$tariffsByDate): Collection {
    if (!isset($tariffsByDate[$date])) {
        $tariffsByDate[$date] = YcTariff::where('disabled', false)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderBy('start_date', 'desc')
            ->get()
            ->unique('service_id')
            ->keyBy('service_id');
    }

    return $tariffsByDate[$date];
};

// Функция пересчета, аналогичная SyncYcRecordService
$calculateTariffCosts = function (YcRecordService $service, Collection $activeTariffs): array {
    /** @var YcTariff|null $tariff */
    $tariff = $activeTariffs->get($service->service_id);

    if (!$tariff) {
        return [
            'tariff_cost'      => 0.00,
            'base_tariff_cost' => 0.00,
        ];
    }

    return [
        'tariff_cost'      => (float) ($tariff->cost !== null ? $tariff->cost : $service->manual_cost),
        'base_tariff_cost' => $tariff->cost !== null ? (float) $tariff->cost : 0.00,
    ];
};

echo "Начинаем пересчет тарифов для YcRecord...\n";

// Обрабатываем записи чанками вместе с привязанными сервисами
YcRecord::with('services')->chunkById(500, function ($records) use ($getTariffsForDate, $calculateTariffCosts) {
    $recordsToUpsert = [];
    $servicesToUpsert = [];

    foreach ($records as $record) {
        $date = $record->datetime->format('Y-m-d');
        $activeTariffs = $getTariffsForDate($date);

        $totalCost = 0.00;
        $totalManualCost = 0.00;
        $totalTariffCost = 0.00;
        $totalBaseTariffCost = 0.00;

        foreach ($record->services as $service) {
            // Пересчитываем цены для конкретной услуги
            $costs = $calculateTariffCosts($service, $activeTariffs);

            $totalTariffCost += $costs['tariff_cost'];
            $totalBaseTariffCost += $costs['base_tariff_cost'];
            $totalCost += $service->cost;
            $totalManualCost += $service->manual_cost;

            // Собираем массив для обновления сервисов
            $servicesToUpsert[] = [
                'record_id'        => $service->record_id,
                'service_id'       => $service->service_id,
                'company_id'       => $service->company_id,
                'title'            => $service->title,
                'cost'             => $service->cost,
                'manual_cost'      => $service->manual_cost,
                'discount'         => $service->discount,
                'amount'           => $service->amount,
                'tariff_cost'      => $costs['tariff_cost'],
                'base_tariff_cost' => $costs['base_tariff_cost'],
            ];
        }

        // Собираем массив для обновления самой записи
        $recordsToUpsert[] = [
            'record_id'              => $record->record_id,
            'company_id'             => $record->company_id,
            'staff_id'               => $record->staff_id,
            'visit_id'               => $record->visit_id,
            'client_id'              => $record->client_id,
            'client_name'            => $record->client_name,
            'client_phone'           => $record->client_phone,
            'client_success_visits'  => $record->client_success_visits,
            'client_fail_visits'     => $record->client_fail_visits,
            'datetime'               => $record->datetime,
            'visit_attendance'       => $record->visit_attendance,
            'attendance'             => $record->attendance,
            'confirmed'              => $record->confirmed,
            'length'                 => $record->length,
            'deleted'                => $record->deleted,

            // Обновленные суммы
            'total_cost'             => $totalCost,
            'total_manual_cost'      => $totalManualCost,
            'total_tariff_cost'      => $totalTariffCost,
            'total_base_tariff_cost' => $totalBaseTariffCost,
        ];
    }

    // Сохраняем пачкой через транзакцию
    DB::transaction(function () use ($recordsToUpsert, $servicesToUpsert) {
        if (!empty($recordsToUpsert)) {
            YcRecord::upsert(
                $recordsToUpsert,
                ['record_id'],
                [
                    'total_cost',
                    'total_manual_cost',
                    'total_tariff_cost',
                    'total_base_tariff_cost',
                ]
            );
        }

        if (!empty($servicesToUpsert)) {
            YcRecordService::upsert(
                $servicesToUpsert,
                ['record_id', 'service_id'],
                [
                    'tariff_cost',
                    'base_tariff_cost',
                ]
            );
        }
    });
});

echo "Пересчет записей успешно завершен.\n";

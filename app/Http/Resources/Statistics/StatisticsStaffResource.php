<?php

declare(strict_types=1);

namespace App\Http\Resources\Statistics;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $additionalServices = $this->records ? (float) $this->records->total_tariff_cost : 0.00;
        $transactionSales = $this->transactions ? (float) $this->transactions->transaction_sales : 0.00;
        $transactionLoyalty = $this->transactions ? (float) $this->transactions->transaction_loyalty : 0.00;

        $income_total = (int) round($this->income_total);
        $income_goods = (int) round($this->income_goods);

        return [
            'staff_id'       => $this->staff_id,
            'name'           => $this->name,
            'firstname'      => $this->firstname,
            'surname'        => $this->surname,
            'specialization' => $this->specialization,
            'avatar'         => $this->avatar,

            'work_days_count' => $this->work_days ? (int) $this->work_days->work_days_count : 0,

            'income_goods'      => $income_goods,
            'income_total'      => $income_total,
            'fullness_percent'  => (int) round($this->fullness_percent),
            'client_new'        => (int) $this->client_new,
            'client_return'     => (int) $this->client_return,
            'retention_percent' => (int) round($this->retention_percent),
            'average_sum'       => (int) round($this->income_average ?? 0),

            'rating_total' => $this->ratings ? (int) $this->ratings->rating_total : 0,
            'rating_best'  => $this->ratings ? (int) $this->ratings->rating_best : 0,

            'additional_services'        => (int) round($additionalServices),
            'transaction_sales'          => $transactionSales,
            'services_with_transactions' => (int) round($additionalServices + $transactionSales),
            'transaction_loyalty'        => (int) $transactionLoyalty,

            'services_per_visit' => ($this->records && $this->records->attended_count > 0)
                ? round($this->records->services_count / $this->records->attended_count, 2)
                : 0.00,
        ];
    }
}

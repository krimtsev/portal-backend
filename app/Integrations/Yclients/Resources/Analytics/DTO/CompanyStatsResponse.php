<?php

namespace App\Integrations\Yclients\Resources\Analytics\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

final class CompanyStatsResponse extends ValidateResponse
{
    public function __construct(
        public readonly IncomeStatsDTO $income_total_stats,
        public readonly IncomeStatsDTO $income_services_stats,
        public readonly IncomeStatsDTO $income_goods_stats,
        public readonly IncomeStatsDTO $income_average_stats,
        public readonly IncomeStatsDTO $income_average_services_stats,
        public readonly FullnessStatsDTO $fullness_stats,
        public readonly RecordStatsDTO $record_stats,
        public readonly ClientStatsDTO $client_stats,
    ) {}

    protected static function rules(): array
    {
        return [
            'income_total_stats'            => ['required', 'array'],
            'income_services_stats'         => ['required', 'array'],
            'income_goods_stats'            => ['required', 'array'],
            'income_average_stats'          => ['required', 'array'],
            'income_average_services_stats' => ['required', 'array'],
            'fullness_stats'                => ['required', 'array'],
            'record_stats'                  => ['required', 'array'],
            'client_stats'                  => ['required', 'array'],
        ];
    }

    protected static function casts(): array
    {
        return [
            'income_total_stats'            => IncomeStatsDTO::class,
            'income_services_stats'         => IncomeStatsDTO::class,
            'income_goods_stats'            => IncomeStatsDTO::class,
            'income_average_stats'          => IncomeStatsDTO::class,
            'income_average_services_stats' => IncomeStatsDTO::class,
            'fullness_stats'                => FullnessStatsDTO::class,
            'record_stats'                  => RecordStatsDTO::class,
            'client_stats'                  => ClientStatsDTO::class,
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}

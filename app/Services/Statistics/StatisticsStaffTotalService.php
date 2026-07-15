<?php

declare(strict_types=1);

namespace App\Services\Statistics;

use App\Models\Partner\Partner;
use App\Models\Yclients\YcComment;
use App\Models\Yclients\YcCompanyStat;
use App\Models\Yclients\YcRecord;
use App\Models\Yclients\YcTransaction;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

final class StatisticsStaffTotalService
{
    /**
     * @return array<string, mixed> Возвращаем массив со строгим набором ключей
     */
    public function getMonthlyStats(Partner $partner, string $date): array
    {
        $companyId = (int) $partner->yclients_id;

        $monthInput = Carbon::parse($date);
        $startDate = $monthInput->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $monthInput->copy()->endOfMonth()->format('Y-m-d');

        /** @var YcCompanyStat|null $stat */
        $stat = YcCompanyStat::forCompany($companyId)
            ->monthlyForPeriod($startDate, $endDate)
            ->first();

        $startDateTime = $monthInput->copy()->startOfMonth()->startOfDay()->format('Y-m-d H:i:s');
        $endDateTime = $monthInput->copy()->endOfMonth()->endOfDay()->format('Y-m-d H:i:s');

        $currentPeriod = $monthInput->copy()->startOfMonth()->startOfDay()->subMonths(2)->startOfDay();
        $currentPeriodStart = $monthInput->copy()->startOfMonth()->subMonths(2)->startOfDay()->format('Y-m-d H:i:s');
        $pastPeriodEnd = $currentPeriod->copy()->subDay()->endOfDay()->format('Y-m-d H:i:s');
        $pastPeriodStart = $currentPeriod->copy()->subDays(60)->startOfDay()->format('Y-m-d H:i:s');

        $companyPastClients = $this->getCompanyClientsByPeriod($companyId, $pastPeriodStart, $pastPeriodEnd);
        $companyCurrentClients = $this->getCompanyClientsByPeriod($companyId, $currentPeriodStart, $endDateTime);

        $retentionPercent = $companyPastClients->isNotEmpty()
            ? round(($companyCurrentClients->intersect($companyPastClients)->count() / $companyPastClients->count()) * 100, 2)
            : 0.00;

        $transactions = $this->getTransactionStats($companyId, $startDateTime, $endDateTime);
        $transactionSales = $transactions ? (float) $transactions->transaction_sales : 0.00;
        $transactionLoyalty = $transactions ? (float) $transactions->transaction_loyalty : 0.00;

        $ratings = $this->getRatingStats($companyId, $startDateTime, $endDateTime);
        $ratingTotal = $ratings ? (int) $ratings->rating_total : 0;
        $ratingBest = $ratings ? (int) $ratings->rating_best : 0;

        $records = $this->getRecordStats($companyId, $startDateTime, $endDateTime);
        $additionalServices = $records ? (float) $records->additional_services : 0.00;

        $servicesPerVisit = ($records && $records->attended_count > 0)
            ? round((float) $records->total_services / $records->attended_count, 2)
            : 0.00;

        $servicesWithTransactions = $additionalServices + $transactionSales;

        // Строгое формирование и возврат требуемых ключей
        return [
            'income_total'               => (float) ($stat->income_total ?? 0.00),
            'fullness_percent'           => (int) round((float) ($stat->fullness_percent ?? 0)),
            'client_new'                 => (int) ($stat->client_new ?? 0),
            'client_return'              => (int) ($stat->client_return ?? 0),
            'retention_percent'          => (int) round((float) $retentionPercent),
            'rating_total'               => $ratingTotal,
            'rating_best'                => $ratingBest,
            'additional_services'        => $additionalServices,
            'transaction_sales'          => $transactionSales,
            'services_with_transactions' => $servicesWithTransactions,
            'transaction_loyalty'        => $transactionLoyalty,
            'average_sum'                => (int) round((float) ($stat->income_average ?? 0)),
            'services_per_visit'         => $servicesPerVisit,
        ];
    }

    public function getComparedMonthlyStats(Partner $partner, string $date): \stdClass
    {
        $currentStats = (object) $this->getMonthlyStats($partner, $date);

        $prevDate = Carbon::parse($date)->subMonth()->format('Y-m-d');

        $prevStats = (object) $this->getMonthlyStats($partner, $prevDate);

        $currentStats->previous_stats = $prevStats;

        return $currentStats;
    }

    private function getCompanyClientsByPeriod(int $companyId, string $start, string $end): Collection
    {
        return YcRecord::query()
            ->where('company_id', $companyId)
            ->whereBetween('datetime', [$start, $end])
            ->where('attendance', 1)
            ->whereNotNull('client_id')
            ->pluck('client_id')
            ->unique();
    }

    private function getRatingStats(int $companyId, string $start, string $end): YcComment
    {
        return YcComment::query()
            ->where('company_id', $companyId)
            ->where('type', 1)
            ->whereBetween('date', [$start, $end])
            ->selectRaw('COUNT(*) as rating_total')
            ->selectRaw('SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_best')
            ->first();
    }

    private function getRecordStats(int $companyId, string $start, string $end): YcRecord
    {
        $servicesSub = DB::table('yc_record_services')
            ->select('record_id', DB::raw('SUM(amount) as services_count'))
            ->groupBy('record_id');

        return YcRecord::query()
            ->leftJoinSub($servicesSub, 'services_sub', 'yc_records.record_id', '=', 'services_sub.record_id')
            ->where('yc_records.company_id', $companyId)
            ->whereBetween('yc_records.datetime', [$start, $end])
            ->selectRaw('SUM(yc_records.total_tariff_cost) as additional_services')
            ->selectRaw('SUM(CASE WHEN yc_records.attendance = 1 THEN COALESCE(services_sub.services_count, 0) ELSE 0 END) as total_services')
            ->selectRaw('COUNT(DISTINCT CASE WHEN yc_records.attendance = 1 THEN yc_records.record_id END) as attended_count')
            ->first();
    }

    private function getTransactionStats(int $companyId, string $start, string $end): YcTransaction
    {
        return YcTransaction::query()
            ->where('company_id', $companyId)
            ->whereBetween('date', [$start, $end])
            ->selectRaw('SUM(CASE WHEN expense_id IN (6, 12) THEN ABS(amount) ELSE 0 END) as transaction_loyalty')
            ->selectRaw('SUM(CASE WHEN expense_id IN (7) THEN ABS(amount) ELSE 0 END) as transaction_sales')
            ->first();
    }
}

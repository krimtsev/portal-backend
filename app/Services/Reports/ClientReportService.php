<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Enums\Report\ClientReportType;
use App\Exports\Reports\ClientReportExcelExport;
use App\Integrations\Telegram\Support\TelegramTargetResolver;
use App\Integrations\Telegram\TelegramManager;
use App\Models\Partner\Partner;
use App\Models\Yclients\YcRecord;
use App\Services\Formatters\ClientReportFormatter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

final readonly class ClientReportService
{
    public function __construct(
        private TelegramManager $telegram,
        private ClientReportExcelExport $exporter
    ) {}

    public function process(Partner $partner, ClientReportType $type): void
    {
        $settingKey = $type->daysSettingKey();
        $days = (int) ($partner->reportSettings?->{$settingKey} ?? 0);

        if ($days === 0) {
            return;
        }

        $targetDate = Carbon::now()->subDays($days)->startOfDay();
        $records = $this->fetchBaseRecords($partner, $type, $targetDate);

        if ($records->isEmpty()) {
            $this->sendEmptyReport($partner, $type, $targetDate, $days);

            return;
        }

        $processedItems = $this->filterFutureVisits($partner, $records, $targetDate);

        if ($processedItems->isEmpty()) {
            $this->sendEmptyReport($partner, $type, $targetDate, $days);

            return;
        }

        $this->sendExcelReport($partner, $processedItems, $type, $targetDate, $days);
    }

    private function fetchBaseRecords(Partner $partner, ClientReportType $type, Carbon $targetDate): Collection
    {
        $query = YcRecord::query()
            ->with('services')
            ->where('company_id', $partner->yclients_id)
            ->whereDate('datetime', $targetDate);

        if ($type === ClientReportType::NEW_CLIENTS) {
            $query->where('client_success_visits', 1);
        }

        return $query->get();
    }

    private function filterFutureVisits(Partner $partner, Collection $records, Carbon $targetDate): Collection
    {
        $phones = $records->pluck('client_phone')->filter()->unique()->values();

        $futureVisitsGrouped = YcRecord::query()
            ->select(['record_id', 'client_phone', 'company_id', 'datetime'])
            ->with('services')
            ->whereIn('client_phone', $phones)
            ->where('datetime', '>', $targetDate->clone()->endOfDay())
            ->orderByDesc('datetime')
            ->get()
            ->groupBy('client_phone');

        $otherBranchIds = $futureVisitsGrouped
            ->flatten()
            ->pluck('company_id')
            ->reject(fn ($id) => (string) $id === (string) $partner->yclients_id)
            ->unique();

        $partnerBranchNames = Partner::query()
            ->whereIn('yclients_id', $otherBranchIds)
            ->pluck('name', 'yclients_id');

        $processed = collect();

        foreach ($records as $record) {
            if (empty($record->client_phone)) {
                continue;
            }

            /** @var Collection<int, YcRecord> $clientVisits */
            $clientVisits = $futureVisitsGrouped->get($record->client_phone, collect());

            if ($clientVisits->isEmpty()) {
                $processed->push(['record' => $record]);

                continue;
            }

            $hasVisitedCurrentBranch = $clientVisits->contains(
                fn ($visit) => (string) $visit->company_id === (string) $partner->yclients_id
            );

            if (!$hasVisitedCurrentBranch) {
                $latestVisit = $clientVisits->first();
                $branchName = $partnerBranchNames->get($latestVisit->company_id);

                $processed->push([
                    'record'            => $record,
                    'other_branch_name' => $branchName,
                    'other_branch_date' => $latestVisit->datetime,
                    'other_branch_services' => $latestVisit->services,
                ]);
            }
        }

        return $processed;
    }

    private function sendEmptyReport(Partner $partner, ClientReportType $type, Carbon $targetDate, int $days): void
    {
        $text = ClientReportFormatter::format(
            partner: $partner,
            type: $type,
            targetDate: $targetDate,
            days: $days,
            isEmpty: true
        );

        $this->dispatchTelegram($partner, fn ($bot, $chatId) => $bot->sendMessage([
            'chat_id' => $chatId,
            'text'    => $text,
        ]));
    }

    private function sendExcelReport(
        Partner $partner,
        Collection $items,
        ClientReportType $type,
        Carbon $targetDate,
        int $days
    ): void {
        $fileName = sprintf('report_%s_%s.xlsx', $type->value, now()->format('Y-m-d'));
        $absolutePath = $this->exporter->generate($partner, $items, $fileName, $type->label());

        try {
            $caption = ClientReportFormatter::format(
                partner: $partner,
                type: $type,
                targetDate: $targetDate,
                days: $days,
                isEmpty: false
            );

            $this->dispatchTelegram($partner, function ($bot, $chatId) use ($absolutePath, $caption) {
                return $bot->sendDocument([
                    'chat_id'  => $chatId,
                    'document' => $absolutePath,
                    'caption'  => $caption,
                ]);
            });
        } finally {
            if (file_exists($absolutePath)) {
                @unlink($absolutePath);
            }
        }
    }

    private function dispatchTelegram(Partner $partner, callable $callback): void
    {
        $target = TelegramTargetResolver::resolve(
            defaultChatId: $partner->notificationChannel->telegram_chat_id,
            defaultBotName: 'notification'
        );

        $bot = $this->telegram->bot($target->botName);
        $response = $callback($bot, $target->chatId);

        if (!$response->ok) {
            throw new RuntimeException(
                "Failed to send Client Report Telegram (Code: {$response->errorCode}): {$response->errorDescription}"
            );
        }
    }
}

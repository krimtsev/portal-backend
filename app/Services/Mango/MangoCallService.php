<?php

declare(strict_types=1);

namespace App\Services\Mango;

use App\Constants\Cache\MangoCache;
use App\Helpers\Cache;
use App\Integrations\Mango\MangoApi;
use App\Integrations\Mango\Resources\CallsStats\DTO\CallsStatsRequestFilters;
use App\Jobs\Reports\SendMissedCallJob;
use App\Models\Mango\MangoBlacklist;
use App\Models\Mango\MangoCall;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final readonly class MangoCallService
{
    public function __construct(
        public MangoApi $mangoApi,
    ) {}

    public function getStatsKey(Carbon $from, Carbon $to, int $limit = 1000, int $offset = 0): ?string
    {
        $timezone = config('mango.timezone', 'Europe/Moscow');

        $response = $this->mangoApi->callsStats()->statsCallsRequest(
            new CallsStatsRequestFilters(
                start_date: $from->clone()
                    ->setTimezone($timezone)
                    ->format('d.m.Y H:i:s'),
                end_date: $to->clone()
                    ->setTimezone($timezone)
                    ->format('d.m.Y H:i:s'),
                limit: $limit,
                offset: $offset,
                context_type: [1]
            )
        );

        return $response['key'] ?? null;
    }

    public function getReportResult(string $key): array
    {
        return $this->mangoApi->callsStats()->statsCallsResult($key);
    }

    /**
     * Обработка пакета звонков из Mango API
     */
    public function process(array $callsData, bool $skipNotifications): void
    {
        $entryIds = array_column($callsData, 'entry_id');

        $existingIds = MangoCall::whereIn('entry_id', $entryIds)
            ->pluck('entry_id')
            ->flip()
            ->toArray();

        $newCallsForInsert = [];
        $missedCallsForAnalysis = [];

        $now = now();

        foreach ($callsData as $call) {
            if (isset($existingIds[$call['entry_id']])) {
                continue;
            }

            $contextType = (int) ($call['context_type'] ?? 0);
            $contextStatus = (int) ($call['context_status'] ?? 0);
            $callerNumber = (string) ($call['caller_number'] ?? '');

            /**
             * Пропускаем входящие которые входят в черный список
             */
            if ($contextType === 1 && $this->isNumberBlacklisted($callerNumber)) {
                continue;
            }

            $newCallsForInsert[] = [
                'entry_id'           => $call['entry_id'],
                'context_type'       => $call['context_type'],
                'context_status'     => $call['context_status'],
                'caller_number'      => $call['caller_number'],
                'called_number'      => $call['called_number'],
                'context_start_time' => Carbon::createFromTimestamp($call['context_start_time'])->toDateTimeString(),
                'duration'           => $call['duration'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            /**
             * Для отправки сообщений собираем только входящие и статус неуспешный
             * context_type: 1 – входящий
             * context_status: 0 – неуспешный
             */
            if ($contextType === 1 && $contextStatus === 0) {
                $missedCallsForAnalysis[] = $call;
            }
        }

        if (!empty($newCallsForInsert)) {
            MangoCall::insert($newCallsForInsert);
        }

        if (!$skipNotifications) {
            foreach ($missedCallsForAnalysis as $missedCall) {
                SendMissedCallJob::dispatch(
                    $missedCall['entry_id'],
                    $missedCall['called_number'],
                    $missedCall['caller_number'],
                    $missedCall['context_start_time'],
                    $missedCall['duration']
                );
            }
        }
    }

    /**
     * Метод проверки номера в чермном списке
     * @param string $number
     * @return bool
     */
    protected function isNumberBlacklisted(string $number): bool
    {
        if (empty($number) || str_starts_with($number, 'sip:')) {
            return false;
        }

        $blacklistedPatterns = Cache::remember(
            MangoCache::MANGO_BLACKLIST_PATTERNS,
            now()->addDay(),
            function () {
                return MangoBlacklist::pluck('number')->filter()->toArray();
            });

        foreach ($blacklistedPatterns as $pattern) {
            if (Str::is($pattern, $number)) {
                return true;
            }
        }

        return false;
    }
}

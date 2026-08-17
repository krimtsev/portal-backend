<?php

namespace App\Services\Reports;

use App\Helpers\PhoneNumber;
use App\Integrations\Telegram\Support\TelegramTargetResolver;
use App\Integrations\Telegram\TelegramManager;
use App\Models\Partner\Traits\HasMangoCalls;
use App\Models\Yclients\YcRecord;
use Illuminate\Support\Carbon;
use RuntimeException;

final readonly class MangoCallReportService
{
    use HasMangoCalls;

    public function __construct(
        private TelegramManager $telegram,
    ) {}

    public function process(
        string $calledNumber,
        string $callerNumber,
        string $contextStartTime,
        int $duration
    ): void
    {
        $partner = $this->getPartnerForMissedCall($calledNumber);

        if (!$partner) {
            return;
        }

        $clientName = YcRecord::where('client_phone', $callerNumber)
            ->latest('datetime')
            ->value('client_name');

        $callDateTime = Carbon::createFromTimestamp($contextStartTime)
            ->setTimezone(config('mango.timezone'))->toDateTimeString();

        $messageLines = [
            __('reports.missed_call.title'),
            __('reports.missed_call.branch', ['branch' => "<b>{$partner->name}</b>"]),
            __('reports.missed_call.caller', [
                'phone' => PhoneNumber::format($callerNumber),
                'name'  => $clientName ? "<b>({$clientName})</b>" : '',
            ]),
            __('reports.missed_call.datetime', ['datetime' => $callDateTime]),
            __('reports.missed_call.duration', ['duration' => $duration]),
        ];

        $text = implode("\n", $messageLines);

        $target = TelegramTargetResolver::resolve(
            defaultChatId: $partner->notificationChannel->telegram_chat_id
        );

        $bot = $this->telegram->bot($target->botName);

        $response = $bot->sendMessage([
            'chat_id' => $target->chatId,
            'text'    => $text,
        ]);

        if (!$response->ok) {
            throw new RuntimeException(
                "Failed to send missed call notification (Code: {$response->errorCode}): {$response->errorDescription}"
            );
        }
    }
}

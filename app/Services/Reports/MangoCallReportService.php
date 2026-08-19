<?php

namespace App\Services\Reports;

use App\Integrations\Telegram\Support\TelegramTargetResolver;
use App\Integrations\Telegram\TelegramManager;
use App\Models\Partner\Traits\HasMangoCalls;
use App\Models\Yclients\YcRecord;
use App\Services\Formatters\MangoCallFormatter;
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
    ): void {
        $partner = $this->getPartnerForMissedCall($calledNumber);

        if (!$partner) {
            return;
        }

        $clientName = YcRecord::where('client_phone', $callerNumber)
            ->latest('datetime')
            ->value('client_name');

        $callDateTime = Carbon::createFromTimestamp($contextStartTime)
            ->setTimezone(config('mango.timezone'))->toDateTimeString();

        $text = MangoCallFormatter::formatMissedCall(
            $partner->name,
            $callerNumber,
            $clientName,
            $callDateTime,
            $duration,
        );

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

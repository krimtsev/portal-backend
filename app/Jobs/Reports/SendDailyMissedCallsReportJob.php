<?php

namespace App\Jobs\Reports;

use App\Integrations\Telegram\Support\TelegramTargetResolver;
use App\Integrations\Telegram\TelegramManager;
use App\Jobs\Middleware\ThrottleJobSleep;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class SendDailyMissedCallsReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        private readonly string $chatId,
        private readonly string $message,
    ) {}

    public function middleware(): array
    {
        return [ThrottleJobSleep::forTelegram()];
    }

    public function uniqueId(): string
    {
        $messageHash = md5($this->message);

        return sprintf(
            'send_daily_missed_call_%s_%s',
            $this->chatId,
            $messageHash
        );
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     * Первая ошибка — ждем 10 сек, вторая — 60 сек, третья — 120 сек.
     */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    public function handle(TelegramManager $telegramManager): void
    {
        $target = TelegramTargetResolver::resolve(
            defaultChatId: $this->chatId
        );

        $bot = $telegramManager->bot($target->botName);

        $response = $bot->sendMessage([
            'chat_id' => $target->chatId,
            'text'    => $this->message,
        ]);

        if (!$response->ok) {
            throw new RuntimeException(
                "Failed to send missed call notification (Code: {$response->errorCode}): {$response->errorDescription}"
            );
        }
    }

    /**
     * Метод срабатывает, когда все попытки завершились неудачей
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('telegram')->critical(
            'Отправка ежедневной статистики пропущенных звонков в Telegram завершилась ошибкой.',
            [
                'error' => $exception->getMessage(),
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Jobs\PartnerNotifications;

use App\Integrations\Telegram\Support\TelegramTargetResolver;
use App\Integrations\Telegram\TelegramManager;
use App\Jobs\Middleware\ThrottleJobSleep;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

final class SendVideoReportRemindersJob implements ShouldQueue
{
    use Queueable;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly string $chatId,
        public readonly string $photoPath,
        public readonly string $caption,
    ) {}

    public function middleware(): array
    {
        return [ThrottleJobSleep::forTelegram()];
    }

    /**
     * Уникальный ID задачи для предотвращения race conditions.
     */
    public function uniqueId(): string
    {
        return "send_video_report_reminders_{$this->chatId}";
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     * Первая ошибка — ждем 60 сек, вторая — 120 сек, третья — 180 сек.
     */
    public function backoff(): array
    {
        return [60, 120, 180];
    }

    public function handle(TelegramManager $telegram): void
    {
        $target = TelegramTargetResolver::resolve(
            defaultChatId: $this->chatId
        );

        $bot = $telegram->bot($target->botName);

        $response = $bot->sendPhoto([
            'chat_id' => $target->chatId,
            'photo'   => $this->photoPath,
            'caption' => $this->caption,
        ]);

        if (!$response->ok) {
            throw new RuntimeException(
                "Failed to send video report photo reminder (Code: {$response->errorCode}): {$response->errorDescription}"
            );
        }
    }

    /**
     * Метод срабатывает, когда все попытки завершились неудачей
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('telegram')->critical(
            'Отправка напоминаний о видеоотчете в Telegram завершилась ошибкой.',
            [
                'company_id' => $this->chatId,
                'error' => $exception->getMessage(),
            ]
        );
    }
}

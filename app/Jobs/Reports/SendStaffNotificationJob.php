<?php

declare(strict_types=1);

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

final class SendStaffNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly int $companyId,
        public readonly int $staffId,
        public readonly string $message,
        public readonly ?string $photoUrl = null,
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
        $messageHash = md5($this->message);

        return "send_staff_notification_{$this->companyId}_{$this->staffId}_{$messageHash}";
    }

    /**
     * Стратегия ожидания между повторами (Exponential/Step Backoff).
     * Первая ошибка — ждем 10 сек, вторая — 60 сек, третья — 120 сек.
     */
    public function backoff(): array
    {
        return [10, 60, 120];
    }

    public function handle(TelegramManager $telegram): void
    {
        $target = TelegramTargetResolver::resolve(
            channelName: 'staff_updates'
        );

        $bot = $telegram->bot($target->botName);

        $sendAsPhoto = (bool) config('telegram.report_type.as_photo');

        if ($sendAsPhoto) {
            $response = $bot->sendPhoto([
                'chat_id' => $target->chatId,
                'photo'   => $this->photoUrl,
                'caption' => $this->message,
            ]);
        } else {
            $response = $bot->sendMessage([
                'chat_id' => $target->chatId,
                'text'    => $this->message,
            ]);
        }

        if (!$response->ok) {
            throw new RuntimeException(
                "Failed to send Telegram photo (Code: {$response->errorCode}): {$response->errorDescription}"
            );
        }
    }

    /**
     * Метод срабатывает, когда все попытки завершились неудачей.
     */
    public function failed(Throwable $exception): void
    {
        Log::channel('telegram')->critical(
            'Отправка уведомления о сотруднике в Telegram завершилась ошибкой.',
            [
                'company_id' => $this->companyId,
                'staff_id'   => $this->staffId,
                'error'      => $exception->getMessage(),
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Jobs\PartnerNotifications;

use App\Integrations\Telegram\Support\TelegramTargetResolver;
use App\Integrations\Telegram\TelegramManager;
use App\Jobs\Middleware\ThrottleJobSleep;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

final class SendPartnerMessageJob implements ShouldQueue
{
    use Queueable;

    /** Количество попыток выполнения */
    public int $tries = 3;

    /** Таймаут выполнения */
    public int $timeout = 60;

    public function __construct(
        public readonly string $chatId,
        public readonly string $message,
        public readonly ?string $filePath = null,
        public readonly bool $isPhoto = false,
    ) {}

    public function middleware(): array
    {
        return [ThrottleJobSleep::forTelegram()];
    }

    public function uniqueId(): string
    {
        $messageHash = md5($this->message);
        return "send_partner_msg_{$messageHash}";
    }

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

        if ($this->filePath) {
            $fileStream = Storage::disk('broadcasts')->readStream($this->filePath);

            if ($this->isPhoto) {
                $response = $bot->sendPhoto([
                    'chat_id' => $target->chatId,
                    'photo'   => $fileStream,
                    'caption' => $this->message,
                ]);
            } else {
                $response = $bot->sendDocument([
                    'chat_id'  => $target->chatId,
                    'document' => $fileStream,
                    'caption'  => $this->message,
                ]);
            }
        } else {
            $response = $bot->sendMessage([
                'chat_id' => $target->chatId,
                'text'    => $this->message,
            ]);
        }

        if (!$response->ok) {
            throw new RuntimeException("Failed to send broadcast message (Code: {$response->errorCode}): {$response->errorDescription}");
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::channel('telegram')->critical(
            'Рассылка в Telegram завершилась ошибкой',
            [
                'chat_id' => $this->chatId,
                'error'   => $exception->getMessage(),
            ]
        );
    }
}

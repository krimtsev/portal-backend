<?php

declare(strict_types=1);

namespace App\Services\Partners;

use App\Jobs\PartnerNotifications\SendPartnerMessageJob;
use App\Models\Partner\Traits\HasBroadcastDispatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use Str;

final class PartnerMessageService
{
    use HasBroadcastDispatch;

    public function broadcast(array $partnerIds, string $message, ?UploadedFile $file): void
    {
        $chatIds = $this->resolveChatIds($partnerIds);

        if ($chatIds->isEmpty()) {
            return;
        }

        $filePath = null;
        $isPhoto = false;

        if ($file) {
            $extension = $file->getClientOriginalExtension() ?: $file->guessExtension();

            if (empty($extension)) {
                throw new InvalidArgumentException('Не удалось определить расширение файла.');
            }

            $originalName = $file->getClientOriginalName();
            $isPhoto = str_starts_with($file->getMimeType() ?? '', 'image/');

            $filePath = $file->storeAs(Str::uuid7()->toString(), $originalName, 'broadcasts');
        }

        foreach ($chatIds as $chatId) {
            SendPartnerMessageJob::dispatch(
                $chatId,
                $message,
                $filePath,
                $isPhoto
            );
        }
    }

    /**
     * Резолв целевых Telegram chat_id в зависимости от режима.
     *
     * @param array<string> $partnerIds
     * @return Collection<int, string>
     */
    private function resolveChatIds(array $partnerIds): Collection
    {
        if (in_array('!test', $partnerIds, true)) {
            $debugChatId = config('telegram.debug.chat_id');

            if (!$debugChatId) {
                throw new RuntimeException('Telegram debug chat_id is not configured in telegram.php.');
            }

            return collect([$debugChatId]);
        }

        $query = $this->getBroadcastQuery();

        if (!in_array('!all', $partnerIds, true)) {
            $query->whereIn('id', $partnerIds);
        }

        return $this->pluckUniqueChatIds($query);
    }
}

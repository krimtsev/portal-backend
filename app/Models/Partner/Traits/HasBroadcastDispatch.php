<?php

declare(strict_types=1);

namespace App\Models\Partner\Traits;

use App\Enums\NotificationChannel;
use App\Models\Partner\Partner;
use Illuminate\Support\Collection;

trait HasBroadcastDispatch
{
    /**
     * Получает уникальные ID чатов для массовой рассылки в Telegram.
     *
     * @param string[] $ignoreChatIds
     * @param string|null $companyId
     * @return Collection<int, string>
     */
    protected function getBroadcastChatIds(array $ignoreChatIds = [], ?string $companyId = null): Collection
    {
        return Partner::query()
            ->with(['notificationChannel'])
            ->withActiveYclients()
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM)
            ->when($companyId, fn ($query) => $query->where('yclients_id', $companyId))
            ->get()
            ->pluck('notificationChannel.telegram_chat_id')
            ->filter()
            ->reject(fn (string $chatId): bool => in_array($chatId, $ignoreChatIds, true))
            ->unique()
            ->values();
    }
}

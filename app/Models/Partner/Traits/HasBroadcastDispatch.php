<?php

declare(strict_types=1);

namespace App\Models\Partner\Traits;

use App\Enums\NotificationChannel;
use App\Models\Partner\Partner;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

trait HasBroadcastDispatch
{
    /**
     * Формирует базовый запрос для получения партнеров, готовых к рассылке.
     */
    protected function getBroadcastQuery(): Builder
    {
        return Partner::query()
            ->with(['notificationChannel'])
            ->withActiveYclients()
            ->hasReadyNotificationChannel(NotificationChannel::TELEGRAM);
    }

    /**
     * Выполняет запрос и извлекает уникальные ID чатов.
     *
     * @param Builder $query
     * @param string[] $ignoreChatIds
     * @return Collection<int, string>
     */
    protected function pluckUniqueChatIds(Builder $query, array $ignoreChatIds = []): Collection
    {
        return $query->get()
            ->pluck('notificationChannel.telegram_chat_id')
            ->filter()
            ->reject(fn (string $chatId): bool => in_array($chatId, $ignoreChatIds, true))
            ->unique()
            ->values();
    }

    /**
     * Метод-обертка для обратной совместимости с существующим кодом.
     *
     * @param string[] $ignoreChatIds
     * @param string|null $companyId
     * @return Collection<int, string>
     */
    protected function getBroadcastChatIds(array $ignoreChatIds = [], ?string $companyId = null): Collection
    {
        $query = $this->getBroadcastQuery()
            ->when($companyId, fn ($q) => $q->where('yclients_id', $companyId));

        return $this->pluckUniqueChatIds($query, $ignoreChatIds);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Message;

use App\Models\Message\Message;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class MessageService
{
    /**
     * Получает список доступных пользователю сообщений
     *
     * * @return Collection<int, Message>
     */
    public function getActiveMessagesForUser(User $user): Collection
    {
        return Cache::remember(
            "user_messages_{$user->id}",
            now()->addMinutes(30),
            function () use ($user) {
                return Message::visibleFor($user)
                    ->select('id', 'title', 'description')
                    ->get();
            }
        );
    }
}

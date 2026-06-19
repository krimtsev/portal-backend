<?php

namespace App\Services\Message;

use App\Models\User\User;
use App\Models\Message\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

final class MessageService
{
    /**
     * Получает список доступных пользователю сообщений
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

<?php

namespace App\Listeners;

use App\Models\User\User;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Carbon;

class UpdateLastActivity
{
    public function handle(Authenticated $event): void
    {
        if ($event->user instanceof User) {
            $user = $event->user;
            $lastActivity = $user->last_activity;

            if (!$lastActivity || Carbon::parse($lastActivity)->diffInMinutes(now()) >= 10) {
                $this->updateActivity($user);
            }
        }
    }

    protected function updateActivity($user): void
    {
        $user->updateQuietly([
            'last_activity' => Carbon::now(),
        ]);
    }
}

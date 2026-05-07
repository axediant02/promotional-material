<?php

namespace App\Services;

use App\Events\WorkflowNotificationBroadcasted;
use App\Models\User;
use App\Notifications\WorkflowNotification;
use Illuminate\Support\Collection;

class WorkflowNotificationService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function sendToUser(User $user, array $payload): void
    {
        $user->notify(new WorkflowNotification($payload));

        event(new WorkflowNotificationBroadcasted($user->user_id, $payload));
    }

    /**
     * @param Collection<int, User> $users
     * @param array<string, mixed> $payload
     */
    public function sendToUsers(Collection $users, array $payload): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $payload);
        }
    }
}

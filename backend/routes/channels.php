<?php

use App\Models\AssignmentChatThread;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{id}.notifications', function ($user, $id) {
    abort_unless($user->user_id === $id, 403);

    return true;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    abort_unless($user->user_id === $id, 403);

    return true;
});

Broadcast::channel('private-App.Models.User.{id}', function ($user, $id) {
    abort_unless($user->user_id === $id, 403);

    return true;
});

Broadcast::channel('assignment-chat.{threadId}', function ($user, $threadId) {
    $thread = AssignmentChatThread::query()->find($threadId);

    abort_unless($thread, 403);
    abort_unless(
        ($user->isClient() && $thread->client_id === $user->user_id)
        || ($user->isProduction() && $thread->production_id === $user->user_id),
        403
    );

    return true;
});

Broadcast::channel('assignment-chat-user.{userId}', function ($user, $userId) {
    abort_unless($user->user_id === $userId, 403);

    return true;
});

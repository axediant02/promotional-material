<?php

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

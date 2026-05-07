<?php

namespace App\Policies;

use App\Models\AssignmentChatThread;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssignmentChatThreadPolicy
{
    public function viewAny(User $user): Response
    {
        return ($user->isClient() || $user->isProduction())
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, AssignmentChatThread $thread): Response
    {
        return $this->canAccessThread($user, $thread)
            ? Response::allow()
            : Response::deny('You cannot access this chat thread.');
    }

    public function markRead(User $user, AssignmentChatThread $thread): Response
    {
        return $this->view($user, $thread);
    }

    public function createMessage(User $user, AssignmentChatThread $thread): Response
    {
        return $this->view($user, $thread);
    }

    private function canAccessThread(User $user, AssignmentChatThread $thread): bool
    {
        return ($user->isClient() && $thread->client_id === $user->user_id)
            || ($user->isProduction() && $thread->production_id === $user->user_id);
    }
}

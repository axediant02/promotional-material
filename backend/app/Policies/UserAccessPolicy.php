<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserAccessPolicy
{
    public function admin(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny();
    }

    public function client(User $user): Response
    {
        return $user->isClient()
            ? Response::allow()
            : Response::deny();
    }

    public function production(User $user): Response
    {
        return $user->isProduction()
            ? Response::allow()
            : Response::deny();
    }

    public function clientOrProduction(User $user): Response
    {
        return ($user->isClient() || $user->isProduction())
            ? Response::allow()
            : Response::deny();
    }
}

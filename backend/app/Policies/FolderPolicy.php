<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FolderPolicy
{
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Folder $folder): Response
    {
        return Folder::query()
            ->accessibleTo($user)
            ->whereKey($folder->getKey())
            ->exists()
            ? Response::allow()
            : Response::deny('You cannot access this folder.');
    }

    public function create(User $user): Response
    {
        return $user->isProduction()
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, Folder $folder): Response
    {
        return $user->isProduction()
            && Folder::query()
                ->accessibleTo($user)
                ->whereKey($folder->getKey())
                ->exists()
            ? Response::allow()
            : Response::deny();
    }
}

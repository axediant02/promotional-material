<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FolderService
{
    public function accessibleFoldersQuery(User $user): Builder
    {
        return Folder::query()
            ->with('client:user_id,name,email')
            ->accessibleTo($user);
    }

    public function authorizeAccess(Folder $folder, User $user): void
    {
        abort_unless($this->accessibleFoldersQuery($user)
            ->whereKey($folder->getKey())
            ->exists(), 403, 'You cannot access this folder.');
    }

    public function authorizeFolderAccess(Folder $folder, User $user): void
    {
        $this->authorizeAccess($folder, $user);
    }
}

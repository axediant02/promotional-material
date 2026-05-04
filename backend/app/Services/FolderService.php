<?php

namespace App\Services;

use App\Models\AssignedClient;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FolderService
{
    public function accessibleFoldersQuery(User $user): Builder
    {
        $query = Folder::query()->with('client:user_id,name,email');

        if ($user->isClient()) {
            return $query->where('folder_id', $user->assigned_folder_id);
        }

        if ($user->isProduction()) {
            return $query->whereIn('client_id', AssignedClient::query()
                ->select('client_id')
                ->where('production_id', $user->user_id));
        }

        return $query;
    }

    public function authorizeFolderAccess(Folder $folder, User $user): void
    {
        if ($user->isClient() && $folder->folder_id !== $user->assigned_folder_id) {
            abort(403, 'You cannot access this folder.');
        }

        if ($user->isProduction() && ! $this->accessibleFoldersQuery($user)
            ->whereKey($folder->getKey())
            ->exists()) {
            abort(403, 'You cannot access this folder.');
        }
    }
}

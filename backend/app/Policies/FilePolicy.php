<?php

namespace App\Policies;

use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function viewRecycleBin(User $user): Response
    {
        return $user->isProduction()
            ? Response::allow()
            : Response::deny();
    }

    public function view(User $user, MediaFile $file): Response
    {
        return $this->canAccessFile($user, $file)
            ? Response::allow()
            : Response::deny('You cannot access this file.');
    }

    public function create(User $user): Response
    {
        return $user->isProduction()
            ? Response::allow()
            : Response::deny();
    }

    public function update(User $user, MediaFile $file): Response
    {
        return $this->canModifyFile($user, $file)
            ? Response::allow()
            : Response::deny();
    }

    public function delete(User $user, MediaFile $file): Response
    {
        return $this->update($user, $file);
    }

    public function restore(User $user, MediaFile $file): Response
    {
        return $this->canModifyFile($user, $file)
            ? Response::allow()
            : Response::deny('You cannot access this file.');
    }

    public function download(User $user, MediaFile $file): Response
    {
        return $this->view($user, $file);
    }

    public function preview(User $user, MediaFile $file): Response
    {
        return $this->view($user, $file);
    }

    private function canModifyFile(User $user, MediaFile $file): bool
    {
        return $user->isProduction() && $this->canAccessFile($user, $file);
    }

    private function canAccessFile(User $user, MediaFile $file): bool
    {
        if ($user->isAdmin() || $user->isAgent()) {
            return true;
        }

        return MediaFile::query()
            ->accessibleTo($user, withTrashed: true)
            ->whereKey($file->getKey())
            ->exists();
    }
}

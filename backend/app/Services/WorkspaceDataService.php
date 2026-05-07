<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Support\Collection;

class WorkspaceDataService
{
    public function __construct(
        private readonly FolderService $folderService,
        private readonly FileService $fileService,
    ) {
    }

    /**
     * @return Collection<int, Folder>
     */
    public function foldersForUser(User $user): Collection
    {
        return $this->folderService->accessibleFoldersQuery($user)
            ->latest('updated_at')
            ->get();
    }

    /**
     * @return Collection<int, MediaFile>
     */
    public function filesForUser(User $user): Collection
    {
        return $this->fileService->accessibleFilesQuery($user)
            ->latest('updated_at')
            ->get();
    }

    /**
     * @return Collection<int, MediaFile>
     */
    public function recycleBinFilesForUser(User $user): Collection
    {
        return $this->fileService->accessibleFilesQuery($user, onlyTrashed: true)
            ->latest('deleted_at')
            ->get();
    }
}

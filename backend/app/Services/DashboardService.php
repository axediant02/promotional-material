<?php

namespace App\Services;

use App\Models\User;

class DashboardService
{
    public function __construct(
        private readonly FolderService $folderService,
        private readonly FileService $fileService,
    ) {
    }

    public function getForUser(User $user): array
    {
        $foldersQuery = $this->folderService->accessibleFoldersQuery($user)->latest('updated_at');
        $filesQuery = $this->fileService->accessibleFilesQuery($user)->latest('updated_at');

        $folders = (clone $foldersQuery)
            ->limit($user->isClient() ? 1 : 12)
            ->get();

        $recentFiles = (clone $filesQuery)
            ->with('folder:folder_id,folder_name')
            ->limit(8)
            ->get();

        return [
            'user' => $user->loadMissing('assignedFolder'),
            'stats' => [
                'folders' => (clone $foldersQuery)->count(),
                'files' => (clone $filesQuery)->count(),
            ],
            'folders' => $folders,
            'recentFiles' => $recentFiles,
        ];
    }
}

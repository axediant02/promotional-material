<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

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

        return $this->buildDashboard(
            $user,
            $folders,
            $recentFiles,
            (clone $foldersQuery)->count(),
            (clone $filesQuery)->count(),
        );
    }

    /**
     * @param  Collection<int, mixed>  $folders
     * @param  Collection<int, mixed>  $files
     * @return array{user: User, stats: array{folders: int, files: int}, folders: Collection<int, mixed>, recentFiles: Collection<int, mixed>}
     */
    public function buildDashboard(
        User $user,
        Collection $folders,
        Collection $files,
        int $folderCount,
        int $fileCount,
    ): array {
        $folderLimit = $user->isClient() ? 1 : 12;

        return [
            'user' => $user->loadMissing('assignedFolder'),
            'stats' => [
                'folders' => $folderCount,
                'files' => $fileCount,
            ],
            'folders' => $folders->take($folderLimit)->values(),
            'recentFiles' => $files->take(8)->values(),
        ];
    }
}

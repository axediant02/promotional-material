<?php

namespace App\Services;

use App\Models\User;

class AgentWorkspaceService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly FolderService $folderService,
        private readonly FileService $fileService,
    ) {
    }

    public function getForUser(User $user): array
    {
        $folders = $this->folderService->accessibleFoldersQuery($user)->latest('updated_at')->get();
        $files = $this->fileService->accessibleFilesQuery($user)->latest('updated_at')->get();

        return [
            'dashboard' => $this->dashboardService->getForUser($user),
            'folders' => $folders->values(),
            'files' => $files->values(),
        ];
    }
}

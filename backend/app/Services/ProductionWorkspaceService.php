<?php

namespace App\Services;

use App\Models\User;

class ProductionWorkspaceService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly FolderService $folderService,
        private readonly FileService $fileService,
        private readonly ProductionRequestService $productionRequestService,
    ) {
    }

    public function getForUser(User $user): array
    {
        $foldersQuery = $this->folderService->accessibleFoldersQuery($user)->latest('updated_at');
        $filesQuery = $this->fileService->accessibleFilesQuery($user)->latest('updated_at');
        $requestsQuery = $this->productionRequestService->accessibleRequestsQuery($user)->latest('created_at');
        $recycleBinFilesQuery = $this->fileService->accessibleFilesQuery($user, onlyTrashed: true)
            ->latest('deleted_at');

        $folders = $foldersQuery->get();
        $files = $filesQuery->get();
        $requests = $requestsQuery->get();
        $recycleBinFiles = $recycleBinFilesQuery->get();

        return [
            'dashboard' => $this->dashboardService->getForUser($user),
            'folders' => $folders->values(),
            'requests' => $requests->values(),
            'files' => $files->values(),
            'recycleBinFiles' => $recycleBinFiles->values(),
        ];
    }
}

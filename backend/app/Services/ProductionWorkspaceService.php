<?php

namespace App\Services;

use App\Models\User;

class ProductionWorkspaceService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly WorkspaceDataService $workspaceDataService,
        private readonly ProductionRequestService $productionRequestService,
    ) {
    }

    public function getForUser(User $user): array
    {
        $folders = $this->workspaceDataService->foldersForUser($user);
        $files = $this->workspaceDataService->filesForUser($user);
        $requestsQuery = $this->productionRequestService->accessibleRequestsQuery($user)->latest('created_at');
        $recycleBinFiles = $this->workspaceDataService->recycleBinFilesForUser($user);
        $requests = $requestsQuery->get();

        return [
            'dashboard' => $this->dashboardService->buildDashboard(
                $user,
                $folders,
                $files,
                $folders->count(),
                $files->count(),
            ),
            'folders' => $folders->values(),
            'requests' => $requests->values(),
            'files' => $files->values(),
            'recycleBinFiles' => $recycleBinFiles->values(),
        ];
    }
}

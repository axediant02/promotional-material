<?php

namespace App\Services;

use App\Models\User;

class ClientWorkspaceService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly WorkspaceDataService $workspaceDataService,
        private readonly ClientRequestService $clientRequestService,
    ) {
    }

    public function getForUser(User $user): array
    {
        $folders = $this->workspaceDataService->foldersForUser($user);
        $files = $this->workspaceDataService->filesForUser($user);
        $requestsQuery = $this->clientRequestService->requestsQuery($user);

        return [
            'dashboard' => $this->dashboardService->buildDashboard(
                $user,
                $folders,
                $files,
                $folders->count(),
                $files->count(),
            ),
            'files' => $files->values(),
            'requests' => $requestsQuery->get(),
        ];
    }
}

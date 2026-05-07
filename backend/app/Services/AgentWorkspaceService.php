<?php

namespace App\Services;

use App\Models\User;

class AgentWorkspaceService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly WorkspaceDataService $workspaceDataService,
    ) {
    }

    public function getForUser(User $user): array
    {
        $folders = $this->workspaceDataService->foldersForUser($user);
        $files = $this->workspaceDataService->filesForUser($user);

        return [
            'dashboard' => $this->dashboardService->buildDashboard(
                $user,
                $folders,
                $files,
                $folders->count(),
                $files->count(),
            ),
            'folders' => $folders->values(),
            'files' => $files->values(),
        ];
    }
}

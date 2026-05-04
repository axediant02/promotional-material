<?php

namespace App\Services;

use App\Models\User;

class ClientWorkspaceService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly FileService $fileService,
        private readonly ClientRequestService $clientRequestService,
    ) {
    }

    public function getForUser(User $user): array
    {
        $filesQuery = $this->fileService->accessibleFilesQuery($user)->latest('updated_at');
        $requestsQuery = $this->clientRequestService->requestsQuery($user);

        return [
            'dashboard' => $this->dashboardService->getForUser($user),
            'files' => $filesQuery->get(),
            'requests' => $requestsQuery->get(),
        ];
    }
}

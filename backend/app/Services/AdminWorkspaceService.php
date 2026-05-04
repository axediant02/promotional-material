<?php

namespace App\Services;

use App\Models\User;

class AdminWorkspaceService
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly AdminRequestService $adminRequestService,
        private readonly AdminAssignmentService $adminAssignmentService,
        private readonly AdminActivityLogService $adminActivityLogService,
        private readonly AdminUserService $adminUserService,
    ) {
    }

    public function getForUser(User $user): array
    {
        return [
            'dashboard' => $this->dashboardService->getForUser($user),
            'requests' => $this->adminRequestService->requestsQuery()->get(),
            'activityLogs' => $this->adminActivityLogService->activityLogsQuery()->limit(3)->get(),
            'assignments' => $this->adminAssignmentService->assignmentsQuery()->get(),
            'productionUsers' => $this->adminAssignmentService->productionUsersQuery()->get(),
            'users' => $this->adminUserService->usersQuery()->get(),
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminAssignmentRequest;
use App\Models\AssignedClient;
use App\Models\User;
use App\Services\AssignmentChatService;
use App\Services\AdminAssignmentService;
use App\Services\ProductionWorkspaceBroadcastService;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\JsonResponse;

class AdminAssignmentController extends Controller
{
    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
        private readonly AssignmentChatService $assignmentChatService,
        private readonly AdminAssignmentService $adminAssignmentService,
        private readonly ProductionWorkspaceBroadcastService $productionWorkspaceBroadcastService,
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('admin', User::class);

        $assignments = $this->adminAssignmentService->assignmentsQuery()->get();
        $productionUsers = $this->adminAssignmentService->productionUsersQuery()->get();

        return response()->json([
            'message' => 'Assignments fetched.',
            'data' => [
                'assignments' => $assignments,
                'production_users' => $productionUsers,
            ],
        ]);
    }

    public function store(StoreAdminAssignmentRequest $request): JsonResponse
    {
        $this->authorize('admin', User::class);

        $assignment = AssignedClient::query()->firstOrNew([
            'client_id' => $request->string('client_id')->toString(),
        ]);

        $isNew = ! $assignment->exists;
        $previousProductionId = $assignment->production_id;
        $previousStatus = $assignment->status;
        $productionId = $request->string('production_id')->toString();

        $assignment->fill([
            'production_id' => $productionId,
            'status' => $request->string('status')->toString(),
        ]);
        $assignment->save();

        $this->assignmentChatService->syncForAssignment($assignment, $previousProductionId, $previousStatus);
        $this->productionWorkspaceBroadcastService->broadcastAssignmentSaved($assignment, $previousProductionId);

        if ($isNew || $previousProductionId !== $productionId) {
            $users = User::query()
                ->whereIn('user_id', [$productionId, $assignment->client_id])
                ->get()
                ->keyBy('user_id');

            $productionUser = $users->get($productionId);
            $clientUser = $users->get($assignment->client_id);

            if ($productionUser && $clientUser) {
                $this->workflowNotificationService->sendToUser($productionUser, [
                    'kind' => 'client_assigned',
                    'title' => 'New client assignment',
                    'body' => sprintf('%s was assigned to you for production work.', $clientUser->name),
                    'target' => 'queue',
                ]);
            }
        }

        return response()->json([
            'message' => 'Client assignment saved.',
            'data' => [
                'assignment' => $assignment->fresh(),
            ],
        ], $isNew ? 201 : 200);
    }

    public function destroy(AssignedClient $assignment): JsonResponse
    {
        $this->authorize('admin', User::class);

        $this->assignmentChatService->archiveForAssignmentDeletion($assignment);
        $this->productionWorkspaceBroadcastService->broadcastAssignmentDeleted($assignment);
        $assignment->delete();

        return response()->json([
            'message' => 'Client assignment removed.',
            'data' => [],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest\StoreClientRequestRequest;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ClientRequestController extends Controller
{
    public function __construct(private readonly WorkflowNotificationService $workflowNotificationService)
    {
    }

    public function store(StoreClientRequestRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('create', ClientRequest::class);

        $clientRequest = DB::transaction(function () use ($request, $user): ClientRequest {
            $assignedFolderId = $user->assigned_folder_id;

            if (! $assignedFolderId) {
                $folder = Folder::query()->firstOrCreate(
                    ['client_id' => $user->user_id],
                    [
                        'folder_name' => $user->name,
                        'created_by' => null,
                    ]
                );

                $user->forceFill([
                    'assigned_folder_id' => $folder->folder_id,
                ])->save();

                $assignedFolderId = $folder->folder_id;
            }

            return ClientRequest::query()->create([
                'client_id' => $user->user_id,
                'folder_id' => $assignedFolderId,
                'title' => $request->string('title')->toString(),
                'description' => $request->string('description')->toString(),
                'request_type' => $request->string('request_type')->toString(),
                'status' => ClientRequest::STATUS_PENDING,
                'due_date' => null,
            ]);
        });

        $adminUsers = User::query()
            ->where('role', User::ROLE_ADMIN)
            ->get();

        $this->workflowNotificationService->sendToUsers($adminUsers, [
            'kind' => 'client_request_created',
            'title' => 'New client request',
            'body' => sprintf(
                '%s submitted a %s request: %s.',
                $user->name,
                $clientRequest->request_type === ClientRequest::TYPE_UPDATE_ASSET ? 'request update' : 'new asset',
                $clientRequest->title
            ),
            'target' => 'requests',
            'request_id' => $clientRequest->request_id,
        ]);

        return response()->json([
            'message' => 'Request created.',
            'data' => [
                'request' => $clientRequest,
            ],
        ], 201);
    }
}
